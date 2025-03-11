<?php

namespace App\Http\Requests\Jira\Issue;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetSearchIssuesRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/search/jql';
    }

    public function setProject(string $projectSlug): GetSearchIssuesRequest
    {
        $this
            ->query()
            ->add(
                key: 'jql',
                value: sprintf($this->query()->get('jql'), $projectSlug)
            );

        return $this;
    }

    protected function defaultQuery(): array
    {
        return [
            'jql' => 'project = %s AND status NOT IN (Done, "Closed for now") AND status IN ("To Do", "On hold", "In Progress") AND sprint in openSprints() ORDER BY created DESC',
            'expand' => 'renderedFields',
            'maxResults' => 10,
            'fields' => 'id,title,summary,description',
        ];
    }
}
