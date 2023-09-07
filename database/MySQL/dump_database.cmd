@echo off

rem set the path to mysql
 
set MYSQL_PATH=C:\mysql\bin\

rem set the root password

set ROOT_PASSWORD="root"

echo *****************************************************
echo * Dumping the MySQL database                        *
echo *****************************************************

"%MYSQL_PATH%mysqldump" -hlocalhost -uroot -proot --routines --triggers --skip-extended-insert nosqlru_forum > nosqlru_forum.sql
if not %errorlevel%==0 goto err

echo -----------------------------------------------------
echo Database successfully dumped                   
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

