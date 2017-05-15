The /locales folder
===================
It contains almost all texts which are displayed ingame and its translations.
You can freely modify these files but please read the following:
 - The syntax is XML, like in the main config of UASECO for example
 - Do change between language tags _only_! (e.g. <en>change is allowed</en>)
 - Do NOT change the text IDs! Otherwise your translation doesn't apply! (text IDs are <lowercase>)
 - You can add other languages by adding a new line to a text-ID block: <lang_code>text</lang_code>
 - You can find a list of available language codes here: http://www.w3schools.com/tags/ref_language_codes.asp (may not completely apply)


Using this feature in your plugins
==================================
We tried to make it as easy and universal as possible. Anyway, this might be a bit more complicated than just directly output single-language text.
But please use it. It is not guaranteed we will keep the old method!

How it works:
 - to start a new message output, create a new Message object. It takes 2 arguments:
	the file where the translation is stored (without .xml) and the ID of the message.
	Example: $msg = new Message('plugin.your_creation', 'msg_id');
 - This will still not output anything. To finally output the string, you need to know the login of the player
	(to choose the translation) and pass it to an output method. Those are:
	- $msg->sendChatMessage($login) for sending to the chat
    - $msg->finish($login) for simple returning
    - $msg->finishMultiline($login) like finish() with automatic splitting into array by {br}
 - If you directly want to apply a method on the message object, you can use this short-syntax:
	(new Message('plugin_your_creation', 'msg_id'))->finish($login);
 - There is also a replacement for formatText(), which in some cases can't be used together with the Messageclass
	Therefor we introduced $msg->addPlaceholders($ph1, $ph2, ...). This method can directly be applied on a newly created Message object
	and works in the same way as formatText().
	If you don't know what formatText() does: it replaces {1}, {2} and so on by given placeholders in the same order in the message, as you add them.

Wether you're a plugin developer or just a translator: feel free to share you're work! You can post the changed files in the forum,
commit them on Github, whatever. Thank you :)
