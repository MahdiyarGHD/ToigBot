<?php

use TeleBot\TeleBot;
use TeleBot\InlineKeyboard;
use TeleBot\Exceptions\TeleBotException;

// error_reporting(E_ALL); 
// ini_set('ignore_repeated_errors', TRUE); 
// ini_set('display_errors', FALSE); 
// ini_set('log_errors', TRUE); 
// ini_set('error_log', 'errors.log'); 

use MemoryManager,Chat;

require '../vendor/autoload.php';

$SUPPORT_ME    = "";

$START_MESSAGE = "Hi! I'm Toig and I can learn how to speak! You can interact with me using the following commands:
- <code>/toig</code> : Let me generate a message
- <code>/sticker</code> : Let me send a sticker
- <code>/gif</code> : Let me send a gif
- <code>/torrent n</code> : Let me reply automatically to messages sent by others. The parameter n sets how much talkative I am and it must be a number between 0 and 10: with <code>/torrent 10</code> I will answer all messages, while <code>/torrent 0</code> will mute me.
- You can enable or disable my learning ability with the commands <code>/enablelearning</code> and <code>/disablelearning</code>
- <code>/strike</code> : This command will delete half the memory of the chat. Use it wisely!
- <code>/tsetting</code> : Here you can have more info about privacy, special commands and visit my source code 🤖

{$SUPPORT_ME}";

$TSETTING      = "To work correctly, I need to store these information for each chat:
- Chat ID
- Sent words
- Sent stickers
- Sent gifs
I don`t store any information about users, such as user ID, username, profile picture...
Data are automatically deleted after 90 days of inactivity.
Further commands can be used to better control your data:
- <code>/tsetting delete</code> : Remove all data for the current chat. NOTE: this operation is irreversible and you will NOT be asked a confirmation!
- <code>/tsetting flag</code> : Reply to a sticker or a gif with this command to remove it from my memory. This is useful to prevent me from spamming inappropriate content.
- <code>/tsetting unflag</code> : Allow me to learn a sticker or gif that was previously flagged.
- <code>/tsetting filter</code> : With this command, you can specify the type of messages I send.
For more information, visit https://www.github.com/MahdiyarGHD/ToigBot.git or contact my developer @MahdiyarDev.\n\n {$SUPPORT_ME}";


include_once './sections/functions.php';
include_once './sections/handlers.php';
include_once './sections/extensions.php';

try {
    
    $tg = new TeleBot(BOT_TOKEN);
    $tg->setDefaults('sendMessage', ['parse_mode' => 'html']); 

    if(in_array($tg->chat->id,CHAT_ID) || !CHAT_ID) {
        // chat pre-proccessing & bot cycle

        $MemoryManager = new MemoryManager();
        $Chat = new Chat();
                         
        if($tg->message->from->username != BOT_ID) {

            if(isset($tg->message->chat->id)) {   
                $Chat->load(array_merge($MemoryManager->get_chat_from_id($tg->message->chat->id) , ['chat_id' => $tg->message->chat->id]));
            }
            
            if($tg->isText() || $tg->isSticker() || $tg->isAnimation()) 
            {
                if($tg->isCommand() === 0) {
                    learn_cycle($MemoryManager, $Chat, $tg);        
                    reply($tg,$Chat);
                }
            }

            
            // command handlers
            
            $tg->listen('/start', 'start' ,false);
            $tg->listen('/start@' . BOT_ID, 'start' ,false);
            
            
            $tg->listen('/torrent %d', 'set_torrent', false);
            $tg->listen('/torrent@' . BOT_ID . ' %d', 'set_torrent', false);
            
            
            $tg->listen('/toig', 'toig' , false);
            $tg->listen('/toig@' . BOT_ID, 'toig' , false);
            
                    
            $tg->listen('/sticker', 'send_sticker', false);
            $tg->listen('/sticker@' . BOT_ID, 'send_sticker', false);
                    
                    
            $tg->listen('/gif', 'send_gif', false);
            $tg->listen('/gif@' . BOT_ID, 'send_gif', false);
            
                            
            $tg->listen('/enablelearning', 'enable_learning', false);
            $tg->listen('/enablelearning@' . BOT_ID, 'enable_learning', false);
                
                            
            $tg->listen('/disablelearning', 'disable_learning', false);
            $tg->listen('/disablelearning@' . BOT_ID, 'disable_learning', false);
            
            $tg->listen('/strike %s', 'strike', false);
            $tg->listen('/strike@' . BOT_ID . ' %s', 'strike', false);
                            
                            
            $tg->listen('/strike', 'strike_generate', false);
            $tg->listen('/strike@' . BOT_ID, 'strike_generate', false);

        
            
            $tg->listen('/tsetting', 'tsetting_start', false);
            $tg->listen('/tsetting@' . BOT_ID, 'tsetting_start', false);
            
                
            $tg->listen('/tsetting %s', 'tsetting', false);
            $tg->listen('/tsetting@' . BOT_ID . ' %s', 'tsetting', false);        
            
            if($tg->isAdded()) {
                start();
            }
            
                
            if ($tg->message->from->id == OWNER_ID) {
                
                $tg->listen('sync();', 'sync', true);
                
            }
                
            // Automatic sync(); 
            
            $dice = random_float_in_range(0,1) * 10;
            if ($dice < 0.01) {
                $MemoryManager->synchronize();
            }

        }

        // callback queries
        $tg->listen('fl-%s-%d', 'filter_messages', false);        
            
    }
        
    $MemoryManager->sync_data();

    unset($MemoryManager);
    unset($Chat);

} catch (Throwable $th) {
    if (DEBUG_MODE) {
        $command = $tg->hasCallbackQuery() ?
            $tg->update->callback_query->data :
            $tg->update->message->text;

        $text = "‼️ <b>Something went wrong</b>\n\n";
        $text .= "💬 <b>Command:</b> {$command}\n";
        $text .= "🔻 <b>Message:</b> {$th->getMessage()}\n";
        $text .= "📃 <b>File:</b> <code>{$th->getFile()}</code>\n";
        $text .= "⤵️ <b>Line:</b> {$th->getLine()}";
        
        $tg->sendMessage([
            'chat_id' => OWNER_ID,
            'text' => $text,
        ]);
    }
}