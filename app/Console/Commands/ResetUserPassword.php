<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password {email? : User email} {password? : New password}';
    protected $description = 'Reset a user password. If no arguments given, interactive prompts are shown.';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (!$email) {
            $email = $this->ask('User email');
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        if (!$password) {
            $password = $this->secret('New password (min 6 chars)');
        }

        $user->password = bcrypt($password);
        $user->save();

        $this->info("Password for {$user->name} ({$email}) has been reset successfully.");
        return 0;
    }
}
