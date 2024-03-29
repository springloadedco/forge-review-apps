<?php

namespace App\Commands;

use App\Traits\UseForgeSdk;
use Illuminate\Support\Str;
use LaravelZero\Framework\Commands\Command;
use Laravel\Forge\Exceptions\ValidationException;
use Laravel\Forge\Resources\Database;

class DeleteSite extends Command
{
    use UseForgeSdk;

    protected $signature = 'app:delete-site
        {forge-cli-token : The Forge CLI token to use}
        {forge-server-id : The Forge server ID to use}
        {root-domain : The root domain to use (example.com)}
        {subdomain : The subdomain of the site}';

    protected $description = 'Deletes the site on the server, including the database.';

    protected string $domain;
    protected string $databaseName;

    public function handle(): void
    {
        $this->buildForge();
        $this->domain = $this->argument('subdomain') . '.' . $this->argument('root-domain');
        $this->databaseName = Str::replace('-', '_', $this->argument('subdomain'));

        try {
            $this->deleteSite();
            $this->deleteDatabase();
        } catch (ValidationException $e) {
            $this->output->error($e->errors());
        }
    }

    protected function getDatabase(): ?Database
    {
        $this->output->info('Checking for database...');

        $databases = collect($this->forge->databases($this->forgeServerId));

        return $databases->first(fn (Database $database) => $database->name === $this->databaseName);
    }

    protected function deleteSite(): void
    {
        $site = $this->getSite($this->domain);

        if ($site) {
            $this->output->info('Deleting site...');
            $this->forge->deleteSite($this->forgeServerId, $site->id);
        }
    }

    protected function deleteDatabase(): void
    {
        $database = $this->getDatabase();

        if ($database) {
            $this->output->info('Deleting database...');
            $this->forge->deleteDatabase($this->forgeServerId, $database->id);
        }
    }

}
