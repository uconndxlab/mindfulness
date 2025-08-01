<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class SyncFiles extends Command
{
    protected $signature = 'app:sync
                            {--only=all : directory to sync (public-content, data)}
                            {--direction=down : UPload or DOWNload)}
                            {--dry-run : simulate sync}';

    protected $description = 'Syncs files between local and remote environments using WinSCP.';

    public function handle(): int
    {
        $config = config('deployment');
        $direction = $this->option('direction');
        $only = $this->option('only');

        // validate config
        if (empty($config['user']) || empty($config['host'])) {
            $this->error('Deployment user or host not set.');
            return self::FAILURE;
        }

        // get targets and validate
        $syncMap = $config['sync'] ?? [];
        $targets = ($only === 'all') ? array_keys($syncMap) : [$only];
        $this->info("Starting file sync using WinSCP (Direction: {$direction})...");
        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: Calculating changes, no files will be transferred.');
        }

        // loop through targets and execute sync
        foreach ($targets as $target) {
            $this->line("Syncing '{$target}'...");

            $localPath = $syncMap[$target]['local'];
            $remotePath = $syncMap[$target]['remote'];

            // build command
            $command = $this->buildWinScpCommand($config, $direction, $localPath, $remotePath, $target);

            if ($this->getOutput()->isVerbose()) {
                $this->info("Running command: " . $command);
            }

            // run command
            $process = Process::forever()->run($command, function (string $type, string $output) {
                $this->output->write($output);
            });

            if (!$process->successful()) {
                $this->error("Failed to sync '{$target}'. Check WinSCP output above for details.");
            }
        }

        $this->info('Synchronization complete.');
        return self::SUCCESS;
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
            '%s /command "option batch on" "option confirm off" "open %s -privatekey=%s -hostkey=*" "%s" "exit"',
            $winscpPath,
            $session,
            $privateKey,
            $syncCommand
        );
    }
}