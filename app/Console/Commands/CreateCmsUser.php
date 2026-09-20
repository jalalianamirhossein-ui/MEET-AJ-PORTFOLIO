<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateCmsUser extends Command
{
    protected $signature = 'cms:create-user
        {--name= : Display name}
        {--email= : Login email}
        {--role= : admin or editor}
        {--password= : Password with 12 or more characters; omit for a hidden prompt}';

    protected $description = 'Create an admin or editor without shipping a default password';

    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?: $this->ask('Name'),
            'email' => $this->option('email') ?: $this->ask('Email'),
            'role' => $this->option('role') ?: $this->choice('Role', ['admin', 'editor'], 0),
            'password' => $this->option('password') ?: $this->secret('Password (12+ characters)'),
        ];

        Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'role' => [Rule::in(['admin', 'editor'])],
            'password' => 'required|string|min:12|max:72',
        ])->validate();

        if (! $this->option('password')) {
            if ($data['password'] !== $this->secret('Repeat password')) {
                $this->error('Passwords do not match.');

                return self::FAILURE;
            }
        }

        $user = new User;
        $user->forceFill($data)->save();
        $this->info('CMS user created for '.$user->email.' as '.$user->role.'.');

        return self::SUCCESS;
    }
}
