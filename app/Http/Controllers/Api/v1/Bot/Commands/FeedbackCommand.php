<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use App\Models\Client;
use Telegram\Bot\Commands\Command;

/**
 * Class HelpCommand.
 */
class FeedbackCommand extends Command
{

    /**
     * @var string Command Name
     */
    protected $name = 'feedback';

    /**
     * @var string Command Description
     */
    protected $description = 'Feedback command';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $client = Client::whereTelegramId($this->getUpdate()->message->from->id)->first();
        $locale = 'ua';

        if ($client) {
            $locale = $client->locale;
        }

        return $this->replyWithMessage([
            'text' => "Всякая информация про обратную связь на {$locale} языке"
        ]);
    }
}
