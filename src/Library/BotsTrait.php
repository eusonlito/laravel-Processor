<?php
namespace Eusonlito\LaravelProcessor\Library;

use Input;
use Request;

trait BotsTrait
{
    protected static $bots = __DIR__.'/../resources/bots.txt';
    protected static $fake_fields = ['fake_email', 'fake_url'];

    protected static function isFake($post, $form)
    {
        $method = strtolower(Request::method());

        if (($form === null) && ($method === 'get')) {
            $token = true;
        } else {
            $token = (isset($post['_token']) && (csrf_token() === $post['_token']));
        }

        $fake = ($method === 'post') ? self::$fake_fields : [];

        return (($token === false) || self::isBot($post, $fake));
    }

    protected static function isBot(array $data = [], array $fake = [])
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            foreach (file(self::$bots, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $bot) {
                if (preg_match('#'.preg_quote($bot, '#').'#', $_SERVER['HTTP_USER_AGENT'])) {
                    return true;
                }
            }
        }

        foreach ($fake as $input) {
            if (!empty($data[$input])) {
                return true;
            }
        }

        return false;
    }
}
