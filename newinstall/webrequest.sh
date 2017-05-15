#!/bin/sh
cd /home/tm2/uaseco
php -d allow_url_fopen=on -d safe_mode=0 webrequest.php 1>&2 &
echo $!
