<p align="center">
	<img src="https://i.imgur.com/Wul7sMI.png" height="450" width="350" alt="..." />
</p>

#  🐸 Toig Bot

Toig is a Telegram bot which uses Markov chains to learn how to speak from the messages of users messages. It can answer with text messages, but also stickers, animations.

To work correctly, I need to store these information for each chat:
- Chat ID
- Sent words
- Sent stickers
- Sent gifs

I don`t store any information about users, such as user ID, username, profile picture
and data are automatically deleted after 90 days of inactivity.

 <br>
 These are the available commands:

- toig - Let me generate a message
- sticker - Send a sticker
- gif - Send a gif
- torrent - Automatic replies
- enablelearning - Enable learning
- disablelearning - Disable learning
- strike - Halve the memory of this chat
- tsetting - Privacy stuff and special commands

And there are a number of extra commands that can be issued through `/tsetting`, namely:

- `/tsetting delete`: Erase the entire content of the current chat in one shot. Be careful when using this function: the deletion of your data will happen without asking confirmation and is not reversible, so do it wisely.
- `/tsetting flag`:  Remove a specific sticker or gif from Toig' memory. Let's say some troll publishes a porn gif in your group. Instead of deleting it, reply to it with `/tsetting flag`, this way the gif will be removed from the bot's memory and Toig won't publish it again later. The operation can be undone with `/tsetting unflag`

Since this bot has access to private messages of Telegram group users, it has been designed with a high focus on openness and transparency to guarantee that private data are not used in an improper way. The simple act of making the source code public is already a great step towards this goal, a step which has not been made by other bots similar to Toig. (except [Fioriktos](https://github.com/FiorixF1/fioriktos-bot) 😁)



### 🔑 Installation

Well, you can clone this project and make your custom version of Toig by changing following values in `/resources/config.php`:

```php
// bot information, create your new bot through @BotFather. You can freely choose its name and profile picture, while as a list of commands, copy-paste the one at the top of this page
define('BOT_TOKEN', 'Bot Token'); 
define('BOT_ID', 'Bot ID');

// array of allowed chat ids, leave it empty if you want it to work for all chats
define('CHAT_ID', []);

// bot owner chat id, you can get it from @userinfobot
define('OWNER_ID', 1234567);

// DEBUG_MODE, if this is true, bot will send error logs to OWNER_ID with some deteils
define('DEBUG_MODE', true);

```

Also you can change SUPPORT_ME variable in `src/bot.php` if you want bot to send a supprt link (...) randomly.

## ⁉️ What is assuring me that you won't read my private messages?
Toig is not storing the whole messages, but only the words composing them, so it is not technically possible to recover the original messages. Anyway, I would like to remark that, whenever you use a Telegram bot which has access to all messages you send, you are accepting the risk that the bot owner will use your data unfairly. If you use a bot which is closed source and you do not know where it is deployed, how it works, what data it stores and even who are the people managing it, nothing can guarantee that the unknown developers are not storing all your conversations in some hidden server.

<br>

### ✅ Todo
- [x] Push first version of Toig
- [x] Update readme
- [ ] Add new commands that can be used by users to have better control of Toig

## 🤝 Contribute
This project is released and maintained under the GNU General Public License v3.0. Anyone can contribute to modify, improve, or add new features to this project. There are no obstacles to creating different forks of the project and developing it in a separate branch, whether free or commercial.
