<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Company;
use App\Period;
use App\Services\TT\ITT;
use App\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Commands\Command;
use Telegram;

class TelegramController extends Controller
{

    const MITINO_CHAT_ID = 237268064,
        VIKICHKI_CHAT_ID = 111003162;

    protected $tt;

    public function __construct(ITT $ITT)
    {
        $this->tt = $ITT;
    }

    public function getUserAuthHash(Request $request, string $lsKey)
    {
        if ($lsKey && Cache::tags(['auth', 'start'])->get($lsKey)) {
            return response()->json(['auth_key' => $lsKey]);
        }

        $code = rand(1000, 9999);
        $key = md5(time() . $code);
        $tenMinutes = Carbon::now()->addMinutes(30);
        Cache::tags(['auth', 'start'])->put($key, $code, $tenMinutes);
        return response()->json(['auth_key' => $key]);
    }

    public function identUser(Request $request, string $authKey = null)
    {
        file_put_contents('./bodyUpdate.txt', print_r($request->all(), 1));
        $input = $request->all();

        $authData = json_decode($input['authData']);

        $telegram_id = $authData->id;

        $fn = $authData->first_name ?? '';
        $ln = $authData->last_name ?? '';
        $un = $authData->username ?? '';


        $company = Company::where('telegram_id', $telegram_id)->first();
        if (!$company) {
            $company = new Company();
            $company->auth_key = $input['lsRel'];
            $company->cooked_key = base64_encode($telegram_id . '_' . $fn . "_" . $ln . "_" . $un);
            $company->telegram_first_name = $fn;
            $company->telegram_last_name = $ln;
            $company->telegram_user_name = $un;
            $company->telegram_auth_data = $input['authData'];
            $company->telegram_id = $telegram_id;
            $company->title = '';

        } else {
            $company->auth_key = $input['lsRel'];
            $basedData = base64_encode($telegram_id . '_' . $fn . "_" . $ln . "_" . $un);
            if ($company->cooked_key !== $basedData) {
                $company->cooked_key = $basedData;
            }

            $company->telegram_first_name = $company->telegram_first_name && $company->telegram_first_name !== '' ? $company->telegram_first_name : $fn;
            $company->telegram_last_name = $company->telegram_last_name && $company->telegram_last_name !== '' ? $company->telegram_last_name : $ln;
            $company->telegram_user_name = $company->telegram_user_name && $company->telegram_user_name !== '' ? $company->telegram_user_name : $un;
            $company->telegram_auth_data = $company->telegram_auth_data ?? $input['authData'];
        }

        $company->save();
        return response()->json(['company' => $company]);
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

    public function getChannel(Request $request, $channelId)
    {
        $channel = $this->tt->getChannelById($channelId);
        return response()->json(['channels' => $channel]);
    }

    public function getChannels(Request $request, $companyId)
    {
        $channels = $this->tt->getChannelsByCompanyId($companyId);
        return response()->json(['channels' => $channels]);
    }

    public function increasePostShows(Request $request, $postHash)
    {
        $task = $this->tt->getPostByHash($postHash);
        $task->increment('shows');
        return response()->json(['post' => $task]);
    }

    public function createChannel(Request $request)
    {
        file_put_contents('./bodyCH.txt', print_r($request->all(), 1));


        $input = $request->all();
        $task = new Channel();
        $task->title = $input['title'];
        $task->manager_account = $input['manager_id'];
        $task->telegram_id = $input['telegram_id'];
        $task->company_id = $input['company_id'];
        $task->hide = 0;
        $task->save();


        return $task->toArray();
    }

    public function saveData(Request $request, int $channelId)
    {
//        file_put_contents('./body11.txt', print_r($request->all(), 1));


        $channel = Channel::find($channelId);

        $input = $request->all();
        $task = new Task();
        $task->title = $input['title'];
        $task->text = $input['text'];
        $task->preview = $input['preview'] ?? '';
        $task->need_link = $input['need_link'];
        $task->company_id = $channel->company_id;
        $task->hash = md5($task->text . time());
        $task->channel_id = $channelId;
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


        try {
            $this->sendMeNotification($task, $channel);
        } catch (\Exception $e) {
        }

        return response()->json(['success' => true]);
    }

    private function sendMeNotification(Task $task, Channel $channel = null, $type = 'add')
    {
        $prodHost = config('app.prod_url');
        $linkToPost = "[{$prodHost}/show/{$task->channel_id}/{$task->hash}]";


        $response = Telegram::sendMessage(['chat_id' => static::MITINO_CHAT_ID,
            'text' => "*Добавлен пост!* 
_Kem:_ " . json_encode([$channel->id, $channel->telegram_id, $channel->company->id, $channel->company->telegram_first_name, $channel->company->telegram_last_name, $channel->company->telegram_user_name]) . " 
_Ссылка на пост_:   " . $linkToPost . "",
            'parse_mode' => 'markdown']); //, 'reply_markup' => $btns
        return $response;
    }

    public function updateChannel(Request $request, int $channelId)
    {
        file_put_contents('./bodyUpdateChannel.txt', print_r($request->all(), 1));

        $input = $request->all();

        $task = Channel::find($channelId);
        $task->update($input);

        return $input;//$task->toArray();
    }

    public function feedback(Request $request, int $companyId)
    {
        try {

            $company = Company::find($companyId);
            $input = $request->all();
            $msg = $input['message'];

            Telegram::sendMessage(['chat_id' => static::MITINO_CHAT_ID,
                'text' => "*FEEDBACK!* 
_Kem:_ " . json_encode([$company->id, $company->telegram_first_name, $company->telegram_last_name, $company->telegram_user_name]) . " 
_Ссылка на пост_:   " . $msg . "",
                'parse_mode' => 'markdown']); //, 'reply_markup' => $btns

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }

    }

    /**
     * @param Request $request
     * @param string $materialHash
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateData(Request $request, string $materialHash)
    {

        try {

            file_put_contents('./bodyUpdate.txt', print_r($request->all(), 1));
            $input = $request->all();

            if ($input['hide']) {
                Task::where('hash', $materialHash)->update(['hide' => 1]);
                $deletedRows = Task::where('hash', $materialHash)->delete();
                return response()->json(['tasks' => $this->tt->getChannelAllPosts($input['channel_id'])]);
            }

            $task = Task::where('hash', $materialHash)->first();
            if (!$task) {
                $task = Task::where('hash', $materialHash)->withTrashed();
                if ($task) {
                    $task->restore();
                    Task::where('hash', $materialHash)->update(['hide' => 0]);
                }
                return response()->json(['tasks' => $this->tt->getChannelAllPosts($input['channel_id'])]);
            }

            $datesArray = [];
            if (!isset($input['dates']) || empty($input['dates'])) {
                $input['active'] = 0;
            } else {
                $datesArray = explode(',', $input['dates']);
            }

            $input['hash'] = $input['hash'] ?? md5($input['text'] . time());

            unset($input['dates']);


            unset($input['sent']);
            unset($input['deleted_at']);
            $task->update($input);

            $taskId = $task->id;
            $deletedRows = Period::where('task_id', $taskId)->delete();

            if (count($datesArray)) {

                foreach ($datesArray as $date) {

                    $periods = new Period();
                    $periods->start = new Carbon($date);
                    $periods->task_id = $taskId;
                    $periods->save();
                }
            }

            return response()->json(['tasks' => $this->tt->getChannelAllPosts($input['channel_id'])]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e]);
        }

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


    public function removeWH()
    {
        $response = Telegram::removeWebhook();
        print_r($response);
    }

    public function setWH()
    {
        # $telegram = new Api('BOT TOKEN');
        $response = Telegram::setWebhook(['url' => config('app.url') . '/telegram/' . config('telegram.bot_token') . '/webhook']);
        print_r($response);

    }

    public function getAuthData($hash)
    {
        $path = config('app.auth_data_path');
        $data = file_get_contents($path . '/' . $hash . '.txt');
        return response()->json(['data' => $data]);
    }

    public function webhook(Request $request)
    {
        $updates = Telegram::getWebhookUpdates();
        $path = public_path();
        file_put_contents($path . '/last.txt', print_r($updates, 1));
        try {
            return $this->processWebhook($updates);
        } catch (\Exception $e) {
            $response = Telegram::sendMessage([ //editMessageText
                'chat_id' => static::MITINO_CHAT_ID, //237268064,
                'text' => "Ошибка при обработке webhook'a: " . '<--- *|' . $e->getLine() . '|*
_' . $e->getMessage() . ':_  
Запрос: [' . config('app.url') . '/last.txt]
Ошибка: [' . config('app.url') . '/lastError.txt]
',
                'parse_mode' => 'markdown'
            ]);

            return $response;
        }


    }


    private function processWebhook($updates)
    {
        $m = $updates->getMessage() ?? $updates->getCallbackQuery() ?? $updates->getEditedMessage();
        if (!$m) {
//            if ($inlineResult = $this->processInline($updates)) {
//                return $inlineResult;
//            }
            throw new \Exception('No message | no Edited Message!');
        }

        $callbackData = $m->getData();
        if ($callbackData) {
            return $this->processCallback($updates, $m, $callbackData);
        }

        $text = $m->getText();
        if (substr(trim($text), 0, 6) === '/start') {
            $hash = substr(trim($text), 7);

            $data = [
                'm' => $updates,
                'id' => $m->getFrom()->getId(),
                'first_name' => $m->getFrom()->getFirstName(),
                'last_name' => $m->getFrom()->getLastName(),
                'username' => $m->getFrom()->getUsername(),
                'language_code' => $m->getFrom()->getLanguageCode(),
            ];

            $path = config('app.auth_data_path');
            try {
                file_put_contents($path . '/' . $hash . '.txt', base64_encode(json_encode($data)));
            } catch (\Exception $e) {
                Cache::tags(['auth', 'start'])->flush();
                throw new \Exception('No auth folder with www-data own in root path! ' .
                    $e->getMessage() . '[LINE:' . $e->getLine() . ']');
            }


            $response = Telegram::sendMessage(['chat_id' => $m->getFrom()->getId(), 'text' => "*Вы авторизованы* 
Успешной работы!    
 
BotMe.top: [" . config('prod.url') . "/channel/100/25]
       
 ", 'parse_mode' => 'markdown']); //, 'reply_markup' => $btns
            return 'success';

        }

        $item = $updates->getChannelPost();
        $chat = $item->getChat();

        $response = Telegram::forwardMessage([
            'chat_id' => static::MITINO_CHAT_ID,
            'from_chat_id' => $chat->getId(),
            'message_id' => $item->getMessageId()
        ]);

        return 'ok';
    }

}
