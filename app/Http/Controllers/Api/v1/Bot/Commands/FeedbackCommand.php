<?php

namespace App\Http\Controllers\Api\v1\Bot\Commands;


use App\Models\Client;
use App\Models\Feedback;
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

        $feedback = Feedback::first();

        return $this->replyWithMessage([
            'text' => $feedback->{$locale . '_text'} ?? 'No info',
            'parse_mode' => 'markdown'
        ]);
    }
}
