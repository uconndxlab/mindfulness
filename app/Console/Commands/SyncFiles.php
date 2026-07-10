<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class SyncFiles extends Command
{
    protected $signature = 'app:sync
                            {--only=all : directory to sync (content, data)}
                            {--direction=down : UPload or DOWNload)}
                            {--dry-run : simulate sync}';

    protected $description = 'Syncs files between local and remote environments.';

    public function handle(): int
    {
        $config = config('deployment');
        $direction = $this->option('direction');
        $only = $this->option('only');
        $driver = $this->resolveDriver($config);

        // validate config
        if (empty($config['user']) || empty($config['host'])) {
            $this->error('Deployment user or host not set.');
            return self::FAILURE;
        }

        if (empty($config['key'])) {
            $this->error('Deployment SSH key path not set.');
            return self::FAILURE;
        }

        if ($driver === 'winscp' && empty($config['winscp_path'])) {
            $this->error('WinSCP path not set for Windows sync.');
            return self::FAILURE;
        }

        // get targets and validate
        $syncMap = $config['sync'] ?? [];
        $targets = ($only === 'all') ? array_keys($syncMap) : [$only];
        $this->info("Starting file sync using {$driver} (Direction: {$direction})...");
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: Calculating changes, no files will be transferred.');
        }

        // loop through targets and execute sync
        foreach ($targets as $target) {
            $this->line("Syncing '{$target}'...");

            $localPath = $syncMap[$target]['local'];
            $remotePath = $syncMap[$target]['remote'];

            // build command
            $command = $this->buildCommand($config, $driver, $direction, $localPath, $remotePath, $target);

            if ($this->getOutput()->isVerbose()) {
                $this->info("Running command: " . $command);
            }

            // run command
            $process = Process::forever()->run($command, function (string $type, string $output) {
                $this->output->write($output);
            });

            if (!$process->successful()) {
                $this->error("Failed to sync '{$target}'. Check command output above for details.");
            }
        }

        $this->info('Synchronization complete.');
        return self::SUCCESS;
    }

    private function resolveDriver(array $config): string
    {
        $configuredDriver = $config['driver'] ?? 'auto';

        if ($configuredDriver !== 'auto') {
            return $configuredDriver;
        }

        return PHP_OS_FAMILY === 'Windows' ? 'winscp' : 'rsync';
    }

    private function buildCommand(array $config, string $driver, string $direction, string $localPath, string $remotePath, string $target): string
    {
        return match ($driver) {
            'winscp' => $this->buildWinScpCommand($config, $direction, $localPath, $remotePath, $target),
            'rsync' => $this->buildRsyncCommand($config, $direction, $localPath, $remotePath),
            default => throw new \InvalidArgumentException("Unsupported sync driver [{$driver}]."),
        };
    }

    private function buildWinScpCommand(array $config, string $direction, string $localPath, string $remotePath, string $target): string
    {
        $winscpPath = sprintf('"%s"', $config['winscp_path']);
        $privateKey = sprintf('"%s"', $config['key']);
        $session = "sftp://{$config['user']}@{$config['host']}";

        // convert local path to use Windows backslashes
        $windowsLocalPath = str_replace('/', '\\', $localPath);

        // determine the direction of the sync
        $syncMode = ($direction === 'down') ? 'local' : 'remote';

        // build switches
        $switches = [];
        // delete files that are not present in the copied directory
        $switches[] = '-delete';

        $switches[] = '-resumesupport=off';
        $switches[] = '-criteria=time';

        // add mirror to replace files in the data directory
        if ($target === 'data') {
            $switches[] = '-mirror';
        }

        if ($this->option('dry-run')) {
            $switches[] = '-preview';
        }

        // build core 'synchronize' command
        $syncCommandParts = ['synchronize', $syncMode];
        $syncCommandParts = array_merge($syncCommandParts, $switches);
        $syncCommandParts[] = sprintf('"%s"', $windowsLocalPath);
        $syncCommandParts[] = sprintf('"%s"', $remotePath);
        $syncCommand = implode(' ', $syncCommandParts);

        // build final command
        return sprintf(
            '%s /ini=nul /command "option batch continue" "option confirm off" "open %s -privatekey=%s -hostkey=*" "%s" "exit"',
            $winscpPath,
            $session,
            $privateKey,
            $syncCommand
        );
    }

    private function buildRsyncCommand(array $config, string $direction, string $localPath, string $remotePath): string
    {
        $rsyncPath = $config['rsync_path'] ?: 'rsync';
        $sshCommand = sprintf(
            'ssh -i %s -o StrictHostKeyChecking=accept-new',
            escapeshellarg($config['key'])
        );

        $source = $direction === 'down'
            ? $this->buildRemoteRsyncPath($config['user'], $config['host'], $remotePath)
            : $this->normalizeLocalPath($localPath);

        $destination = $direction === 'down'
            ? $this->normalizeLocalPath($localPath)
            : $this->buildRemoteRsyncPath($config['user'], $config['host'], $remotePath);

        $commandParts = [
            escapeshellcmd($rsyncPath),
            '--archive',
            '--compress',
            '--delete',
            '--itemize-changes',
            '--rsh=' . escapeshellarg($sshCommand),
        ];

        if ($this->option('dry-run')) {
            $commandParts[] = '--dry-run';
        }

        $commandParts[] = escapeshellarg($source);
        $commandParts[] = escapeshellarg($destination);

        return implode(' ', $commandParts);
    }

    private function buildRemoteRsyncPath(string $user, string $host, string $path): string
    {
        return sprintf('%s@%s:%s', $user, $host, $this->ensureTrailingSlash($path));
    }

    private function normalizeLocalPath(string $path): string
    {
        return $this->ensureTrailingSlash($path);
    }

    private function ensureTrailingSlash(string $path): string
    {
        return rtrim($path, '/\\') . DIRECTORY_SEPARATOR;
    }
}
