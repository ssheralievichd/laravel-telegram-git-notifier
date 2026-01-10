<?php

namespace App\Console\Commands;

use App\Services\RepositoryFilter;
use Illuminate\Console\Command;

class ManageRepositoryFilter extends Command
{
    protected $signature = 'tgn:filter {action} {repository?} {event?}';

    protected $description = 'Manage repository and event filters for notifications';

    public function handle(): int
    {
        $filter = new RepositoryFilter();
        $action = $this->argument('action');

        switch ($action) {
            case 'enable':
                return $this->enable($filter);

            case 'disable':
                return $this->disable($filter);

            case 'status':
                return $this->status($filter);

            case 'list':
                return $this->listRules($filter);

            case 'add':
            case 'ignore':
                return $this->addRule($filter);

            case 'remove':
                return $this->removeRule($filter);

            case 'clear':
                return $this->clearRules($filter);

            default:
                $this->error("Unknown action: {$action}");
                $this->showUsage();
                return 1;
        }
    }

    private function enable(RepositoryFilter $filter): int
    {
        $filter->setEnabled(true);
        $this->info('Repository filter enabled');
        return 0;
    }

    private function disable(RepositoryFilter $filter): int
    {
        $filter->setEnabled(false);
        $this->info('Repository filter disabled');
        return 0;
    }

    private function status(RepositoryFilter $filter): int
    {
        $enabled = $filter->isEnabled();
        $rules = $filter->listRules();

        $this->info('Filter status: ' . ($enabled ? 'ENABLED' : 'DISABLED'));
        $this->info('Total rules: ' . count($rules));

        return 0;
    }

    private function listRules(RepositoryFilter $filter): int
    {
        $rules = $filter->listRules();

        if (empty($rules)) {
            $this->info('No filter rules configured');
            return 0;
        }

        $this->table(
            ['#', 'Repository', 'Event', 'Type'],
            array_map(function ($rule, $index) {
                return [
                    $index + 1,
                    $rule['repository'],
                    $rule['action'] ?? 'ALL',
                    strtoupper($rule['type'])
                ];
            }, $rules, array_keys($rules))
        );

        return 0;
    }

    private function addRule(RepositoryFilter $filter): int
    {
        $repository = $this->argument('repository');
        $event = $this->argument('event');

        if (!$repository) {
            $this->error('Repository pattern is required');
            $this->line('Usage: tgn:filter add <repository> [event]');
            $this->line('Examples:');
            $this->line('  tgn:filter add myorg/test-repo');
            $this->line('  tgn:filter add myorg/test-repo push');
            $this->line('  tgn:filter add "myorg/*" pull_request');
            return 1;
        }

        $filter->addRule($repository, $event, 'ignore');

        if ($event) {
            $this->info("Added filter: Ignore '{$event}' events from '{$repository}'");
        } else {
            $this->info("Added filter: Ignore all events from '{$repository}'");
        }

        return 0;
    }

    private function removeRule(RepositoryFilter $filter): int
    {
        $repository = $this->argument('repository');
        $event = $this->argument('event');

        if (!$repository) {
            $this->error('Repository pattern is required');
            return 1;
        }

        $removed = $filter->removeRule($repository, $event);

        if ($removed) {
            $this->info('Filter rule removed');
        } else {
            $this->warn('No matching filter rule found');
        }

        return 0;
    }

    private function clearRules(RepositoryFilter $filter): int
    {
        if (!$this->confirm('Are you sure you want to clear all filter rules?', false)) {
            $this->info('Cancelled');
            return 0;
        }

        $filter->clearRules();
        $this->info('All filter rules cleared');

        return 0;
    }

    private function showUsage(): void
    {
        $this->line('');
        $this->line('Usage:');
        $this->line('  tgn:filter enable                    - Enable filtering');
        $this->line('  tgn:filter disable                   - Disable filtering');
        $this->line('  tgn:filter status                    - Show filter status');
        $this->line('  tgn:filter list                      - List all filter rules');
        $this->line('  tgn:filter add <repo> [event]        - Add ignore rule');
        $this->line('  tgn:filter remove <repo> [event]     - Remove rule');
        $this->line('  tgn:filter clear                     - Clear all rules');
        $this->line('');
        $this->line('Examples:');
        $this->line('  tgn:filter add myorg/test-repo                - Ignore all events from repo');
        $this->line('  tgn:filter add myorg/test-repo push           - Ignore push events only');
        $this->line('  tgn:filter add "myorg/test-*"                 - Ignore all repos matching pattern');
        $this->line('  tgn:filter add "myorg/*" pull_request         - Ignore PRs from all org repos');
        $this->line('');
        $this->line('Supported events:');
        $this->line('  push, pull_request, issues, release, fork, deployment, workflow_run, etc.');
    }
}
