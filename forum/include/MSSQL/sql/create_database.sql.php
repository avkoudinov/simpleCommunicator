<?php

$sql_cmds[] = '
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N\'$(DB_NAME)\') CREATE DATABASE [$(DB_NAME)] COLLATE Latin1_General_BIN
';

?>