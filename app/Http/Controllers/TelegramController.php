<?php

namespace App\Http\Controllers;

use App\Period;
use App\Services\TT\ITT;
use App\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
//use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Commands\Command;
use Telegram;

class TelegramController extends Controller
{

    protected $tt;

    public function __construct(ITT $ITT)
    {
        $this->tt = $ITT;
    }

    public function getChannelPostsSent(Request $request, $channelId = 1)
    {
        $tasks = $this->tt->getChannelAllPosts($channelId, false, true);
        return response()->json(['tasks' => $tasks]);
    }

    public function getChannelPostsUnSent(Request $request, $channelId = 1)
    {
        $tasks = $this->tt->getChannelAllPosts($channelId, true);
        return response()->json(['tasks' => $tasks]);
    }

    public function getChannelPosts(Request $request, $channelId = 1)
    {
        $tasks = $this->tt->getChannelAllPosts($channelId);
        return response()->json(['tasks' => $tasks]);
    }

    public function getPost(Request $request, $hash)
    {
        $task = $this->tt->getPostByHash($hash);
        return response()->json(['post' => $task]);
    }

//    private function getPostByHash($hash)
//    {
//        return Task::where(['hash' => $hash])->first();
//    }

    public function increasePostShows(Request $request, $postHash)
    {
        $task = $this->tt->getPostByHash($postHash);
        $task->increment('shows');
        return response()->json(['post' => $task]);
    }


    public function saveData(Request $request)
    {
//        file_put_contents('./body5.txt',print_r($_REQUEST,1));
        file_put_contents('./body11.txt', print_r($request->all(), 1));


        $input = $request->all();
        $task = new Task();
        $task->title = $input['title'];
        $task->text = $input['text'];
        $task->preview = $input['preview'] ?? '';
        $task->need_link = $input['need_link'] === 'true' ? 1 : 0;
        $task->company_id = 1;
        $task->hash = md5($task->text . time());
        $task->channel_id = 1;
        $task->minutes_to_read = $input['minutes_to_read'] ?? 0;
        $task->hide = 0;
        $task->save();


        $dates = explode(',', $input['dates']);
        foreach ($dates as $date) {
            $periods = new Period();
            $periods->start = new Carbon($date);
            $periods->task_id = $task->id;
            $periods->save();
        }


//        file_put_contents('/data.txt',print_r($request, true));
//
//        print_r($request);
        return '';
    }


//    private function getChannelAllPosts($channelId, bool $unSent = false, bool $sent = false, bool $deleted = false)
//    {
//
////        $tasksSel = Task::where(['channel_id' => $channelId]);
////
////        if ($unSent) {
////            $tasksSel->where(['active' => 1, 'sent' => null]);
////        }
////
////        if ($sent) {
////            $tasksSel->where(['sent' => 1]);
////        }
////
////        $tasksSel->with('periods');
////
////        if ($deleted) {
////            $tasksSel->withTrashed();
////        }
////
////
////        $tasks = $tasksSel->get()->toArray();
////
////        return $tasks;
//    }

    public function updateData(Request $request)
    {
        file_put_contents('./bodyUpdate.txt', print_r($request->all(), 1));

        $input = $request->all();
        $input['active'] = $input['active'] === 'true' ? 1 : 0;
        $input['hide'] = $input['hide'] === 'true' ? 1 : 0;
        if ($input['hide']) {

            Task::where('id', $input['id'])->update(['hide' => 1]);
            $deletedRows = Task::where('id', $input['id'])->delete();
            return response()->json(['tasks' => $this->getChannelAllPosts($input['channel_id'])]);
        }

        $task = Task::find($input['id']);

        if (!$task) {
            $task = Task::where('id', $input['id'])->withTrashed();
            if ($task) {
                $task->restore();
                Task::where('id', $input['id'])->update(['hide' => 0]);
            }
            return response()->json(['taskse' => $this->getChannelAllPosts($input['channel_id'])]);
        }

        $datesArray = [];
        if (!isset($input['dates']) && empty($input['dates'])) {
            $input['active'] = 0;
        } else {
            $datesArray = explode(',', $input['dates']);
        }

        $input['hash'] = $input['hash'] ?? md5($input['text'] . time());

        unset($input['dates']);


        unset($input['sent']);
        unset($input['deleted_at']);
//        $task->title = $input['title'];
//        $task->text = $input['text'];
//        $task->company_id = $input['company_id'];
//        $task->hash = $input['company_id'] ?? md5($task->text . time());
//        $task->channel_id = $input['channel_id'];
//        $task->hide = $input['hide'];
//        $task->active = $input['active'];
        $task->update($input);
//        print_r($input);


        $deletedRows = Period::where('task_id', $input['id'])->delete();

        if (count($datesArray)) {

            foreach ($datesArray as $date) {

                $periods = new Period();
                $periods->start = new Carbon($date);
                $periods->task_id = $input['id'];
                $periods->save();
            }
        }

        return response()->json(['tasks' => $this->tt->getChannelAllPosts($input['channel_id'])]);
    }

    public function start()
    {
        $response = Telegram::getMe();
        print_r($response);

        $botId = $response->getId();
        $firstName = $response->getFirstName();
        $username = $response->getUsername();

        Telegram::sendMessage(['chat_id' => '@soft_made',
            'text' => 'Что такое Lorem Ipsum?
*Lorem Ipsum* - это текст-"рыба", часто используемый в печати и вэб-дизайне. 

Lorem Ipsum является стандартной "рыбой" для текстов на латинице с начала XVI века. 

В то время некий безымянный печатник создал большую коллекцию размеров и форм шрифтов, используя Lorem Ipsum для распечатки образцов. Lorem Ipsum не только успешно пережил без заметных изменений пять веков, но и перешагнул в электронный дизайн. Его популяризации в новое время послужили публикация листов Letraset с образцами Lorem Ipsum в 60-х годах и, в более недавнее время, программы электронной вёрстки типа Aldus PageMaker, в шаблонах которых используется Lorem Ipsum',

            'parse_mode' => 'markdown',
        ]);

        print_r($botId);

        //config('app.soft_made')

//
//        $command = new Telegram\Bot\Commands\HelpCommand();
//        Telegram::addCommand($command);

//        Telegram::sendMessage(['chat_id'=>'@soft_made','text'=>'Message with buttons']);


//        $keyboard = [['text'=>'something' ],['text'=>'something2' ]];
////            ['7', '8', '9'],
////            ['4', '5', '6'],
////            ['1', '2', '3'],
////            ['0']
////        ];
//
//        $replyMarkup = Telegram::replyKeyboardMarkup([
//            'inline_keyboard' => $keyboard,
//            'resize_keyboard' => true,
//            'one_time_keyboard' => true
//        ]);


//        $inline_button1 = ["text"=>"Google url","url"=>"http://google.com"];
        $inline_button1 = ["text" => "news", "callback_data" => '/menu'];
        $inline_button2 = ["text" => "консьерж", "callback_data" => '/urgent'];
        $inline_button3 = ["text" => "сотруднику", "callback_data" => '/stuff'];
        $inline_keyboard = [[$inline_button1, $inline_button2, $inline_button3]];
        $keyboard = [
            "inline_keyboard" => $inline_keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ];
        $replyMarkup = json_encode($keyboard);
        $response = Telegram::sendMessage([
            'chat_id' => '@soft_made',
            'text' => '*Добрый день!*
_Чем можем помочь?_',
            'parse_mode' => 'markdown',
            'reply_markup' => $replyMarkup //$reply_markup

        ]);


        Telegram::sendMessage(['chat_id' => '@soft_made',

            'text' => 'Message with buttons']);

        die();


        return '';
    }
}
