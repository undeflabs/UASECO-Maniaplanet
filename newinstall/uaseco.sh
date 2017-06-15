#!/bin/sh

DATE=`date +%Y-%m-%d`

cd /home/tm2/uaseco
php -d allow_url_fopen=on -d safe_mode=0 uaseco.php TM2 2>>logs/$DATE-uaseco-current.log 2>&1 &
echo $!
