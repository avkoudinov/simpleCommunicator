@echo off

rem set the path to mysql.exe
 
set MYSQL_PATH="c:\mysql\bin\mysql.exe"

rem set the root password

set ROOT_PASSWORD="root"
set DB_NAME="nosqlru_forum"

echo *****************************************************
echo * Restoring MySQL database                          *
echo *****************************************************

echo -----------------------------------------------------
echo Step 1: creating database                                  
echo -----------------------------------------------------

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% -e "drop database if exists %DB_NAME%"
if not %errorlevel%==0 goto err

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% -e "create database %DB_NAME%"
if not %errorlevel%==0 goto err

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% -e "alter database %DB_NAME% character set utf8mb4 collate utf8mb4_unicode_ci"
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 2: restoring tables                                   
echo -----------------------------------------------------

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% %DB_NAME% < %DB_NAME%.sql 
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Database successfully restored                     
echo -----------------------------------------------------
pause
@echo on
exit

:err
echo -----------------------------------------------------
echo Error detected. Please read the error message     
echo supplied from script, eliminate the problem and   
echo repeate the action!                               
echo -----------------------------------------------------
pause
@echo on

