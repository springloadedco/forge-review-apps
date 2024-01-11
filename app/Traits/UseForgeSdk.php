<?php

namespace App\Traits;

use LaravelZero\Framework\Commands\Command;
use Laravel\Forge\Forge;
use Laravel\Forge\Resources\Site;

/** @mixin Command */
trait UseForgeSdk {
    protected Forge $forge;
    protected string $forgeApiToken;
    protected string $forgeServerId;

    protected function getSite(string $domain): ?Site
    {
        $this->output->info("Checking for site: {$domain}");

        $sites = collect($this->forge->sites($this->forgeServerId));

        return $sites->first(fn (Site $site) => $site->name === $domain);
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

        $this->forge = new Forge($this->forgeApiToken);
    }
}
