You can freely modify these files but please read the following:
 - The syntax is XML, like in the main config of UASECO for example
 - Do change between language tags _only_! (e.g. <en>change is allowed</en>)
 - Do NOT change the text IDs! Otherwise your translation doesn't apply! (text IDs are <UPPERCASE>)
 - You can add other languages by adding a new line to a text-ID block: <lang_code>text</lang_code>
 - You can find a list of available language codes here: http://www.w3schools.com/tags/ref_language_codes.asp (may not completely apply)

If you're a plugin developer:
 - First of all: even if you don't plan to translate your plugin: use this feature and just add one language to the xml.
	This will save a lot of work for the translators.
 - Make your text translatable by calling the method Locales::_($msg, $login) instead of outputting the text directly
 - $msg must be formatted like this: #LOCALES:translation.file:MESSAGE_ID
 - Please follow upper-/lowercase conventions
 - Please give the translation file exactly the same name as the plugin which uses it and give it the .xml extension
 - There is one file (common.xml) with commonly used translations like "Ok", "close", "open" etc. This can be used by any plugin.
	To use it, your $msg will look like #LOCALES:common:MESSAGE_ID


Wether you're a plugin developer or just a translator: feel free to share you're work! You can post the changed files in the forum,
commit them on Github, whatever. Thank you :)
