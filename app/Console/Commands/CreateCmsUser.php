<?php
namespace App\Console\Commands;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class CreateCmsUser extends Command
{
    protected $signature = 'cms:create-user';
    protected $description = 'Interactively create an admin or editor without a default password';
    public function handle(): int {
        $data = ['name' => $this->ask('Name'), 'email' => $this->ask('Email'), 'role' => $this->choice('Role', ['admin', 'editor'], 1), 'password' => $this->secret('Password (12+ characters)')];
        Validator::make($data, ['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users', 'role' => [Rule::in(['admin', 'editor'])], 'password' => 'required|string|min:12|max:72'])->validate();
        if ($data['password'] !== $this->secret('Repeat password')) { $this->error('Passwords do not match.'); return self::FAILURE; }
        $user = new User; $user->forceFill($data)->save(); $this->info('CMS user created.'); return self::SUCCESS;
    }
}
