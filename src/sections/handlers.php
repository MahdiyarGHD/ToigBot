<?php
    
//  Constants
const BEGIN                     = "";
const END                       = 0;
const MESSAGE                   = "Message";
const STICKER                   = "Sticker";
const ANIMATION                 = "Animation";
const CHAT_MAX_DURATION         = 7776000; // Seconds in three months
const PREFIX                    = __DIR__ . "/../../data/chat-data/";   
const CHAT_UPDATE_TIMEOUT       = 666;     // 37 % of 30 minutes
const SUCCESSOR_LIST_MAX_LENGTH = 256;
const WORD_LIST_MAX_LENGTH      = 1624;
const MEDIA_LIST_MAX_LENGTH     = 1024;


// This class handles data storage and synchronization 
class MemoryManager {    
    private $chats; // key: chat_id ---- value: chat data
    private $last_sync;
    private $debug_msg = "Default message";
    
    public function __construct() {
        $this->chats = [];
        $this->last_sync = time();
    }
    
    public function load_data() : void {
        // autoload chats data
        $files = scandir(PREFIX);
        
        foreach($files as $file) 
        { 
            
            if(strpos($file, ".json") !== false)
            {
                $json = json_decode(file_get_contents(PREFIX . $file), true);
                $filename = pathinfo( PREFIX . $file)['filename']; 
                $chat = new Chat();
                $chat->torrent_level = $json['torrent_level'] ?? 5;
                $chat->is_learning = $json['is_learning'] ?? true;
                $chat->model = $json['model'] ?? [];
                $chat->stickers = $json['stickers'] ?? [];
                $chat->animations = $json['animations'] ?? [];
                $chat->flagged_media = $json['flagged_media'] ?? [];
                $chat->last_update = $json['last_update'] ?? time();
                
                $this->chats[$filename] = $chat->get_chat_properties();
            } 
        } 
    }
    
    public function get_chat_from_id(int $chat_id) : array|string {
        $chat = [];
        if(isset($this->chats[$chat_id])) // rare condition
        { 
            // if chat data already loaded
            $chat = $this->chats[$chat_id];   
        }
        elseif(file_exists(PREFIX . "$chat_id.json"))  
        {
            // if not, load from json file 
            $chat = json_decode( file_get_contents(PREFIX . "$chat_id.json") , true );
        }
        else 
        {
            // if database doesn't exists, create new one
            $chat = new Chat();
            $chat = $chat->get_chat_properties();
        }
        
        $chat['last_update'] = time();
        
        $this->chats[$chat_id] = $chat;
        return $chat;
        
    }
    
    public function load_single_chat(int $chat_id, array $data) : void {
        $this->chats[$chat_id] = $data;
    }
    
    public function synchronize() : void {
        $this->load_data();
        $this->delete_old_chats();
        $this->strike_big_chats();
        $this->sync_data();
    }
    
    public function strike_big_chats() : void {
        foreach ($this->chats as $chat_id => $chat_data) {
            // check if file deleted or not
            if($chat_data !== 0) 
            {
                if (count($chat_data['model']) > WORD_LIST_MAX_LENGTH) {
                    $Chat = new Chat();
                    $Chat->load($chat_data);
                    $Chat->halve();
                    $Chat->clean();
                    $this->chats[$chat_id] = $Chat->get_chat_properties();
                }
            }
        }
    }
    
    public function sync_data() : void {
        if (isset($this->chats)) 
        {
            foreach($this->chats as $chat_id => $chat) 
            {
                if($chat != 0)
                {
                    $chat['last_update'] = time();
                    $json = json_encode($chat);
                    file_put_contents(PREFIX . "$chat_id.json", $json);        
                } elseif($chat == 0) 
                {
                    unlink(PREFIX . "$chat_id.json");
                }               
            }
            
        }
    }
    
    function delete_old_chats() : void {
        $now = time();
        foreach($this->chats as $chat_id => $chat )
        {
            if(($now - $chat['last_update']) > CHAT_MAX_DURATION) 
            {
                $this->chats[$chat_id] = 0;
            }
        }
    }
    
    public function delete_chat(int $chat_id) : void {
        $this->chats[$chat_id] = 0;
    }
    
    public function test() {
        return json_encode($this->debug_msg);
    }
    
}

// This class handles the data for a single chat, including chain generation 
class Chat {

    public $chat_id;
    public $torrent_level = 5;
    public $is_learning = true;
    public $model = [ BEGIN => [END] ];
    public $stickers = [];
    public $animations = [];
    public $flagged_media = [];
    public $last_update = [];
    
    private $debug_msg = "default debug message";
    
    public function load(array $chat) : void {
        $this->chat_id = $chat['chat_id'];
        $this->torrent_level = $chat['torrent_level'] ?? 5;
        $this->is_learning = $chat['is_learning'] ?? true;
        $this->model = $chat['model'] ?? [];
        $this->stickers = $chat['stickers'] ?? [];
        $this->animations = $chat['animations'] ?? [];
        $this->flagged_media = $chat['flagged_media'] ?? [];
        $this->last_update = $chat['last_update'] ?? time();
    }

    public function get_chat_properties() : array {
        return [
            'torrent_level' => $this->torrent_level,
            'is_learning' => $this->is_learning,
            'model' => $this->model,
            'stickers' => $this->stickers,
            'animations' => $this->animations,
            'flagged_media' => $this->flagged_media,
            'last_update' => $this->last_update
        ];
    }
    
    public function learn_text(string|null $text) : void {
        if ($this->is_learning) {
            $sentences = explode("\n", $text);
            
            foreach ($sentences as $sentence) {
                $tokens = explode(" ", $sentence);
                $tokens = array_merge([BEGIN], array_filter($tokens, function($x) {
                    return strpos($x, "http") === false;
                }), [END]);
    
                for ($i = 0; $i < count($tokens) - 1; $i++) {
                    $token = $tokens[$i];
                    $successor = $tokens[$i + 1];
    
                    $filtered_token = filter($token);
                    if ($filtered_token !== $token) {
                        $this->model[$token] = [];
                        $token = $filtered_token;
                    }
    
                    if (!isset($this->model[$token])) {
                        $this->model[$token] = [];
                    }
    
                    if (count($this->model[$token]) < SUCCESSOR_LIST_MAX_LENGTH) {
                        $this->model[$token][] = $successor;
                    } else {
                        $guess = rand(0, SUCCESSOR_LIST_MAX_LENGTH - 1);
                        $this->model[$token][$guess] = $successor;
                    }
                    
                }
            }
        }
    }
    
    public function learn_sticker(string|null $file_id, string|null $unique_id) : void {
        if($this->is_learning && $file_id && $unique_id && in_array($unique_id, $this->flagged_media) === false) 
        {
            if(!in_array($file_id,$this->stickers)) 
            {
                if(count($this->stickers) < MEDIA_LIST_MAX_LENGTH) 
                {
                    $this->stickers[] = $file_id;    
                } else {
                    $guess = mt_rand(0, MEDIA_LIST_MAX_LENGTH - 1);
                    $this->stickers[$guess] = $file_id;
                }
            }
        }
    }
    
    public function learn_animation(string|null $file_id, string|null $unique_id) : void {
        if($this->is_learning && $file_id && $unique_id && in_array($unique_id, $this->flagged_media) === false) 
        {
            if(count($this->animations) < MEDIA_LIST_MAX_LENGTH) 
            {
                $this->animations[] = $file_id;    
            } else {
                $guess = mt_rand(0, MEDIA_LIST_MAX_LENGTH - 1);
                $this->animations[$guess] = $file_id;
            }
        }
    }
    
    public function reply() : ?array {
        global $SUPPORT_ME;
        if(random_float_in_range(0,1) * 10 < ($this->torrent_level**2)/10) 
        {
            $dice = random_float_in_range(0,1) * 10;
            if($dice < 0.01) 
            {
                return [MESSAGE,$SUPPORT_ME];
            } 
            elseif($dice < 8.0) 
            {
                return [MESSAGE, $this->talk()];
            } else {
                $type_of_reply = [STICKER,ANIMATION];
                $type_of_reply = $type_of_reply[array_rand($type_of_reply)];
                if($type_of_reply == STICKER) 
                {
                    return [STICKER,$this->choose_sticker()];
                }
                elseif ($type_of_reply == ANIMATION) 
                {
                    return [ANIMATION,$this->choose_animation()];    
                }
            }
        }
        
        return [];
    }
    
    public function talk() : string {
        $answer = [];
        $walker = (mt_rand(0, 1) < 0.5) ? BEGIN : array_rand($this->model);
        $answer[] = $walker;
        
        while (true) {
            $filtered_walker = filter($walker);
            if ($filtered_walker !== $walker) {
                $walker = $filtered_walker;
            }
            
            $successors = $this->model[$walker];
            $num_successors = count($successors);
            $new_token = $successors[rand(0, $num_successors - 1)];
            
            if ($new_token === END && count($answer) === 1 && count(array_unique($this->model[BEGIN])) > 1) {
                do {
                    $new_token = $this->model[BEGIN][rand(0, count($this->model[BEGIN]) - 1)];
                } while ($new_token === END);
            }
            
            if ($new_token === END) {
                break;
            }
            
            $answer[] = $new_token;
            $walker = $new_token;
        }
        
        return implode(" ", $answer);
    }
    
    public function choose_sticker() : string {
        if(count($this->stickers) > 0) return $this->stickers[array_rand($this->stickers)];
        return '';
    }
    
    public function choose_animation() : string {
        if(count($this->animations) > 0) return $this->animations[array_rand($this->animations)];
        return '';
    }
    
    public function halve() : void {
        
        foreach ($this->model as $word => $values) 
        {
            $length = count($this->model[$word]);
            if ($length != 0) {
                $this->model[$word] = array_slice($this->model[$word], $length/2);
            }
            
            $this->model[$word][] = END;
        }
        
        $length = count($this->stickers);
        $this->stickers = array_slice($this->stickers, $length/2);
        $length = count($this->animations);
        $this->animations = array_slice($this->animations, $length/2);
    
    }
    
    
    function clean() : void {
        // delete unreferenced words that doesn't have successors
        
        $words = array_keys($this->model);
        $referenced_words = array(BEGIN);
        foreach ($words as $word) {
            foreach ($this->model[$word] as $successor) {
                $referenced_words[] = $successor;
                $referenced_words[] = filter($successor);
            }
        }
        $to_remove = array_diff($words, $referenced_words);
        unset($words, $referenced_words);
    
        $not_to_remove = array();
        foreach ($to_remove as $word) {
            $successors = array_diff($this->model[$word], array(END));
            if (count($successors) != 0) {
                $not_to_remove[] = $word;
                $not_to_remove[] = filter($word);
            }
        }
        $to_remove = array_diff($to_remove, $not_to_remove);
        unset($not_to_remove);
    
        // delete lonely words
        foreach ($to_remove as $word) {
            unset($this->model[$word]);
        }
        
        unset($to_remove);
    }
    
    public function flag($item, $unique_id) : void { 
        if(!in_array($unique_id,$this->flagged_media)) {
            
            $this->stickers = array_filter($this->stickers, function($sticker) use ($item, $unique_id) {
                return $sticker != $item && !preg_match("/$unique_id$/", $sticker);
            });
            $this->animations = array_filter($this->animations, function($animation) use ($item, $unique_id) {
                return $animation != $item && !preg_match("/$unique_id$/", $animation);
            });
            
            $this->flagged_media[] = $unique_id;
    
        }
    }    
    
    public function unflag($unique_id) : void {

        if(in_array($unique_id,$this->flagged_media)) {
            unset($this->flagged_media[array_search($unique_id,$this->flagged_media)]);
        }
        
        $this->debug_msg = $this->get_chat_properties();
        
    }
    
    
    public function get_chat_usage() : array {
        
        $data = [
            "words" => 1,
            "stickers" => 0,
            "animations" => 0,
            "size" => filesize(PREFIX . "$this->chat_id.json") ?? 0
        ];
        
        if(count($this->model) > 1) {
            $data['words'] = count($this->model);
        } 
        
        if(count($this->stickers) > 0) {
            $data['stickers'] = count($this->stickers);
        } 
        
        if(count($this->animations) > 0) {
            $data['animations'] = count($this->animations);
        }
                
        return $data;
        
    }
    
    
    public function test() {
        return json_encode($this->debug_msg,JSON_PRETTY_PRINT);
    }

    
}