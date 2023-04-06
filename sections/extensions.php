<?php

use TeleBot\TeleBot;
<<<<<<< HEAD
=======
use TeleBot\InlineKeyboard;
>>>>>>> c83ce6b (Revert missing dependencies)

function learn_cycle($MemoryManager,$Chat, $tg) {
    $file_id = '';
    $unique_id = '';
    $file_type = '';
    
    if ($tg->isSticker()) {
        $file_id = $tg->message->sticker->file_id;
        $unique_id = $tg->message->sticker->file_unique_id;
        $file_type = STICKER;
    } 
    
    if ($tg->isAnimation()) {
        $file_id = $tg->message->animation->file_id;
        $unique_id = $tg->message->animation->file_unique_id;        
        $file_type = ANIMATION;
    }
    
        
    if($tg->message->text) {
        $Chat->learn_text($tg->message->text);
    }
    elseif($file_type == STICKER) {
        $Chat->learn_sticker($file_id,$unique_id);
    } 
    elseif($file_type == ANIMATION) {
        $Chat->learn_animation($file_id,$unique_id);
    }
        
    $MemoryManager->load_single_chat($tg->message->chat->id, $Chat->get_chat_properties());
    $MemoryManager->sync_data();
}

function reply($tg, $Chat) {
    
    $reply = $Chat->reply();
    
    if (count($reply) == 2) {
        
        $type_of_reply = $reply[0];
        $content = $reply[1];
        
<<<<<<< HEAD
        if($content !== "") {
            
            if($type_of_reply == MESSAGE) 
=======
        $filters = $Chat->get_chat_filters();
        
        if($content !== "") {
            
            if($type_of_reply == MESSAGE && !$filters[MESSAGE]) 
>>>>>>> c83ce6b (Revert missing dependencies)
            {
                $tg->sendMessage([
                    'chat_id' => $tg->message->chat->id,
                    'text' => $content,
                ]);
            }
            
<<<<<<< HEAD
            elseif($type_of_reply == STICKER) 
=======
            elseif($type_of_reply == STICKER && !$filters[STICKER]) 
>>>>>>> c83ce6b (Revert missing dependencies)
            {
                $tg->sendSticker([
                    'chat_id' => $tg->message->chat->id,
                    'sticker' => $content
                ]);
            } 
            
<<<<<<< HEAD
            elseif($type_of_reply == ANIMATION)
=======
            elseif($type_of_reply == ANIMATION && !$filters[ANIMATION])
>>>>>>> c83ce6b (Revert missing dependencies)
            {
                $tg->sendAnimation([
                    'chat_id' => $tg->message->chat->id,
                    'animation' => $content
                ]);
            }
            
        }       
    }
}

function start() {
    global $tg,$START_MESSAGE;
    
    $tg->sendMessage([
        'chat_id' => $tg->message->chat->id,
        'text' => $START_MESSAGE,
    ]);
}


function set_torrent($torrent_lvl) {
    
    global $Chat,$MemoryManager,$tg;
    $torrent_lvl = (int)$torrent_lvl ?? '';
    if(isset($torrent_lvl)) {
        if($torrent_lvl <= 10 && $torrent_lvl >= 0 && is_int($torrent_lvl)) { 
            
            $Chat->torrent_level = $torrent_lvl;
        
            $MemoryManager->load_single_chat($tg->message->chat->id, $Chat->get_chat_properties());
            $MemoryManager->sync_data();
            
            $tg->sendMessage([
            'chat_id' => $tg->message->chat->id,
            'text' => 'Done!',
            ]);
            
        } else {
            
            $tg->sendMessage([
                'chat_id' => $tg->message->chat->id,
                'text' => 'AHA // torrent level must be a valid number between 0 and 10.',
            ]);
            
        }
    }
}

function toig() {
    global $Chat,$tg;
    
    $reply = $Chat->talk();
            
    if($reply !== "") {
        $tg->sendMessage([
            'chat_id' => $tg->message->chat->id,
            'text' => $reply,
        ]);
    } else {
        $tg->sendMessage([
            'chat_id' => $tg->message->chat->id,
            'text' => "AHA // chain is empty",
        ]);
    }
}

function send_sticker() {
    global $Chat,$tg;
    
    $sticker = $Chat->choose_sticker();
            
    if($sticker !== "") {
        $tg->sendSticker([
            'chat_id' => $tg->message->chat->id,
            'sticker' => $sticker,
        ]);
    } else {
        $tg->sendMessage([
            'chat_id' => $tg->message->chat->id,
            'text' => "AHA // sticker set is empty",
        ]);
    }
    
}

function send_gif() {
    global $Chat,$tg;
                
    $animation = $Chat->choose_animation();
            
    if($animation !== "") {
        $tg->sendAnimation([
            'chat_id' => $tg->message->chat->id,
            'animation' => $animation,
        ]);
    } else {
        $tg->sendMessage([
            'chat_id' => $tg->message->chat->id,
            'text' => "AHA // animation set is empty",
        ]);
    }
    
}

function enable_learning() {
    global $Chat,$tg,$MemoryManager;
                
    $Chat->is_learning = true;

    $MemoryManager->load_single_chat($tg->message->chat->id, $Chat->get_chat_properties());
    $MemoryManager->sync_data();
    
    $tg->sendMessage([
        'chat_id' => $tg->message->chat->id,
        'text' => "Learning enabled",
    ]);
    
}

function strike($hash) {
    global $Chat,$tg,$MemoryManager;
    
    // check hash
             
    $expected_hash = strtoupper(md5($tg->message->from->id));
            
    if($hash == $expected_hash) {
        $tg->sendMessage([
            'chat_id' => $tg->message->chat->id,
            'text' => "AHA // Let's do some cleaning!",
        ]);
        
        sleep(2);

        $protocol = 'http';
        $host = $_SERVER['HTTP_HOST'];
        $file = str_replace(basename($_SERVER["SCRIPT_FILENAME"]), '', $_SERVER['REQUEST_URI']);
        $file =  "$file/../../resources/thanos.mp4";
        
        $url = $protocol . "://" . $host . $file;
        
        $tg->sendAnimation([
            'chat_id' => $tg->message->chat->id,
            'animation' =>  $url,
        ]);
        
        $Chat->halve();
        $Chat->clean();
        
        $MemoryManager->load_single_chat($tg->message->chat->id, $Chat->get_chat_properties());
        $MemoryManager->sync_data();
        
        sleep(2);
        
        $chat_usage = $Chat->get_chat_usage();
    
        $tg->sendMessage([
            'chat_id' => $tg->message->chat->id,
            'text' => "Now this chat contains {$chat_usage['words']} words, {$chat_usage['stickers']} stickers and {$chat_usage['animations']} gifs for a total size of {$chat_usage['size']} bytes.",
        ]);
           
    }
}

function strike_generate() {
    global $Chat,$tg;
    
    // generate hash
             
    $chat_usage = $Chat->get_chat_usage();
            
    $tg->sendMessage([
        'chat_id' => $tg->message->chat->id,
        'text' => "NAH // Currently this chat has {$chat_usage['words']} words, {$chat_usage['stickers']} stickers and {$chat_usage['animations']} gifs for a total size of {$chat_usage['size']} bytes. Send this message to delete half the memory of this chat:",
    ]);
       
    $hash = strtoupper(md5($tg->message->from->id));
    $tg->sendMessage([
        'chat_id' => $tg->message->chat->id,
        'text' => "/strike $hash",
    ]);
    
    
}

function disable_learning() {
    global $Chat,$tg,$MemoryManager;
                
    $Chat->is_learning = false;

    $MemoryManager->load_single_chat($tg->message->chat->id, $Chat->get_chat_properties());
    $MemoryManager->sync_data();
    
                
    $tg->sendMessage([
        'chat_id' => $tg->message->chat->id,
        'text' => "Learning disabled",
    ]);
}

function tsetting_start() {
    global $tg,$TSETTING;
    
    $tg->sendMessage([
        'chat_id' => $tg->message->chat->id,
        'text' => $TSETTING,
    ]);

}

function tsetting($command) {
    global $Chat,$tg,$MemoryManager;
    
    $command = strtolower($command);
            
    switch($command) {
        case 'delete':
            $MemoryManager->delete_chat($tg->message->chat->id);
            $MemoryManager->sync_data();
            
            $tg->sendMessage([
                'chat_id' => $tg->message->chat->id,
                'text' => 'Done!',
            ]);    
              
            break;           
            
        case 'flag':
            $chat_id = $tg->message->chat->id;
            $user_id = $tg->message->from->id;
            
            $chat_member = $tg->getChatMember([
                'chat_id' => $chat_id,
                'user_id' => $user_id
            ]);
            
            if(in_array($chat_member->status, ["creator", "administrator"])) {
                if(isset($tg->message->reply_to_message))
                {
                    if(isset($tg->message->reply_to_message->sticker))
                    {
                        
                        $item = $tg->message->reply_to_message->sticker->file_id;
                        $unique_id = $tg->message->reply_to_message->sticker->file_unique_id;
                        
                    } elseif(isset($tg->message->reply_to_message->animation)) 
                    {
                        
                        $item = $tg->message->reply_to_message->animation->file_id;
                        $unique_id = $tg->message->reply_to_message->animation->file_unique_id;
<<<<<<< HEAD
                        
=======
>>>>>>> c83ce6b (Revert missing dependencies)
                    }
                    
                    $Chat->flag($item,$unique_id);
                    
                    $MemoryManager->load_single_chat($tg->message->chat->id, $Chat->get_chat_properties());
                    $MemoryManager->sync_data();
                    
                    $tg->sendMessage([
                        'chat_id' => $tg->message->chat->id,
                        'text' => 'Done!',
                    ]);         
                    
                } else {
                    $tg->sendMessage([
                        'chat_id' => $tg->message->chat->id,
                        'text' => 'NAH // Reply to a sticker or a gif with <code>/tsetting flag</code>.',
                    ]);         
                }
            } else {
                                    
            $tg->sendMessage([
                'chat_id' => $tg->message->chat->id,
                'text' => 'NAH // Command available only for admins.',
            ]);   
            
            }
            
            break;     
            
            
        case 'unflag':
            $chat_id = $tg->message->chat->id;
            $user_id = $tg->message->from->id;
            
            $chat_member = $tg->getChatMember([
                'chat_id' => $chat_id,
                'user_id' => $user_id
            ]);
            
            if(in_array($chat_member->status, ["creator", "administrator"])) {
                if(isset($tg->message->reply_to_message))
                {
                    if(isset($tg->message->reply_to_message->sticker))
                    {
                        
                        $item = $tg->message->reply_to_message->sticker->file_id;
                        $unique_id = $tg->message->reply_to_message->sticker->file_unique_id;
                        
                    } elseif(isset($tg->message->reply_to_message->animation)) 
                    {
                        
                        $item = $tg->message->reply_to_message->animation->file_id;
                        $unique_id = $tg->message->reply_to_message->animation->file_unique_id;
                        
                    }
                    
                    $Chat->unflag($unique_id);
                    
                    $MemoryManager->load_single_chat($tg->message->chat->id, $Chat->get_chat_properties());
                    $MemoryManager->sync_data();
                    
                    $tg->sendMessage([
                        'chat_id' => $tg->message->chat->id,
                        'text' => 'Done!',
                    ]);         
                    
                } else {
                    $tg->sendMessage([
                        'chat_id' => $tg->message->chat->id,
                        'text' => 'NAH // Reply to a sticker or a gif with <code>/tsetting flag</code>.',
                    ]);         
                }
            } else {
                                    
            $tg->sendMessage([
                'chat_id' => $tg->message->chat->id,
                'text' => 'NAH // Command available only for admins.',
            ]);   
            
            }
            
            break;
<<<<<<< HEAD
=======
            
            
        case 'filter':
            
            $filters = $Chat->get_chat_filters();
            $from_id = $tg->message->from->id;
            $icons = [
                MESSAGE => $filters[MESSAGE] === false ? '✅ ' : '❌ ',
                STICKER => $filters[STICKER] === false ? '✅ ' : '❌ ',
                ANIMATION => $filters[ANIMATION] === false ? '✅ ' : '❌ '
            ];
            
            $keyboard = (new InlineKeyboard())
            ->addCallbackButton($icons[MESSAGE] . MESSAGE, "fl-Message-$from_id")
            ->addCallbackButton($icons[STICKER] . STICKER, "fl-Sticker-$from_id")
            ->addCallbackButton($icons[ANIMATION] . ANIMATION, "fl-Animation-$from_id")
            ->chunk(1)
            ->rightToLeft()
            ->get();
            
            $tg->sendMessage([
                'chat_id' => $tg->message->chat->id,
                'text' => "AHA // Toggle message types you want to filter",
                'reply_markup' => $keyboard
            ]);   
            
            break;
>>>>>>> c83ce6b (Revert missing dependencies)
    
    }
    
}

function sync() {
    global $MemoryManager,$tg;
    
    $MemoryManager->synchronize();
                
    $tg->sendMessage([
        'chat_id' => $tg->message->chat->id,
        'text' => '🤖 synchronized successfully!',
    ]);
    
}

<<<<<<< HEAD
=======
function filter_messages($type, $from_id) {
    global $Chat,$tg,$MemoryManager;
    
    if($tg->callback_query->from->id == $from_id) 
    {  
        $Chat->load(array_merge($MemoryManager->get_chat_from_id($tg->message->chat->id) , ['chat_id' => $tg->message->chat->id]));
        $filters = $Chat->get_chat_filters();
        
        if ($type === MESSAGE)   $filters[MESSAGE] = !$filters[MESSAGE];
        if ($type === STICKER)   $filters[STICKER] = !$filters[STICKER];
        if ($type === ANIMATION) $filters[ANIMATION] = !$filters[ANIMATION];
        
        $Chat->update_filters($filters);
        
        $MemoryManager->load_single_chat($tg->message->chat->id, $Chat->get_chat_properties());
        $MemoryManager->sync_data();
            
        $icons = [
            MESSAGE => $filters[MESSAGE] === false ? '✅ ' : '❌ ',
            STICKER => $filters[STICKER] === false ? '✅ ' : '❌ ',
            ANIMATION => $filters[ANIMATION] === false ? '✅ ' : '❌ '
        ];
        
        $keyboard = (new InlineKeyboard())
        ->addCallbackButton($icons[MESSAGE] . MESSAGE, "fl-Message-$from_id")
        ->addCallbackButton($icons[STICKER] . STICKER, "fl-Sticker-$from_id")
        ->addCallbackButton($icons[ANIMATION] . ANIMATION, "fl-Animation-$from_id")
        ->chunk(1)
        ->rightToLeft()
        ->get();
        
        $tg->editMessageReplyMarkup([
            'message_id' => $tg->message->message_id,
            'chat_id' => $tg->message->chat->id,
            'reply_markup' => $keyboard
        ]);   
        
    } else {
        $tg->answerCallbackQuery([
            'callback_query_id' => $tg->callback_query->id,
            'text' => 'You don`t have permission to do this'
        ]);
    }
}

>>>>>>> c83ce6b (Revert missing dependencies)
TeleBot::extend('isSticker', function () {
    if($this->message) {
        return property_exists($this->message, 'sticker');
    }
});

TeleBot::extend('isAnimation', function () {
    if($this->message) {
        return property_exists($this->message, 'animation');
    }
});

TeleBot::extend('isText', function () {
    if($this->message) {
        return property_exists($this->message, 'text');
    }
});

TeleBot::extend('isAdded', function () {
    if(isset($this->message->new_chat_member)) {
        return $this->message->new_chat_member->username == BOT_ID;        
    } 
    
});

TeleBot::extend('isCommand', function () {
    $commandPattern = "/^\/[\w@]+(\s\w+)?$/";
    return preg_match($commandPattern, $this->message->text);
    
});