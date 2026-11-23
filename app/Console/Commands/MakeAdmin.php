<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

// The controlled way to get a platform admin besides DemoAccountsSeeder: the admin role is never
// offered by a public form nor by the backoffice. Asks for confirmation before any change.
class MakeAdmin extends Command
{
    protected $signature = 'foodsy:make-admin {email : email of the account to promote or create}';

    protected $description = 'Promote an account to platform admin, or create a new admin account';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        if (Validator::make(['email' => $email], ['email' => ['required', 'email']])->fails()) {
            $this->error('Not a valid email address.');

            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if ($user?->isAdmin()) {
            $this->info("{$email} is already an admin.");

            return self::SUCCESS;
        }
        if ($user?->isBanned()) {
            $this->error("{$email} is banned: lift the ban from the backoffice first.");

            return self::FAILURE;
        }

        if ($user) {
            if (! $this->confirm("Promote {$user->name} {$user->last_name} ({$email}, now {$user->role}) to platform admin?")) {
                return self::FAILURE;
            }
            $user->role = User::ADMIN;
            $user->save();
            $this->info("{$email} is now an admin.");

            return self::SUCCESS;
        }

        if (! $this->confirm("No account with {$email}: create a new admin?")) {
            return self::FAILURE;
        }
        $name = $this->ask('First name');
        $lastName = $this->ask('Last name');
        $password = $this->secret('Password (min. 8 characters)');
        $check = Validator::make(
            ['name' => $name, 'last_name' => $lastName, 'password' => $password],
            ['name' => ['required', 'max:255'], 'last_name' => ['required', 'max:255'], 'password' => ['required', Password::min(8)]],
        );
        if ($check->fails()) {
            foreach ($check->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $admin = new User(['name' => $name, 'last_name' => $lastName, 'email' => $email, 'password' => Hash::make($password)]);
        $admin->role = User::ADMIN;
        $admin->email_verified_at = now();
        $admin->save();
        $this->info("Admin {$email} created.");

        return self::SUCCESS;
    }
}
