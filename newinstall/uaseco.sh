#!/bin/sh
cd /home/tm2/uaseco
php uaseco.php TM2 2>>logs/uaseco.log 1>&2 &
echo $!
