@echo off

rem set the path to mysql.exe
 
set MYSQL_PATH="C:\web\mysql-5.6.10\bin\mysql.exe"
set MYSQL_PATH="C:\web\mysql-5.7.18\bin\mysql.exe"
set MYSQL_PATH="C:\mysql\bin\mysql.exe"

rem set the root password

set ROOT_PASSWORD="root"
set DB_NAME="simple_communicator"

echo *****************************************************
echo * Creating MySQL database                           *
echo *****************************************************

echo -----------------------------------------------------
echo Step 1: creating the database                                  
echo -----------------------------------------------------

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% -e "drop database if exists %DB_NAME%"
if not %errorlevel%==0 goto err

rem create_database.sql is used for preparation of the install php script
rem it cannot be used here because it is not spossible to pass the database name to the script
%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% -e "create database if not exists %DB_NAME%"
if not %errorlevel%==0 goto err

rem init_database.sql is used for preparation of the install php script
rem it cannot be used here because it is not spossible to pass the database name to the script
%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% -e "alter database %DB_NAME% character set utf8mb4 collate utf8mb4_unicode_ci"
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 2: creating the tables                                   
echo -----------------------------------------------------

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% %DB_NAME% < create_tables.sql 
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 3: filling init data                                 
echo -----------------------------------------------------

%MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% %DB_NAME% < init_data.sql 
if not %errorlevel%==0 goto err

rem %MYSQL_PATH% --user=root --password=%ROOT_PASSWORD% %DB_NAME% < test_data.sql
rem if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Database successfully created                     
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

