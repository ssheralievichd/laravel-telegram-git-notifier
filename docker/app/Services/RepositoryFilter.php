<?php

namespace App\Services;

class RepositoryFilter
{
    private string $storageFile;
    private array $filters;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/repository-filter.php';
        $this->storageFile = $config['storage_path'];
        $this->loadFilters();
    }

    private function loadFilters(): void
    {
        if (file_exists($this->storageFile)) {
            $content = file_get_contents($this->storageFile);
            $this->filters = json_decode($content, true) ?? [];
        } else {
            $this->filters = [
                'enabled' => false,
                'rules' => []
            ];
        }
    }

    private function saveFilters(): void
    {
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            $this->storageFile,
            json_encode($this->filters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public function isEnabled(): bool
    {
        return $this->filters['enabled'] ?? false;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->filters['enabled'] = $enabled;
        $this->saveFilters();
    }

    public function addRule(string $repository, ?string $action = null, string $type = 'ignore'): void
    {
        $rule = [
            'repository' => $repository,
            'action' => $action,
            'type' => $type
        ];

        $this->filters['rules'][] = $rule;
        $this->saveFilters();
    }

    public function removeRule(string $repository, ?string $action = null): bool
    {
        $found = false;

        foreach ($this->filters['rules'] as $index => $rule) {
            if ($rule['repository'] === $repository && $rule['action'] === $action) {
                unset($this->filters['rules'][$index]);
                $found = true;
            }
        }

        if ($found) {
            $this->filters['rules'] = array_values($this->filters['rules']);
            $this->saveFilters();
        }

        return $found;
    }

    public function listRules(): array
    {
        return $this->filters['rules'] ?? [];
    }

    public function clearRules(): void
    {
        $this->filters['rules'] = [];
        $this->saveFilters();
    }

    public function shouldProcess(array $payload): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if (!isset($payload['repository']['full_name'])) {
            return true;
        }

        $repoFullName = $payload['repository']['full_name'];
        $eventType = $this->getEventType($payload);

        foreach ($this->filters['rules'] as $rule) {
            if ($this->matchesRule($repoFullName, $eventType, $rule)) {
                return $rule['type'] !== 'ignore';
            }
        }

        return true;
    }

    private function matchesRule(string $repoFullName, string $eventType, array $rule): bool
    {
        if (!$this->matchesPattern($repoFullName, $rule['repository'])) {
            return false;
        }

        if ($rule['action'] === null) {
            return true;
        }

        return strcasecmp($eventType, $rule['action']) === 0;
    }

    private function matchesPattern(string $value, string $pattern): bool
    {
        $pattern = trim($pattern);

        if (empty($pattern)) {
            return false;
        }

        if (str_contains($pattern, '*')) {
            $regex = '/^' . str_replace(['\*', '/'], ['.*', '\/'], preg_quote($pattern, '/')) . '$/i';
            return preg_match($regex, $value) === 1;
        }

        return strcasecmp($value, $pattern) === 0;
    }

    private function getEventType(array $payload): string
    {
        if (isset($payload['pusher'])) {
            return 'push';
        }

        if (isset($payload['pull_request'])) {
            return 'pull_request';
        }

        if (isset($payload['issue']) && !isset($payload['pull_request'])) {
            return 'issues';
        }

        if (isset($payload['release'])) {
            return 'release';
        }

        if (isset($payload['fork'])) {
            return 'fork';
        }

        if (isset($payload['deployment'])) {
            return 'deployment';
        }

        if (isset($payload['workflow_run'])) {
            return 'workflow_run';
        }

        if (isset($payload['workflow_job'])) {
            return 'workflow_job';
        }

        if (isset($payload['merge_request'])) {
            return 'merge_request';
        }

        if (isset($payload['pipeline'])) {
            return 'pipeline';
        }

        if (isset($payload['object_kind'])) {
            return $payload['object_kind'];
        }

        return '';
    }
}
