if exists (select 1 where serverproperty('isfulltextinstalled') = 1)
begin
  exec sp_fulltext_database 'enable'

  if not exists (select name from sys.fulltext_catalogs where name = N'v1_topic_ft_catalog')
    create fulltext catalog v1_topic_ft_catalog

  if not exists (select 1 from sys.fulltext_indexes where object_id = (select object_id from sys.tables where name = N'v1_topic'))
    create fulltext index on v1_topic (name)
    key index v1_topic_pk
    on v1_topic_ft_catalog
    with change_tracking auto
    
  if not exists (select name from sys.fulltext_catalogs where name = N'v1_post_ft_catalog')
    create fulltext catalog v1_post_ft_catalog

  if not exists (select 1 from sys.fulltext_indexes where object_id = (select object_id from sys.tables where name = N'v1_post'))
    create fulltext index on v1_post (searchable_content)
    key index v1_post_pk
    on v1_post_ft_catalog
    with change_tracking auto
end
go
