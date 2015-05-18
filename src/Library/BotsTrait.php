<?php
namespace Eusonlito\LaravelProcessor\Library;

use Input;
use Request;

trait BotsTrait
{
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
        $bots = [
            'ask jeeves','baiduspider','butterfly','fast','feedfetcher-google','firefly','gigabot',
            'googlebot','infoseek','me.dium','mediapartners-google','nationaldirectory','rankivabot',
            'scooter','slurp','sogou web spider','spade','tecnoseek','technoratisnoop','teoma',
            'tweetmemebot','twiceler','twitturls','url_spider_sql','webalta crawler','webbug',
            'webfindbot','zyborg','alexa','appie','crawler','froogle','girafabot','inktomi',
            'looksmart','msnbot','rabaz','www.galaxy.com','rogerbot',
        ];

        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        foreach ($bots as $bot) {
            if (strstr($agent, $bot) !== false) {
                return true;
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
