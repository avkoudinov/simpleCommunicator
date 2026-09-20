@echo off
rem set the path to sqlplus.exe

set SQLPLUS_PATH="c:\Oracle12c\dbhome1\bin\sqlplus.exe"
rem set the sysdba credentials
set SYS_PASSWORD=D3v3l0p3r
rem set the schema (user) name - in Oracle schema = user
set SCHEMA_NAME=simple_communicator
set SCHEMA_PASSWORD=root
rem set the tablespace name
set TABLESPACE_NAME=%SCHEMA_NAME%_ts

rem CDB - для create_dir (работает на уровне CDB)
set CONNECT_SYS_CDB=sys/%SYS_PASSWORD%@localhost/ORCL
rem PDB - для tablespace, schema, таблиц
set CONNECT_SYS=sys/%SYS_PASSWORD%@localhost/ORCLPDB
set CONNECT_SCHEMA=%SCHEMA_NAME%/%SCHEMA_PASSWORD%@localhost/ORCLPDB

echo *****************************************************
echo * Dropping Oracle schema                            *
echo *****************************************************
"%SQLPLUS_PATH%" -S -L %CONNECT_SYS% as sysdba @run_sql1.sql drop_schema.sql %SCHEMA_NAME%
if not %errorlevel%==0 goto err

"%SQLPLUS_PATH%" -S -L %CONNECT_SYS% as sysdba @run_sql1.sql drop_tablespace.sql %TABLESPACE_NAME%
if not %errorlevel%==0 goto err
echo -----------------------------------------------------
echo Schema successfully dropped
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