<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class FixOrganizationIds extends Command
{
    protected $signature = 'fix:organization-ids';

    protected $description = 'Ensure all organization_accounts have the correct organization_id key (UUID child key)';

    protected $database;

    public function __construct()
    {
        parent::__construct();

        $factory = (new Factory())
            ->withServiceAccount(storage_path('firebase/sdao-system-firebase-adminsdk-fbsvc-3a7070d3f6.json'))
            ->withDatabaseUri('https://sdao-system-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->database = $factory->createDatabase();
    }

    public function handle()
    {
        $this->info('Starting to fix organization_ids...');

        // 1) Get all organizations
        $organizationsRef = $this->database->getReference('organizations')->getValue();

        // Build a simple lookup by name => key
        $lookup = [];
        foreach ($organizationsRef as $key => $value) {
            if (is_array($value) && isset($value['name'])) {
                $lookup[$value['name']] = $key;
            }
        }

        $this->info('Loaded ' . count($lookup) . ' organizations');

        // 2) Get all organization accounts
        $accountsRef = $this->database->getReference('organization_accounts')->getValue();

        $fixed = 0;
        foreach ($accountsRef as $accountKey => $account) {
            if (isset($account['organization_id']) && strlen($account['organization_id']) < 40) {
                // Assume it is a name, not a UUID key
                $name = $account['organization_id'];
                $correctKey = $lookup[$name] ?? null;

                if ($correctKey && $correctKey !== $account['organization_id']) {
                    // Fix it
                    $this->database->getReference('organization_accounts/' . $accountKey . '/organization_id')
                        ->set($correctKey);

                    $this->info("Fixed account {$accountKey}: '{$name}' => '{$correctKey}'");
                    $fixed++;
                }
            }
        }

        $this->info("Done! Fixed {$fixed} accounts.");
    }
}
