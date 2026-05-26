<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetAdminPasswordCommand extends Command
{
    protected $signature = 'admin:reset-password {email=admin@store.com} {password?}';
    protected $description = 'Reset the password for an admin account.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (!$password) {
            $password = $this->secret('Enter the new password');
            if (!$password) {
                $this->error('Password is required.');
                return 1;
            }
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info("Password for {$email} has been reset successfully.");
        $this->line('Use the new password to log in, then change it immediately from the admin profile.');

        return 0;
    }
}
