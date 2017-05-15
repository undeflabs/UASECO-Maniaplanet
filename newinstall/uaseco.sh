#!/bin/sh
cd /home/tm2/uaseco
php -d allow_url_fopen=on -d safe_mode=0 uaseco.php TM2 2>>logs/uaseco.log 1>&2 &
echo $!
