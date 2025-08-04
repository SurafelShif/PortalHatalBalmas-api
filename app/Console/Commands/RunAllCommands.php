<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunAllCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run multiple Artisan commands in sequence';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $this->call('optimize:clear');
        $this->call('migrate:fresh', ['--seed' => true]);
        // Create password grant client
        $this->call('passport:client', [
            '--password' => true,
            '--name' => config('auth.passport_tokens.password_client_name'),
            '--provider' => 'users',
        ]);
        // create personalAccessTokens
        // $this->call('passport:client', [
        //     '--personal' => true,
        //     '--name' => config('auth.token_lifetime.token_name'),
        // ]);
        $this->call('l5-swagger:generate');
        $this->call('optimize:clear');
        $this->call('serve');
    }
}
