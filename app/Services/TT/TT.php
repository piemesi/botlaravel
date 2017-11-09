<?php
/**
 * Created by PhpStorm.
 * User: malgrat
 * Date: 09.07.17
 * Time: 13:35
 */

namespace App\Services\TT;


use App\Channel;
use App\Task;
use League\HTMLToMarkdown\HtmlConverter;
use Telegram;


class TT
{

    protected $tasks;

    public function __construct()
    {


    }

    private function getChannelsList()
    {
        return Channel::all()->pluck('telegram_id', 'id');


    }

    public function sendPosts(array $postsList = [])
    {

        $sentItems = [];

        if (!$postsList) {
            echo 'There is nothing to send' . PHP_EOL;
            return $sentItems;
        }
        $converter = new HtmlConverter();
//        $converter->getConfig()->setOption('italic_style', '_');
        $converter->getConfig()->setOption('bold_style', '*');

        $channelsList = $this->getChannelsList();
        $prodHost = config('app.prod_url');
        foreach ($postsList as $post) {

            $postPeriodId = $post['periods'][0]['id'] ?? null;
            if (!$postPeriodId) {
                echo '[ERR]No period_id for post! [' . $post['id'] . ']' . PHP_EOL;
                continue;
            }

            $chatId = $channelsList[$post['channel_id']] ?? null;
            if (!$chatId) {
                echo '[ERR]No chat_id for channel!' . PHP_EOL;
                continue;
            }

            $parseMode = 'markdown';//'html';
            if ($post['need_link']) {
                $parseMode = 'markdown';

                if (strlen($post['preview']) < 2) {
                    echo '[WRN]Message is to short to send!' . PHP_EOL;
                    continue;
                }


                $inline_button1 = ["text" => "{$prodHost}/show/{$post['channel_id']}/{$post['hash']}", "callback_data" => '/goToLink'];
//                $inline_button2 = ["text" => "консьерж", "callback_data" => '/urgent'];
//                $inline_button3 = ["text" => "сотруднику", "callback_data" => '/stuff'];
                $inline_keyboard = [[$inline_button1]]; //, $inline_button2, $inline_button3
                $keyboard = [
                    "inline_keyboard" => $inline_keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ];
                $replyMarkup = json_encode($keyboard);
                $response = Telegram::sendMessage([
                    'chat_id' => '@' . $chatId,
                    'text' => '*' . $post['title'] . '' . ($post['minutes_to_read'] ? ' [' . $post['minutes_to_read'] . ' мин.]' : '') . '* 
' . $post['preview'] . "

" . $converter->convert("<a href='{$prodHost}/show/{$post['channel_id']}/{$post['hash']}' target='_blank'>Страница материала</a>"),

//_Чем можем помочь?_',
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => true,
//                    'reply_markup' => $replyMarkup //$reply_markup

                ]);

            } else {

                if (strlen($post['text']) < 2) {
                    echo '[WRN]Message is too short to send!' . PHP_EOL;
                    continue;
                }


                $html = $post['text'];
                $markdown = $converter->convert($html);
                $markdown = $this->fixMarkdown($markdown);

                $inline_button1 = ["text" => "{$prodHost}/show/{$post['channel_id']}/{$post['hash']}", "callback_data" => '/goToLink'];
//                $inline_button2 = ["text" => "консьерж", "callback_data" => '/urgent'];
//                $inline_button3 = ["text" => "сотруднику", "callback_data" => '/stuff'];
                $inline_keyboard = [[$inline_button1]]; //, $inline_button2, $inline_button3
                $keyboard = [
                    "inline_keyboard" => $inline_keyboard,
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true
                ];
                $replyMarkup = json_encode($keyboard);
                $response = Telegram::sendMessage([
                    'chat_id' => '@' . $chatId,
                    'text' => '*' . $post['title'] . '' . ($post['minutes_to_read'] ? ' [' . $post['minutes_to_read'] . ' мин.]' : '') . '* 
                    
' . $markdown
                        //strip_tags($post['text'],"<em><strong><b><pre><code><i><a>").'

                        . "          
                       
                        
"
             //           .$converter->convert("<a href='{$prodHost}/show/{$post['channel_id']}/{$post['hash']}' target='_blank'>Перейти на страницу материала</a>" )
                    ,
//_Чем можем помочь?_',
                    'parse_mode' => $parseMode,
                    'disable_web_page_preview' => true,
//                    'reply_markup' => $replyMarkup

                ]);


            }

            $messageId = $response->getMessageId();
            if ($messageId) {
                $sentItems[$postPeriodId] = $messageId;
            }
        }

//        $response = Telegram::sendPhoto([
//            'chat_id' => '@' . $chatId,
//            'photo' => 'https://gif.cmtt.space/3/club/9b/40/d1/9a9e7232332784.jpg',
//            'caption' => 'Справедливости ради стоит отметить, что ситуация на Нашествии ещё хуже. И в соцсетях фестиваля об этом ни слова. Идея для организаторов: в комплекте билетов в следующем году дарить сапоги.'
//        ]);

        return $sentItems;


    }

    /**
     * Function to fix "\" near digit/s in list in the beginning of row
     *
     * @param $markdown
     * @return mixed
     */
    private function fixMarkdown(string $markdown): string
    {
        $markdown = preg_replace('/<u[^>]*>([^<]*?)<\/u[^>]*>/', '$1', $markdown);
        return preg_replace('/(\\s\d+)\\\/i', "$1", $markdown);
    }


}