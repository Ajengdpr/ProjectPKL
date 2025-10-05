<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Contract\Database;

class AbsensiResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'absensi:reset {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate the absensi table and clear the entire rekap node in Firebase.';

    /**
     * The Firebase Realtime Database instance.
     *
     * @var \Kreait\Firebase\Contract\Database
     */
    protected $database;

    /**
     * Create a new command instance.
     *
     * @param  \Kreait\Firebase\Contract\Database  $database
     * @return void
     */
    public function __construct(Database $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            $this->warn('This will delete ALL attendance records and reset the Firebase rekap.');
            if (!$this->confirm('Do you wish to continue?')) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        // Truncate the absensi table
        DB::table('absensi')->truncate();
        $this->info('Absensi table truncated successfully.');

        // Clear the Firebase rekap node
        $this->database->getReference('rekap')->remove();
        $this->info('Firebase rekap node cleared successfully.');

        $this->info('Absensi reset complete.');

        return self::SUCCESS;
    }
}
