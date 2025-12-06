<?php
// app/Services/UserService.php
namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Entities\User;
use RuntimeException;

class UserService
{
    protected UserModel $users;
    protected BaseConnection $db;

    public function __construct(?UserModel $users = null)
    {
        $this->users = $users ?? new UserModel();
        $this->db    = $this->users->db;
    }

    /** Read groups from \Config\AuthGroups.php (source of truth). */
    public function getConfigGroups(): array
    {
        $cfg = config('AuthGroups');
        // Normalize to: [['alias'=>'admin','title'=>'Admin','description'=>'...'], ...]
        $out = [];
        foreach ($cfg->groups as $alias => $meta) {
            $out[] = [
                'alias'       => $alias,
                'title'       => $meta['title']       ?? $alias,
                'description' => $meta['description'] ?? '',
            ];
        }
        return $out;
    }

    /** List users + their assigned group aliases. */
    public function getUsers(): array
    {
        $users = $this->users
            ->orderBy('id', 'ASC')
            ->findAll(); // returns array of \CodeIgniter\Shield\Entities\User

        $map = $this->getAllUserGroupAliasesMap();

        $rows = [];
        foreach ($users as $user) {
            // Shield entities: use properties directly
            $rows[] = [
                'id'         => (int) $user->id,
                'username'   => $user->username,
                'email'      => $user->email,
                'active'     => (int) $user->active,
                'created_at' => (string) $user->created_at,
                'groups'     => $map[$user->id] ?? [],
            ];
        }

        return $rows;
    }

    /** Helper: user_id => [alias, alias...] */
    protected function getAllUserGroupAliasesMap(): array
    {
        $res = $this->db->table('auth_groups_users')
            ->select('user_id, `group`')
            ->get()->getResultArray();

        $map = [];
        foreach ($res as $row) {
            $map[(int)$row['user_id']][] = $row['group'];
        }
        return $map;
    }

    public function getUserGroupAliases(int $userId): array
    {
        $res = $this->db->table('auth_groups_users')
            ->select('`group`')
            ->where('user_id', $userId)
            ->get()->getResultArray();

        return array_map(fn($r) => $r['group'], $res);
    }

    /** Create a user and optionally assign groups[] of aliases. */
    public function createUser(array $data): array
    {
        $authUser = auth()->user();

        if (! $authUser) {
            throw new RuntimeException('Not authenticated.');
        }

        // Normalize boolean
        $data['active'] = isset($data['active']) && (int) $data['active'] === 1 ? 1 : 0;

        // Security: sanitize creation input
        $data = $this->sanitizeUserCreate($data, $authUser);

        // Basic validation (you can extend this later)
        if (empty($data['username'])) {
            throw new RuntimeException('Username is required.');
        }
        if (empty($data['password'])) {
            throw new RuntimeException('Password is required.');
        }

        // Build entity
        $userEntity = new User([
            'username' => $data['username'],
            'password' => $data['password'], // Shield will hash based on its config
            'active'   => $data['active'] ?? 1,
        ]);

        if (! $this->users->insert($userEntity)) {
            throw new RuntimeException('Failed to create user: ' . json_encode($this->users->errors()));
        }

        $id   = (int) $this->users->getInsertID();
        $user = $this->users->find($id);

        // Assign groups (if allowed & provided)
        if (! empty($data['groups']) && is_array($data['groups'])) {
            $user->syncGroups($data['groups']);
        }

        return ['id' => $id];
    }

    public function updateUser(array $data): bool
    {
        if (empty($data['id'])) {
            throw new RuntimeException('User ID is required.');
        }

        $user = $this->users->find($data['id']);

        if (! $user instanceof User) {
            throw new RuntimeException('User not found.');
        }

        // SECURITY: filter what can actually be updated
        $data = $this->sanitizeUserUpdate($data, $user);

        // Handle "active" flag if present
        if (isset($data['active'])) {
            $data['active'] = (int) ((bool) $data['active']);
        }

        // Handle password: blank means "no change"
        if (empty($data['password'])) {
            unset($data['password']);
        }

        if (! empty($data['password'])) {
            // Shield normally handles hashing, but if you're doing manual:
            // $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $user->fill(['password' => $data['password']]);
        }

        // Fill other user properties
        $updatable = array_intersect_key(
            $data,
            array_flip(['username', 'active'])
        );

        if (! empty($updatable)) {
            $user->fill($updatable);
        }

        if (! $this->users->save($user)) {
            throw new RuntimeException('Failed to update user: ' . json_encode($this->users->errors()));
        }

        // Groups handled separately in controller via setUserGroups()
        // but if you also want inline group update:
        if (isset($data['groups']) && is_array($data['groups'])) {
            $user->syncGroups($data['groups']);
        }

        return true;
    }

    public function deleteUser(int $id): bool
    {
        return (bool) $this->users->delete($id);
    }

    /**
     * Replace a user's groups with the provided aliases.
     * Only aliases defined in \Config\AuthGroups are allowed.
     */
    public function setUserGroups(int $userId, array $aliases): bool
    {
        $valid = array_column($this->getConfigGroups(), 'alias');
        $aliases = array_values(array_unique(array_intersect($aliases, $valid)));

        // Transactionally replace
        $this->db->transStart();

        $this->db->table('auth_groups_users')->where('user_id', $userId)->delete();
        foreach ($aliases as $alias) {
            $this->db->table('auth_groups_users')->insert([
                'user_id' => $userId,
                'group'   => $alias,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->db->transComplete();
        return $this->db->transStatus();
    }

    public function setGroups(int $userId, array $groups): bool
    {
        $provider = auth()->getProvider();
        $user = $provider->findById($userId);

        if (! $user) {
            throw new \RuntimeException("User not found.");
        }

        // Load all valid groups from Config\AuthGroups
        $config = config('AuthGroups');
        $validGroups = array_keys($config->groups);

        // Sanitize group list
        $groups = array_values(array_filter($groups, function ($g) use ($validGroups) {
            return in_array($g, $validGroups, true);
        }));

        // Prevent removing superadmin unless caller is superadmin
        $authUser = auth()->user();
        if ($user->inGroup('superadmin') && ! $authUser->inGroup('superadmin')) {
            throw new \RuntimeException("You cannot modify a superadmin user's groups.");
        }

        // Remove ALL groups first
        foreach ($validGroups as $possible) {
            if ($user->inGroup($possible)) {
                $user->removeGroup($possible);
            }
        }

        // Add selected groups
        foreach ($groups as $alias) {
            $user->addGroup($alias);
        }

        return true;
    }

    /**
     * Enforce security rules and prevent privilege escalation.
     *
     * @param array $data Incoming POST data
     * @param \CodeIgniter\Shield\Entities\User $targetUser The user being modified
     * @return array Sanitized data
     */
    protected function sanitizeUserUpdate(array $data, $targetUser): array
    {
        $authUser = auth()->user();

        // 1 — No one except Superadmin can modify a Superadmin
        if ($targetUser->inGroup('superadmin') && ! $authUser->inGroup('superadmin')) {
            throw new \RuntimeException('You do not have permission to modify a Superadmin.');
        }

        // 2 — Prevent changing group assignments unless allowed
        $canAssignGroups = $authUser->can('users.manage-admins');

        if (! $canAssignGroups) {
            // Remove groups array entirely if user is not allowed
            unset($data['groups']);
        }

        // 3 — Prevent non-admins from changing account "active" status
        if (! $authUser->can('users.manage-admins')) {
            unset($data['active']);
        }

        // 4 — Prevent non-admins from modifying users in higher groups
        $authGroups = $authUser->getGroups();
        $targetGroups = $targetUser->getGroups();

        $authLevel = $this->groupRank($authGroups);
        $targetLevel = $this->groupRank($targetGroups);

        if ($authLevel < $targetLevel) {
            throw new \RuntimeException('You cannot modify a user with higher privileges.');
        }

        // 5 — Prevent self-demotion or self-deactivation
        if ($authUser->id === $targetUser->id) {
            unset($data['groups']);      // cannot change own groups
            unset($data['active']);      // cannot deactivate yourself
        }

        return $data;
    }

    /**
     * Sanitize creation input so non-admins cannot:
     *  - Create admin/superadmin accounts
     *  - Control "active" flag
     */
    protected function sanitizeUserCreate(array $data, User $authUser): array
    {
        // If they cannot manage admins, strip privileged groups
        if (! $authUser->can('users.manage-admins')) {
            if (! empty($data['groups'])) {
                $data['groups'] = array_values(array_filter(
                    (array) $data['groups'],
                    static fn ($g) => ! in_array($g, ['admin', 'superadmin'], true)
                ));
            }

            // Also prevent non-admins from controlling the active flag on create
            unset($data['active']);
        }

        return $data;
    }

    /**
     * Assign a numeric privilege level to groups for comparison.
     * Higher number = higher privilege.
     */
    protected function groupRank(array $groups): int
    {
        if (in_array('superadmin', $groups)) return 100;
        if (in_array('admin', $groups)) return 80;
        if (in_array('developer', $groups)) return 70;

        return 10; // normal users
    }
}
