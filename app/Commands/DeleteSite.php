<?php

namespace App\Commands;

use App\Traits\UseForgeSdk;
use LaravelZero\Framework\Commands\Command;
use Laravel\Forge\Exceptions\ValidationException;
use Laravel\Forge\Resources\Database;

class DeleteSite extends Command
{
    use UseForgeSdk;

    protected $signature = 'app:delete-site
        {branch : The name of the branch to delete.}';

    protected $description = 'Deletes the site on the server, including the database.';

    public function handle(): void
    {
        $this->buildForge();
        $domain = $this->argument('branch') . '.' . config('forge.root_domain');

        try {
            $this->deleteSite();
            $this->deleteDatabase();
        } catch (ValidationException $e) {
            $this->output->error($e->errors());
        }
    }

    protected function getDatabase(): Database
    {
        $this->output->info('Checking for database...');

        $databases = collect($this->forge->databases($this->forgeServerId));

        return $databases->first(fn (Database $database) => $database->name === $this->argument('branch'));
    }

    protected function deleteSite(): void
    {
        $domain = $this->argument('branch') . '.' . config('forge.root_domain');
        $site = $this->getSite($domain);

        $this->output->info('Deleting site...');
        $this->forge->deleteSite($this->forgeServerId, $site->id);
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
