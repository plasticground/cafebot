<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class KeyCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'key';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Generate 32 character random string";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info(Str::random(32));
    }
}
