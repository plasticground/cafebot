<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class UserCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'make:user
        {--name= : Name}
        {--email= : Email}
        {--password= : Password}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create a new user";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $name = $this->option('name');
            $email = $this->option('email');
            $password = !empty($this->option('password'))
                    ? app('hash')->make($this->option('password'))
                    : null;

            $this->info('Name: '  . $name);
            $this->info('Email: '  . $email);
            $this->info('Password (hash): '  . $password);

            $user = User::create(compact('name', 'email', 'password'));

            $this->info('User created - ' . $user->id . '#: ' . $user->name);
        } catch (\Throwable $e) {
            if (empty($name) || empty($email) || empty($password)) {
                $this->warn('Use: php artisan make:user --name=Adam --email=email@mail.com --password=qwerty' . PHP_EOL);
            }

            $this->error($e->getMessage());
        }
    }
}
