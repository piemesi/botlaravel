<?php
/**
 * Created by PhpStorm.
 * User: malgrat
 * Date: 09.07.17
 * Time: 13:46
 */

namespace App\Services\TT;


use App\Period;
use App\Task;

class TTController implements ITT
{

    protected $taskSender;

    protected $repo;

    function __construct(TTRepository $repository)
    {
        $this->repo = $repository;
        $this->taskSender = new TT();
    }

    public function getChannelAllPosts(int $channelId, bool $unSent = false, bool $sent = false, bool $deleted = false)
    {
        $tasks = $this->repo->getChannelAllPosts($channelId, $unSent, $sent, $deleted);
        return $tasks;
    }

    public function getPostByHash(string $hash)
    {
        return $this->repo->getPostByHash($hash);
    }

    public function checkChannelsPosts(int $channelId = 0)
    {
        $posts = $channelId ? $this->repo->getPostToSendByChannelId($channelId) : $this->repo->getAllPostToSend();
//        print_r($posts->toArray());

        $sentItems = $this->taskSender->sendPosts($posts->toArray());

        if ($sentItems) {
            foreach ($sentItems as $sentPeriodId => $telegramMessageId) {
                echo $sentPeriodId.'--->'.$telegramMessageId.PHP_EOL;

                Period::find($sentPeriodId)->update(['telegram_message_id' => $telegramMessageId]);
                Period::find($sentPeriodId)->delete();
            }
        }

    }

}