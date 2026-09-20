@echo off

set PHP_PATH="C:\web\php\php"
set ZIP_PATH=%ProgramFiles%\7-Zip

echo *****************************************************
echo * Creating installation distributive                *
echo *****************************************************

echo -----------------------------------------------------
echo Step 1: Generating langs
echo -----------------------------------------------------

%PHP_PATH% -f "scripts/genlangs.php"

echo -----------------------------------------------------
echo Step 2: Generating SQL commands
echo -----------------------------------------------------

%PHP_PATH% -f "scripts/gensql.php"

echo -----------------------------------------------------
echo Step 3: Deleting old zips
echo -----------------------------------------------------

del *.zip

echo -----------------------------------------------------
echo Step 4: Copying application files
echo -----------------------------------------------------

rmdir /S /Q application
mkdir application
xcopy ..\forum application /S /E /R /Y

echo -----------------------------------------------------
echo Step 5: Removing unnecessary stuff
echo -----------------------------------------------------

rmdir /S /Q application\.idea

rmdir /S /Q application\_aux
rmdir /S /Q application\import
rmdir /S /Q application\export
rmdir /S /Q application\migrate
rmdir /S /Q application\testupload
rmdir /S /Q application\image_input
rmdir /S /Q application\import_topic
rmdir /S /Q application\export_topic

del application\z_*.php
del application\z_*.cmd

del application\user_data\config\img_black_list.txt
del application\user_data\config\email_black_list.txt
del application\user_data\config\protected_guests.txt

echo -----------------------------------------------------
echo Step 6: Preparing skins
echo -----------------------------------------------------

rmdir /S /Q application\skins\debug

del application\skins\copy_langs.cmd

del application\skins\default\desktop\test.php
del application\skins\default\mobile\test.php

"%ZIP_PATH%\7z.exe" a skins.zip .\application\skins\*

echo -----------------------------------------------------
echo Step 7: Remove old stuff and apply defaults
echo -----------------------------------------------------

rmdir /S /Q application\log

rmdir /S /Q application\tmp

rmdir /S /Q application\user_data

@echo Creating patch

xcopy defaults application /S /E /R /Y

echo -----------------------------------------------------
echo Step 8: Preparing clear database project
echo -----------------------------------------------------

rmdir /S /Q database
mkdir database
xcopy ..\database database /S /E /R /Y /exclude:xcopy_exclude.cfg

rmdir /S /Q database\MySQL\maintenance
rmdir /S /Q database\MySQL\scripts
rmdir /S /Q database\MySQL\update
rmdir /S /Q database\MySQL\backup

rmdir /S /Q database\PostgreSQL\maintenance
rmdir /S /Q database\PostgreSQL\scripts
rmdir /S /Q database\PostgreSQL\update
rmdir /S /Q database\PostgreSQL\backup

rmdir /S /Q database\MSSQL\maintenance
rmdir /S /Q database\MSSQL\scripts
rmdir /S /Q database\MSSQL\update
rmdir /S /Q database\MSSQL\backup

rmdir /S /Q database\Oracle\maintenance
rmdir /S /Q database\Oracle\scripts
rmdir /S /Q database\Oracle\update
rmdir /S /Q database\Oracle\backup

del "database\Power Designer Notes.docx"

echo -----------------------------------------------------
echo Step 9: Creationg zips
echo -----------------------------------------------------

@echo Zipping

"%ZIP_PATH%\7z.exe" a simple_communicator.zip .\application\*
"%ZIP_PATH%\7z.exe" a database.zip .\database\*
"%ZIP_PATH%\7z.exe" a smileys.zip ..\forum\user_data\smileys\*

rmdir /S /Q application\log
rmdir /S /Q application\tmp
rmdir /S /Q application\user_data
rmdir /S /Q application\jobs

del application\include\admin_config_inc.php
del application\include\config_inc.php
del application\include\maintenance_inc.php
del application\.htaccess

"%ZIP_PATH%\7z.exe" a simple_communicator_update.zip .\application\*

rmdir /S /Q application

echo -----------------------------------------------------
echo Distributive successfully created
echo -----------------------------------------------------

pause


