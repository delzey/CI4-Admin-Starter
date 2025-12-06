<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'user';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('superadmin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'superadmin' => [
            'title'       => 'Super Admin',
            'description' => 'Complete control of the site.',
        ],
        'admin' => [
            'title'       => 'Admin',
            'description' => 'Day to day administrators of the site.',
        ],
        'developer' => [
            'title'       => 'Developer',
            'description' => 'Site programmers.',
        ],
        'user' => [
            'title'       => 'User',
            'description' => 'General users of the site. Often customers.',
        ],
        'beta' => [
            'title'       => 'Beta User',
            'description' => 'Has access to beta-level features.',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'users.manage-admins'   => 'Can manage other admins',
        'users.view'            => 'Can view users',
        'users.create'          => 'Can create new non-admin users',
        'users.edit'            => 'Can edit existing non-admin users',
        'users.delete'          => 'Can delete existing non-admin users',

        'groups.view'           => 'Can view groups',
        'groups.create'         => 'Can create new groups',
        'groups.edit'           => 'Can edit existing groups',
        'groups.delete'         => 'Can delete existing groups',

        'settings.view'         => 'Can view users',
        'settings.create'       => 'Can create new non-admin users',
        'settings.edit'         => 'Can edit existing non-admin users',
        'settings.delete'       => 'Can delete existing non-admin users',

        'menu-management.view'  => 'Can manage menus',
        'menu-management.create'=> 'Can create new non-admin users',
        'menu-management.edit'  => 'Can edit existing non-admin users',
        'menu-management.delete'=> 'Can delete existing non-admin users',

        'messages.view'         => 'Can view messages',
        'messages.create'       => 'Can create new messages',
        'messages.edit'         => 'Can edit existing messages',
        'messages.delete'       => 'Can delete existing messages',

        'bolt.manage'           => 'Can Encrypt Files',        
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'superadmin' => [
            'admin.*',
            'groups.*',
            'users.*',
            'settings.*',
            'menu-management.*',
            'messages.*',
            'bolt.*',

        ],
        'admin' => [
            'admin.access',
            'users.create',
            'users.edit',
            'users.delete',
            'beta.access',
        ],
        'developer' => [
            'admin.access',
            'admin.settings',
            'users.create',
            'users.edit',
            'beta.access',
        ],
        'user' => [],
        'beta' => [
            'beta.access',
        ],
    ];
}
