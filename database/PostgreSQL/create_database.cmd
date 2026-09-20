@echo off

rem set the path to postgre.exe
 
set PGSQL_PATH="c:\PostgreSQL\bin\psql.exe"

rem set the root password

set PGPASSWORD=root
set DB_NAME="basename_forum"

echo *****************************************************
echo * Creating PostgreSQL database                      *
echo *****************************************************

echo -----------------------------------------------------
echo Step 1: creating the database                                  
echo -----------------------------------------------------

%PGSQL_PATH% -U postgres -c "drop database if exists %db_name%"
if not %errorlevel%==0 goto err

%PGSQL_PATH% -U postgres -v dbname=%DB_NAME% < create_database.sql
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 2: creating the tables                                   
echo -----------------------------------------------------

%PGSQL_PATH% -U postgres -d %DB_NAME% < create_tables.sql
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 3: filling init data                                 
echo -----------------------------------------------------

%PGSQL_PATH% -U postgres -d %DB_NAME% < init_data.sql
if not %errorlevel%==0 goto err

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

