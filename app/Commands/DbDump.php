<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use App\Models\DbBackupsModel;

class DbDump extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'db:dump';
    protected $description = 'Creates a MySQL backup and logs it.';

    protected $usage = 'db:dump --label="Text" --user-id=1 [--log]';

    public function run(array $params)
    {
        helper('filesystem');

        $label   = CLI::getOption('label')   ?? 'manual-backup';
        $userId  = CLI::getOption('user-id') ?? null;
        $doLog   = CLI::getOption('log')     ?? false;

        $db = db_connect();

        // 🔥 FIX — Correctly get DB Name
        $dbName = $db->getDatabase();

        if (!$dbName) {
            CLI::error("Unable to determine database name.");
            return;
        }

        $timestamp = Time::now()->format('Ymd-His');
        $backupFile = WRITEPATH . "backups/db-{$timestamp}.sql";

        // Run mysqldump
        $cmd = sprintf(
            '/Applications/MAMP/Library/bin/mysql80/bin/mysqldump -u%s -p%s %s > %s',
            escapeshellarg($db->username),
            escapeshellarg($db->password),
            escapeshellarg($dbName),
            escapeshellarg($backupFile)
        );

        $result = shell_exec($cmd);

        if (!file_exists($backupFile)) {
            CLI::error("Backup failed.");
            return;
        }

        // Optionally GZIP
        $gzFile = $backupFile . '.gz';
        $gz = gzopen($gzFile, 'w9');
        gzwrite($gz, file_get_contents($backupFile));
        gzclose($gz);
        unlink($backupFile);

        CLI::write("Backup created: {$gzFile}", 'green');

        // If --log is provided, store in db_backups
        if ($doLog) {
            $model = new DbBackupsModel();

            $model->insert([
                'file_path' => $gzFile,
                'label'     => $label,
                'user_id'   => $userId,
                'created_at'=> Time::now()->format('Y-m-d H:i:s'),
            ]);

            CLI::write("Backup logged to database.", 'yellow');
        }
    }
}
