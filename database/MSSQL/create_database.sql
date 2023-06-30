IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'$(DB_NAME)') CREATE DATABASE [$(DB_NAME)]
go
