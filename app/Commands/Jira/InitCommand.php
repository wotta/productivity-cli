<?php

declare(strict_types=1);

namespace App\Commands\Jira;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\text;

class InitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jira:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Jira settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $siteUrl = text(
            label: 'What is the site url for Jira?',
            placeholder: 'your-domain',
            default: (string) Cache::get('jira.site-url', ''),
            required: true,
            hint: 'Jira uses the following format: https://your-domain.atlassian.net/ where "your-domain" is the site url',
            transform: function (string $siteUrl) {
                $siteUrl = Str::of($siteUrl);

                return $siteUrl->when($siteUrl->isUrl(), function (Stringable $str) {
                    return $str->after('https://')->before('.atlassian.net/');
                });
            }
        );

        Cache::put('jira.site-url', $siteUrl);
        $this->comment('Store site url');;

        $apiToken = text(
            label: 'API token',
            required: true,
            hint: 'API tokens can be made at the following url: https://id.atlassian.com/manage-profile/security/api-tokens',
        );

        Cache::put('jira.api-token', $apiToken);
        $this->comment('API token is stored');

        $this->info('Saved settings successfully');

        return Command::SUCCESS;
    }
}
