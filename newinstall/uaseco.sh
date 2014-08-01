#!/bin/sh
cd /home/tm2/uaseco
php uaseco.php TM2 </dev/null >logs/uaseco.log 2>&1 &
echo $!
