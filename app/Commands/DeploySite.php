<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Laravel\Forge\Exceptions\ValidationException;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;

class DeploySite extends Command
{
    protected $signature = 'app:deploy-site
        {forge-cli-token : The Forge CLI token to use}
        {forge-server-id : The Forge server ID to use}
        {root-domain : The root domain to use (example.com)}
        {git-provider : The git provider to use (github, gitlab)}
        {repository : The repository to use (username/repository)}
        {branch : The name of the branch to deploy}
        {subdomain : The subdomain to use for the site and the site\'s database)}
        {php-version : The PHP version to use (php82, php81, php80, php74)}
        {env : Base64 encoded .env file to use for the site}';

    protected $description = 'Deploys the given branch to the server.';

    protected string $domain;
    protected Forge $forge;
    protected string $forgeApiToken;
    protected string $forgeServerId;

    public function handle(): void
    {
        $this->buildForge();
        $this->domain = $this->argument('subdomain') . '.' . $this->argument('root-domain');

        try {
            $site = $this->getSite();
            if (!$site) {
                $site = $this->createSite();
            }

            $this->deploySite($site);
        } catch (ValidationException $e) {
            $this->output->error($e->errors());
        }
    }

    protected function getSite(): ?Site
    {
        $this->output->info("Checking for site: {$this->domain}");

        $sites = collect($this->forge->sites($this->forgeServerId));

        return $sites->first(fn (Site $site) => $site->name === $this->domain);
    }

    protected function createSite(): Site
    {
        $this->output->info('Site not found, creating site...');

        $site = $this->forge->createSite($this->forgeServerId, [
            'domain' => $this->domain,
            'database' => $this->argument('subdomain'),
            'project_type' => 'php',
            'php_version' => $this->argument('php-version'),
            'aliases' => [],
            'directory' => '/public',
            'isolated' => false,
        ]);

        $this->output->info('Site created, installing git repository...');
        $this->forge->installGitRepositoryOnSite($this->forgeServerId, $site->id, [
            'provider' => $this->argument('git-provider'),
            'repository' => $this->argument('repository'),
            'branch' => $this->argument('branch'),
            'composer' => true
         ]);

        $this->forge->obtainLetsEncryptCertificate($this->forgeServerId, $site->id, [
            'domains' => [$this->domain]
        ], $wait = true);

        return $site;
    }

    protected function deploySite(Site $site): void
    {
        $this->output->info('Updating site .env file...');
        $this->forge->updateSiteEnvironmentFile($this->forgeServerId, $site->id, base64_decode($this->argument('env')));

        $this->output->info('Deploying site...');
        $this->forge->deploySite($this->forgeServerId, $site->id);
    }

    protected function buildForge(): void
    {
        $this->forgeApiToken = $this->argument('forge-cli-token');
        $this->forgeServerId = $this->argument('forge-server-id');

        if (empty($this->forgeApiToken)) {
            $this->forgeApiToken = $this->ask('What is your Forge API token?');
        }
        if (empty($this->forgeServerId)) {
            $this->forgeServerId = $this->ask('What is your Forge Server ID?');
        }

        $this->forge = (new Forge($this->forgeApiToken))->setTimeout(180);
    }
}
