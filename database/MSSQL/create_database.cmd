@echo off

rem set the path to sqlcmd.exe

set OSQL_PATH="c:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\"

rem set the instance name

set HOST="(local)"

set DB_NAME=forum

rem Due to the restriction that the SQL driver sticks to the encoding
rem of the Windows and ignores the database and server collation, the 
rem following should be ensured:

rem - The encoding of the Windows and that of the SQL Server must be identical.
rem - The collation of the database should be identical to that of the server.
rem - The code page in the sqlcmd should be identical to that of the server.

set CODEPAGE="1251"

echo *****************************************************
echo * Creating MSSQL database                           *
echo *****************************************************

echo -----------------------------------------------------
echo Step 1: creating database                                  
echo -----------------------------------------------------

%OSQL_PATH%sqlcmd -S %HOST% -E -b -Q "if exists (select name from sys.databases where name = N'%DB_NAME%') drop database [%DB_NAME%]"
if not %errorlevel%==0 goto err

%OSQL_PATH%sqlcmd -S %HOST% -E -b -i create_database.sql
if not %errorlevel%==0 goto err

%OSQL_PATH%sqlcmd -S %HOST% -E -b -d %DB_NAME% -i init_database.sql
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 2: creating tables                                   
echo -----------------------------------------------------

%OSQL_PATH%sqlcmd -S %HOST% -E -b -d %DB_NAME% -i create_tables.sql
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 3: filling init data                                 
echo -----------------------------------------------------

%OSQL_PATH%sqlcmd -S %HOST% -E -b -d %DB_NAME% -f %CODEPAGE% -i init_data.sql
if not %errorlevel%==0 goto err

rem %OSQL_PATH%sqlcmd -S %HOST% -E -b -d %DB_NAME% -f %CODEPAGE% -i test_data.sql
rem if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Step 4: creating full-text catalog                        
echo -----------------------------------------------------

%OSQL_PATH%sqlcmd -S %HOST% -E -b -d %DB_NAME% -f %CODEPAGE% -i final_actions.sql
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



