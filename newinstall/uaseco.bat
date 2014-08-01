@echo off

rem ****** Set here your php path *******

set INSTPHP=C:\Programme\Apache2\Php5

rem *************************************

PATH=%PATH%;%INSTPHP%;%INSTPHP%\extensions
"%INSTPHP%\php.exe" uaseco.php

pause
