@echo off
rem set the path to sqlplus.exe
 
set SQLPLUS_PATH="C:\oracle12c\dbhome1\bin\sqlplus.exe"
rem set the sysdba credentials
set SYS_PASSWORD=D3v3l0p3r
rem set the schema (user) name - in Oracle schema = user
set SCHEMA_NAME=simple_communicator
set SCHEMA_PASSWORD=root
rem set the tablespace name
set TABLESPACE_NAME=FORUM_TS
set CONNECT_SYS=sys/%SYS_PASSWORD%@localhost/orcl
set CONNECT_SCHEMA=%SCHEMA_NAME%/%SCHEMA_PASSWORD%@localhost/orcl

echo *****************************************************
echo * Creating Oracle schema                            *
echo *****************************************************
echo -----------------------------------------------------
echo Step 1: creating the schema (user)
echo -----------------------------------------------------

"%SQLPLUS_PATH%" -S -L sys/%SYS_PASSWORD%@localhost/orcl as sysdba @run_sql0.sql create_dir.sql 
if not %errorlevel%==0 goto err

"%SQLPLUS_PATH%" -S -L %CONNECT_SYS% as sysdba @run_sql1.sql drop_schema.sql %SCHEMA_NAME%
if not %errorlevel%==0 goto err

"%SQLPLUS_PATH%" -S -L %CONNECT_SYS% as sysdba @run_sql1.sql create_tablespace.sql %TABLESPACE_NAME%

"%SQLPLUS_PATH%" -S -L %CONNECT_SYS% as sysdba @run_sql3.sql create_schema.sql %SCHEMA_NAME% %SCHEMA_PASSWORD% %TABLESPACE_NAME%
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Schema successfully created
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