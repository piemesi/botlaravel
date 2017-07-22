<?php
/**
 * Created by PhpStorm.
 * User: malgrat
 * Date: 09.07.17
 * Time: 14:11
 */

namespace App\Services\TT;


use App\Channel;
use App\Task;
use Carbon\Carbon;

class TTRepository
{
    // @toDo неотправленные -> время прошло, но periods.deleted_at (telegram_message_id) -> null

    public function getChannelAllPosts(int $channelId, bool $unSent = false, bool $sent = false, bool $deleted = false)
    {
        $tasksSel = Task::where(['channel_id' => $channelId]);

        if ($unSent) {
            $now = Carbon::now('Europe/Moscow');
            $now->addSeconds(5);

//            $tasksSel->where(['active' => 1, 'sent' => null]);
            $tasksSel->where(['active' => 1])
                ->whereHas('periods', function ($query) use ($now) {
                    $query->where('start', '>', $now);
                });
        }

        if ($sent) {

            $tasksSel
                ->whereHas('periods', function ($query) {
                    $query->where('telegram_message_id', '>', 0)->withTrashed();
                });

//            $tasksSel->where(['sent' => 1]);
        }

        $tasksSel->with('periods');

        if ($deleted) {
            $tasksSel->withTrashed();
        }

        $tasks = $tasksSel->get()->toArray();

        return $tasks;
    }

    public function getPostByHash(string $hash)
    {
        return Task::where(['hash' => $hash])->first();
    }

    public function getChannelsByCompanyId(int $companyId)
    {
        return Channel::where(['company_id' => $companyId])->get()->toArray();
    }

    public function getAllPostToSend()
    {
        $now = Carbon::now('Europe/Moscow');
        $now->addSeconds(5);
        $posts = Task::where('active', 1)
            ->whereHas('periods', function ($query) use ($now) {
                $query->where('start', '<', $now);
            })->with('periods')->get();
        return $posts;
    }

    public function getPostToSendByChannelId(int $channelId)
    {
        $now = Carbon::now('Europe/Moscow');
        $now->addSeconds(5);

        $posts = Task::where(['channel_id' => $channelId, 'active' => 1])
            ->whereHas('periods', function ($query) use ($now) {
                $query->where('start', '<', $now);
            })->with('periods')->get();
        return $posts;
    }
}