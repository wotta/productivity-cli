<?php

declare(strict_types=1);

namespace App\Http\Connectors\Jira;

use Saloon\Http\Connector;

class JiraConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return sprintf('https://%s.atlassian.net/rest/api/3/', config('services.jira.domain'));
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
