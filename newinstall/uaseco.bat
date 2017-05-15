@echo off

rem ****** Set here your php path *******

set INSTPHP=C:\Programme\Apache2\Php5

rem *************************************

PATH=%PATH%;%INSTPHP%;%INSTPHP%\extensions

chcp 65001
"%INSTPHP%\php.exe" -d allow_url_fopen=on -d safe_mode=0 uaseco.php 2>nul

pause
