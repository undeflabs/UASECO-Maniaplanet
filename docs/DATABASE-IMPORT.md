# Database Import
###### from other Controllers into UASECO



With the scripts in the folder `newinstall/database` you are able to convert a database from an
other Controller (e.g. `XAseco2`) into a UASECO database.

Currently there is only the support for converting a XAseco2 database, but we are working currently
on converter for `ManiaControl` and `PyPlanet`.



## Step 1
You have to setup the connection data in `<settings><dbms>` at the `config/UASECO.xml` file to
enable the converter to connect to your database.



## Step 2
UASECO stores for all local records the Gamemode it was running while driving it. This feature
requires it, to setup here your gamemode which you have used in your previous used Controller
too.

#### XAseco2
You can find your settings in the MatchSettings file from your dedicated Server at
`<playlist><gameinfos><game_mode>`:

| ID	| Mode
|-------|--------------
| 1	| Rounds
| 2	| TimeAttack
| 3	| Team
| 4	| Laps
| 5	| Cup

Replace the below `[GAMEMODE]` with one of the above ID. If your ID is not in the
list above, then your Gamemode is not supported by UASECO!

Example: If your dedicated server was running the Gamemode `TimeAttack` then you
have to replace `[GAMEMODE]` with `2`.



## Step 3

Execute this Script from a `Shell` (LINUX) or `Command Prompt` (Windows), change into that
directory in where you can find uaseco.php.


#### Linux
	php -d max_execution_time=0 -d memory_limit=-1 ./newinstall/database/convert-xaseco2-to-uaseco.php [GAMEMODE]

#### Windows
	php.exe -d max_execution_time=0 -d memory_limit=-1 .\newinstall\database\convert-xaseco2-to-uaseco.php [GAMEMODE]


> Note: Replace `xaseco2` with e.g. `maniacontrol` or `pyplanet`, when you used on of these Controllers.
