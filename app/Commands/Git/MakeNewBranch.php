<?php

declare(strict_types=1);

namespace App\Commands\Git;

use App\Http\Connectors\Jira\JiraConnector;
use App\Http\Requests\Jira\Issue\GetSearchIssuesRequest;
use ArdaGnsrn\Ollama\Ollama;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\select;

class MakeNewBranch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:new:branch {name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new branch within the current working directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->hasJiraAuthenticationSettings()) {
            return Command::FAILURE;
        }

        $jira = new JiraConnector(
            siteUrl: Cache::get('jira.site-url'),
            email: Cache::get('jira.email'),
            apiToken: Cache::get('jira.api-token'),
        );

        $getSearchIssuesRequest = new GetSearchIssuesRequest();
        $jiraIssues = $jira->send(
            $getSearchIssuesRequest
                ->setProject('JAM')
        )->json('issues');

        $selectedIssue = select(
            'For which ticket would you like to create a new branch?',
            options: Arr::pluck($jiraIssues, 'fields.summary', 'key'),
        );

        $selectedIssue = Arr::first($jiraIssues, function ($value, $key) use ($selectedIssue) {
            return $value['key'] === $selectedIssue;
        });

        $key = $selectedIssue['key'];
        $description = $selectedIssue['renderedFields']['description'];
        $summary = $selectedIssue['fields']['summary'];

        $client = Ollama::client();

        $result = $client->chat()->create([
            'model' => 'llama3.2',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => sprintf(
                        '
                            Create a local branch name using the following format: {KEY}-{title: sensible short text based on description and or summary}.
                            All branch names should be written in English.

                            key: %s
                            description: %s
                            summary: %s

                            rule:
                            - Branch names in English
                            - No spaces but `-`
                            - Always start with the {KEY}
                            - Only return the most logic likely to option if multiple could be feasible
                            - Only one branch name without any other text

                            Examples:

                            PROJ123-this-is-an-example-branch
                            SEC25-implement-oauth-authentication

                            Incorrect examples which should not be suggested:

                            - WOT-1466 - Saving a page in the admin doesn\'t redirect to the index pages
                              - The format in question is incorrect since it doesn\'t use `-` where spaces are.
                            - PROJ-1239-mailings-and-actions-inschrijvingen-campagnes
                              - This is incorrect because it uses both English and Dutch while it should be English only.

                            <output>
                            branch name.
                            </output>
                        ',
                        $key,
                        $description,
                        $summary,
                    ),
                ],
            ],
        ]);

        dd($result);

        // Format: KEY-sensible-branch-title

        return Command::SUCCESS;
    }

    private function hasJiraAuthenticationSettings(): bool
    {
        $siteUrl = Cache::get('jira.site-url');
        $email = Cache::get('jira.email');
        $apiToken = Cache::get('jira.api-token');

        if (empty($siteUrl) || empty($email) || empty($apiToken)) {
            $this->error('No site-url, email, or api-token found');

            return false;
        }

        return true;
    }
}
