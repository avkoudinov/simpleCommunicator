exec dbo.sp_dbcmptlevel @dbname = N'$(DB_NAME)', @new_cmptlevel = 100
go

/*
Due to the restriction that the SQL driver sticks to the encoding
of the Windows and ignores the database and server collation, the 
following should be ensured:

- The encoding of the Windows and that of the SQL Server must be identical.
- The collation of the database should be identical to that of the server.
- The code page in the sqlcmd should be identical to that of the server.
*/

alter database [$(DB_NAME)] set ansi_null_default off 
go

alter database [$(DB_NAME)] set ansi_nulls off 
go

alter database [$(DB_NAME)] set ansi_padding off 
go

alter database [$(DB_NAME)] set ansi_warnings off 
go

alter database [$(DB_NAME)] set arithabort off 
go

alter database [$(DB_NAME)] set auto_close off 
go

alter database [$(DB_NAME)] set auto_create_statistics on 
go

alter database [$(DB_NAME)] set auto_shrink off 
go

alter database [$(DB_NAME)] set auto_update_statistics on 
go

alter database [$(DB_NAME)] set cursor_close_on_commit off 
go

alter database [$(DB_NAME)] set cursor_default  global 
go

alter database [$(DB_NAME)] set concat_null_yields_null off 
go

alter database [$(DB_NAME)] set numeric_roundabort off 
go

alter database [$(DB_NAME)] set quoted_identifier off 
go

alter database [$(DB_NAME)] set recursive_triggers off 
go

alter database [$(DB_NAME)] set  enable_broker 
go

alter database [$(DB_NAME)] set auto_update_statistics_async off 
go

alter database [$(DB_NAME)] set date_correlation_optimization off 
go

alter database [$(DB_NAME)] set allow_snapshot_isolation off 
go

alter database [$(DB_NAME)] set parameterization simple 
go

alter database [$(DB_NAME)] set  read_write 
go

alter database [$(DB_NAME)] set recovery full 
go

alter database [$(DB_NAME)] set  multi_user 
go

alter database [$(DB_NAME)] set page_verify checksum  
go

