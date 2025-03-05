<?php

declare(strict_types=1);

namespace App\Http\Connectors\Jira;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;

class JiraConnector extends Connector
{
    public function __construct(
        public readonly string $siteUrl,
        public readonly string $email,
        public readonly string $apiToken,
    ) {}

    public function resolveBaseUrl(): string
    {
        return sprintf('https://%s.atlassian.net/rest/api/3/', $this->siteUrl);
    }

    protected function defaultAuth(): ?Authenticator
    {
        return new BasicAuthenticator(
            $this->email,
            $this->apiToken,
        );
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
