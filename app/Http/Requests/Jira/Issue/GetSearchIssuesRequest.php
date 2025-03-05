<?php

namespace App\Http\Requests\Jira\Issue;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetSearchIssuesRequest extends Request
{
    protected Method $method = MEthod::GET;

    public function resolveEndpoint(): string
    {
        return '/search/jql';
    }

    protected function defaultQuery(): array
    {
        return [
            'jql' => 'project = PROJ AND status NOT IN (Done, "Closed for now") AND sprint != empty ORDER BY created DESC',
            'maxResults' => 10,
            'fields' => 'id,title,summary'
        ];
    }
}
