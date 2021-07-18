<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class HelpCommand extends Command
{

    /**
     * @var string Command Name
     */
    protected $name = 'help';

    /**
     * @var string Command Description
     */
    protected $description = 'Help command';
    /**
     * @var array
     */
    protected $helpText = [
        "Информация по коммандам:",
        "",
        "test",
        "<b>endtest cmd ☺</b>",
    ];

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        return $this->replyWithMessage([
            'text' => implode(PHP_EOL, $this->helpText),
            'parse_mode' => 'html'
        ]);
    }
}
