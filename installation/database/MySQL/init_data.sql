set charset utf8mb4;

insert into v1_settings (moderator_log, max_att_size, whois_server) values ('moderators', 200, 'http://www.db.ripe.net/whois?form_type=simple&full_query_string=&searchtext={IP}&do_search=Search');

insert into v1_forum (name, creation_date, allow_edit, hide_from_robots)
values ('PRIVATE_MESSAGES', current_timestamp, '1', '1');

insert into v1_forum_statistics (forum_id) values (1);

insert into v1_dual (dummy_val) values ('0');

insert into v1_cache_invalidation (new_dt) values (current_timestamp);
