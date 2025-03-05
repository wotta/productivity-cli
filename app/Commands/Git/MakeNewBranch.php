<?php

declare(strict_types=1);

namespace App\Commands\Git;

use App\Http\Connectors\Jira\JiraConnector;
use App\Http\Requests\Jira\Issue\GetSearchIssuesRequest;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

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

        dd($jira->send(new GetSearchIssuesRequest)->json());

        return Command::SUCCESS;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
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
