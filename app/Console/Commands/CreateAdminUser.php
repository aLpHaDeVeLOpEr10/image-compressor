<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('admin:create {--name= : The admin\'s name} {--email= : The admin\'s email address} {--password= : The password (prompted when omitted)}')]
#[Description('Create an admin user, or grant admin access and reset the password of an existing user')]
class CreateAdminUser extends Command
{
    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?? text('Name', required: true),
            'email' => $this->option('email') ?? text('Email', required: true),
            'password' => $this->option('password') ?? password('Password', required: true),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => $data['email']],
            ['name' => $data['name'], 'password' => $data['password']],
        );
        $user->forceFill(['is_admin' => true])->save();

        $this->components->info("Admin user [{$user->email}] is ready.");

        return self::SUCCESS;
    }
}
