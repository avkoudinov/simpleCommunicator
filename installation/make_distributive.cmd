@echo off

set PHP_PATH="C:\web\php\php"
set ZIP_PATH=%ProgramFiles%\7-Zip

rem generate langs and sqls

@echo Generating langs
@echo.

%PHP_PATH% -f "scripts/genlangs.php"

@echo.

@echo Generating sqls
@echo.

%PHP_PATH% -f "scripts/gensql.php"

@echo.

rem delete old zips

@echo Copying files

del *.zip

rem copying application

rmdir /S /Q application
mkdir application
xcopy ..\forum application /S /E /R /Y

rem remove unnecessary stuff

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

rmdir /S /Q application\lang\neru

rem skins handling

rmdir /S /Q application\skins\debug

del application\skins\copy_langs.cmd

del application\skins\default\desktop\test.php
del application\skins\default\mobile\test.php

del application\skins\sqlru\desktop\test.php
del application\skins\sqlru\mobile\test.php

del application\skins\ascetic\desktop\test.php
del application\skins\ascetic\mobile\test.php

"%ZIP_PATH%\7z.exe" a skins.zip .\application\skins\*

rmdir /S /Q application\skins\sqlru
rmdir /S /Q application\skins\ascetic

rmdir /S /Q application\skins\default\desktop\lang\neru
rmdir /S /Q application\skins\default\mobile\lang\neru

rem remove old stuff and apply defaults

rmdir /S /Q application\log

rmdir /S /Q application\tmp

rmdir /S /Q application\user_data

xcopy defaults application /S /E /R /Y

rem database

rmdir /S /Q database
mkdir database
xcopy ..\database database /S /E /R /Y /exclude:xcopy_exclude.cfg

rmdir /S /Q database\MySQL\maintenance
rmdir /S /Q database\MySQL\scripts
rmdir /S /Q database\MySQL\update
del "database\Power Designer Notes.docx"

rem zipping

@echo Zipping

"%ZIP_PATH%\7z.exe" a simple_communicator.zip .\application\*
"%ZIP_PATH%\7z.exe" a database.zip .\database\*
"%ZIP_PATH%\7z.exe" a smileys.zip ..\forum\user_data\smileys\*

rmdir /S /Q application
rmdir /S /Q database

@echo Done

pause


