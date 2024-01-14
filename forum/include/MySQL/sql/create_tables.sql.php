<?php

$sql_cmds[] = '
SET default_storage_engine=INNODB
';

$sql_cmds[] = '
create table v1_attachment
(
   id                   int not null auto_increment,
   post_id              int not null,
   nr                   int not null,
   name                 varchar(700),
   origin_name          varchar(700),
   type                 varchar(255),
   deleted              tinyint not null default 0,
   user_id              int,
   favourite            tinyint not null default 0,
   last_post_id         int not null,
   primary key (id)
)
';

$sql_cmds[] = '
create unique index v1_attachment_unq on v1_attachment
(
   post_id,
   nr
)
';

$sql_cmds[] = '
create index v1_attachment_name_idx on v1_attachment
(
   name
)
';

$sql_cmds[] = '
create index v1_attachment_type_idx on v1_attachment
(
   type
)
';

$sql_cmds[] = '
create index v1_attachment_user_idx on v1_attachment
(
   user_id
)
';

$sql_cmds[] = '
create index v1_attachment_favourite_idx on v1_attachment
(
   favourite
)
';

$sql_cmds[] = '
create index v1_attachment_last_post_idx on v1_attachment
(
   last_post_id
)
';

$sql_cmds[] = '
create table v1_auto_saved
(
   topic_id             int not null,
   read_marker          varchar(255) not null,
   dt                   datetime not null,
   text_content         mediumtext
)
';

$sql_cmds[] = '
create index v1_auto_saved_tid_idx on v1_auto_saved
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_auto_saved_dt_idx on v1_auto_saved
(
   dt
)
';

$sql_cmds[] = '
create index v1_auto_saved_rm_idx on v1_auto_saved
(
   read_marker
)
';

$sql_cmds[] = '
create table v1_banned_ips
(
   ip                   varchar(250) not null,
   banned_until         datetime not null,
   hits                 int not null,
   atype                varchar(255)
)
';

$sql_cmds[] = '
create index v1_banned_ips_idx on v1_banned_ips
(
   ip
)
';

$sql_cmds[] = '
create table v1_browser_statistics_cache
(
   tm                   datetime,
   tp                   varchar(100),
   name                 varchar(250),
   cnt                  int
)
';

$sql_cmds[] = '
create table v1_cache_invalidation
(
   new_dt               datetime
)
';

$sql_cmds[] = '
create table v1_daily_statistics
(
   user_id              int,
   forum_id             int not null,
   dt                   date not null,
   hits_count           int not null default 0,
   post_count           int not null default 0,
   time_online          bigint not null default 0
)
';

$sql_cmds[] = '
create unique index v1_daily_statistics_unq on v1_daily_statistics
(
   user_id,
   forum_id,
   dt
)
';

$sql_cmds[] = '
create index v1_daily_statistics_dt_idx on v1_daily_statistics
(
   dt
)
';

$sql_cmds[] = '
create index v1_daily_statistics_user_idx on v1_daily_statistics
(
   user_id
)
';

$sql_cmds[] = '
create index v1_daily_statistics_forum_idx on v1_daily_statistics
(
   forum_id
)
';

$sql_cmds[] = '
create table v1_dual
(
   dummy_val            tinyint not null default 0
)
';

$sql_cmds[] = '
create table v1_events
(
   id                   int not null auto_increment,
   event_time           datetime not null,
   event_code           varchar(255) not null,
   params               text,
   author_name          varchar(255),
   author_id            int,
   post_id              int,
   is_new               tinyint not null default 1,
   todo                 tinyint not null default 0,
   user_id              int not null,
   redundant            tinyint not null default 0,
   topic_name           varchar(1000),
   topic_id             int,
   forum_name           varchar(255),
   forum_id             int,
   comment              text,
   source_topic_name    varchar(1000),
   source_topic_id      int,
   primary key (id)
)
';

$sql_cmds[] = '
create index v1_events_evtime_idx on v1_events
(
   event_time
)
';

$sql_cmds[] = '
create index v1_events_user_id_idx on v1_events
(
   user_id
)
';

$sql_cmds[] = '
create index v1_events_post_id_idx on v1_events
(
   post_id
)
';

$sql_cmds[] = '
create index v1_events_redundant_idx on v1_events
(
   redundant
)
';

$sql_cmds[] = '
create index v1_events_todo_idx on v1_events
(
   todo
)
';

$sql_cmds[] = '
create index v1_events_code_idx on v1_events
(
   event_code
)
';

$sql_cmds[] = '
create index v1_events_author_name_idx on v1_events
(
   author_name
)
';

$sql_cmds[] = '
create index v1_events_topic_id_idx on v1_events
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_events_src_topic_id_idx on v1_events
(
   source_topic_id
)
';

$sql_cmds[] = '
create index v1_events_forum_id_idx on v1_events
(
   forum_id
)
';

$sql_cmds[] = '
create table v1_favourite_posts
(
   post_id              int not null,
   user_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_favourite_posts_unq on v1_favourite_posts
(
   post_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_favourite_posts_post_id_idx on v1_favourite_posts
(
   post_id
)
';

$sql_cmds[] = '
create index v1_favourite_posts_user_id_idx on v1_favourite_posts
(
   user_id
)
';

$sql_cmds[] = '
create table v1_favourite_topics
(
   topic_id             int not null,
   user_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_favourite_topics_unq on v1_favourite_topics
(
   topic_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_favourite_topics_topic_id_idx on v1_favourite_topics
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_favourite_topics_user_id_idx on v1_favourite_topics
(
   user_id
)
';

$sql_cmds[] = '
create table v1_forum
(
   id                   int not null auto_increment,
   name                 varchar(255) not null,
   protected_by_password tinyint not null default 0,
   password             varchar(255),
   creation_date        datetime not null,
   restricted_access    tinyint not null default 0,
   allow_edit           tinyint not null default 0,
   no_guests            tinyint not null default 0,
   user_posting_as_guest tinyint not null default 0,
   restricted_guest_mode tinyint not null default 0,
   hide_from_robots     tinyint not null default 0,
   description          varchar(500),
   deleted              tinyint not null default 0,
   closed               tinyint not null default 0,
   sort_order           int not null default 0,
   access_duration      int,
   access_message_count int,
   stringent_rules      tinyint not null default 0,
   disable_ignore       tinyint not null default 0,
   primary key (id)
)
';

$sql_cmds[] = '
create unique index v1_forum_name_unq on v1_forum
(
   name
)
';

$sql_cmds[] = '
create index v1_forum_is_deleted_idx on v1_forum
(
   deleted
)
';

$sql_cmds[] = '
create index v1_forum_protected_by_pwd_idx on v1_forum
(
   protected_by_password
)
';

$sql_cmds[] = '
create index v1_forum_access_idx on v1_forum
(
   restricted_access
)
';

$sql_cmds[] = '
create table v1_forum_blocked
(
   block_expires        datetime,
   user_id              int not null,
   forum_id             int not null
)
';

$sql_cmds[] = '
create unique index v1_forum_blocked_unq on v1_forum_blocked
(
   user_id,
   forum_id
)
';

$sql_cmds[] = '
create index v1_forum_blocked_user_id_idx on v1_forum_blocked
(
   user_id
)
';

$sql_cmds[] = '
create index v1_forum_blocked_forum_id_idx on v1_forum_blocked
(
   forum_id
)
';

$sql_cmds[] = '
create table v1_forum_hits
(
   forum_id             INT,
   topic_id             int,
   dt                   datetime not null,
   user_id              int,
   hits_count           int not null default 0,
   duration             int not null default 0,
   guest_name           varchar(250),
   user_agent           varchar(700),
   uri                  varchar(2000),
   ip                   varchar(250),
   browser              varchar(250),
   os                   varchar(250),
   bot                  varchar(250),
   processed            tinyint not null default 0,
   read_marker          varchar(255)
)
';

$sql_cmds[] = '
create index v1_forum_hits_forum_idx on v1_forum_hits
(
   forum_id
)
';

$sql_cmds[] = '
create index v1_forum_hits_dt_idx on v1_forum_hits
(
   dt
)
';

$sql_cmds[] = '
create index v1_forum_hits_user_idx on v1_forum_hits
(
   user_id
)
';

$sql_cmds[] = '
create index v1_forum_hits_topic_idx on v1_forum_hits
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_forum_hits_ua_idx on v1_forum_hits
(
   user_agent
)
';

$sql_cmds[] = '
create index v1_forum_hits_processed_idx on v1_forum_hits
(
   processed
)
';

$sql_cmds[] = '
create index v1_forum_hits_rm_idx on v1_forum_hits
(
   read_marker
)
';

$sql_cmds[] = '
create index v1_forum_hits_guest_name_idx on v1_forum_hits
(
   guest_name
)
';

$sql_cmds[] = '
create index v1_forum_hits_ip_idx on v1_forum_hits
(
   ip
)
';

$sql_cmds[] = '
create index v1_forum_hits_bot_idx on v1_forum_hits
(
   bot
)
';

$sql_cmds[] = '
create table v1_forum_member
(
   user_id              int not null,
   forum_id             int not null
)
';

$sql_cmds[] = '
create unique index v1_forum_member_unq on v1_forum_member
(
   user_id,
   forum_id
)
';

$sql_cmds[] = '
create index v1_forum_member_user_id_idx on v1_forum_member
(
   user_id
)
';

$sql_cmds[] = '
create index v1_forum_member_forum_id_idx on v1_forum_member
(
   forum_id
)
';

$sql_cmds[] = '
create table v1_forum_moderator
(
   user_id              int not null,
   forum_id             int not null
)
';

$sql_cmds[] = '
create unique index v1_forum_moderator_unq on v1_forum_moderator
(
   user_id,
   forum_id
)
';

$sql_cmds[] = '
create index v1_forum_moderator_user_id_idx on v1_forum_moderator
(
   user_id
)
';

$sql_cmds[] = '
create index v1_forum_moderator_forum_id_idx on v1_forum_moderator
(
   forum_id
)
';

$sql_cmds[] = '
create table v1_forum_read_markers
(
   read_marker          varchar(255),
   forum_id             int,
   first_read_date      datetime,
   last_activity        datetime,
   first_activity       datetime,
   ip                   varchar(250)
)
';

$sql_cmds[] = '
create unique index v1_forum_read_markers_fmrm_idx on v1_forum_read_markers
(
   read_marker,
   forum_id
)
';

$sql_cmds[] = '
create index v1_forum_read_markers_lastact_idx on v1_forum_read_markers
(
   last_activity
)
';

$sql_cmds[] = '
create index v1_forum_read_markers_rm_idx on v1_forum_read_markers
(
   read_marker
)
';

$sql_cmds[] = '
create index v1_forum_read_markers_frdt_idx on v1_forum_read_markers
(
   first_read_date
)
';

$sql_cmds[] = '
create table v1_forum_statistics
(
   forum_id             int not null,
   topic_count          int not null default 0,
   topic_count_total    int not null default 0,
   last_message_date    datetime,
   last_message_id      int
)
';

$sql_cmds[] = '
create unique index v1_forum_statistics_unq on v1_forum_statistics
(
   forum_id
)
';

$sql_cmds[] = '
create table v1_found_post_cache
(
   post_id              int not null,
   topic_id             int not null,
   session_id           varchar(255) not null,
   dt                   datetime not null,
   search_hash          varchar(255)
)
';

$sql_cmds[] = '
create index v1_found_post_cache_session_idx on v1_found_post_cache
(
   session_id
)
';

$sql_cmds[] = '
create index v1_found_post_cache_post_idx on v1_found_post_cache
(
   post_id
)
';

$sql_cmds[] = '
create index v1_found_post_cache_topic_idx on v1_found_post_cache
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_found_post_cache_hash_idx on v1_found_post_cache
(
   search_hash
)
';

$sql_cmds[] = '
create index v1_found_post_cache_dt_idx on v1_found_post_cache
(
   dt
)
';

$sql_cmds[] = '
create table v1_found_topic_cache
(
   topic_id             int not null,
   session_id           varchar(255) not null,
   dt                   datetime not null,
   search_hash          varchar(255)
)
';

$sql_cmds[] = '
create index v1_found_topic_cache_session_idx on v1_found_topic_cache
(
   session_id
)
';

$sql_cmds[] = '
create index v1_found_topic_cache_topic_idx on v1_found_topic_cache
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_found_topic_cache_hash_idx on v1_found_topic_cache
(
   search_hash
)
';

$sql_cmds[] = '
create index v1_found_topic_cache_dt_idx on v1_found_topic_cache
(
   dt
)
';

$sql_cmds[] = '
create table v1_hide_guest_avatars
(
   user_id              int not null,
   avatar               varchar(250) not null
)
';

$sql_cmds[] = '
create unique index v1_hide_guest_avatars_unq on v1_hide_guest_avatars
(
   user_id,
   avatar
)
';

$sql_cmds[] = '
create table v1_hide_profile
(
   user_id              int not null,
   hidden_user_id       int not null
)
';

$sql_cmds[] = '
create unique index v1_hide_profile_unq on v1_hide_profile
(
   user_id,
   hidden_user_id
)
';

$sql_cmds[] = '
create table v1_ignore_history
(
   user_id              int not null,
   ignored_user_id      int not null,
   exclude_date         datetime not null
)
';

$sql_cmds[] = '
create unique index v1_ignore_history_unq on v1_ignore_history
(
   user_id,
   ignored_user_id
)
';

$sql_cmds[] = '
create table v1_ignored_forums
(
   forum_id             int not null,
   user_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_ignored_forums_unq on v1_ignored_forums
(
   forum_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_ignored_forums_user_id_idx on v1_ignored_forums
(
   user_id
)
';

$sql_cmds[] = '
create index v1_ignored_forums_forum_id_idx on v1_ignored_forums
(
   forum_id
)
';

$sql_cmds[] = '
create table v1_ignored_guests
(
   user_id              int not null,
   guest_name           varchar(255) not null,
   whitelist            tinyint not null default 0
)
';

$sql_cmds[] = '
create unique index v1_ignored_guests_unq on v1_ignored_guests
(
   guest_name,
   user_id,
   whitelist
)
';

$sql_cmds[] = '
create index v1_ignored_guests_name_idx on v1_ignored_guests
(
   guest_name
)
';

$sql_cmds[] = '
create index v1_ignored_guests_user_id_idx on v1_ignored_guests
(
   user_id
)
';

$sql_cmds[] = '
create table v1_ignored_topics
(
   topic_id             int not null,
   user_id              int not null,
   auto_ignored         tinyint not null default 0
)
';

$sql_cmds[] = '
create unique index v1_ignored_topic_unq on v1_ignored_topics
(
   topic_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_ignored_topic_topic_id_idx on v1_ignored_topics
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_ignored_topic_user_id_idx on v1_ignored_topics
(
   user_id
)
';

$sql_cmds[] = '
create table v1_ignored_topics_archive
(
   topic_id             int not null,
   user_id              int not null,
   auto_ignored         tinyint not null default 0
)
';

$sql_cmds[] = '
create unique index v1_ignored_topics_archive_unq on v1_ignored_topics_archive
(
   topic_id,
   user_id
)
';

$sql_cmds[] = '
create table v1_ignored_users
(
   user_id              int not null,
   ignored_user_id      int not null,
   comment              text
)
';

$sql_cmds[] = '
create unique index v1_ignored_users_unq on v1_ignored_users
(
   user_id,
   ignored_user_id
)
';

$sql_cmds[] = '
create index v1_ignored_users_user_id_idx on v1_ignored_users
(
   user_id
)
';

$sql_cmds[] = '
create index v1_ignored_users_ignored_user_id_idx on v1_ignored_users
(
   ignored_user_id
)
';

$sql_cmds[] = '
create table v1_ip_blocked
(
   ip                   varchar(250) not null,
   block_expires        datetime,
   tp                   varchar(10) not null default \'IP\',
   block_reason         text
)
';

$sql_cmds[] = '
create unique index v1_ip_blocked_unq on v1_ip_blocked
(
   ip
)
';

$sql_cmds[] = '
create table v1_ip_white_list
(
   ip                   varchar(250) not null
)
';

$sql_cmds[] = '
create table v1_load_statistics
(
   dt                   datetime not null,
   url                  varchar(255) not null,
   user_name            varchar(255),
   user_id              int,
   ip                   varchar(255),
   exec_time            int not null,
   forum_rm_count       int not null default 0,
   topic_rm_count       int not null default 0
)
';

$sql_cmds[] = '
create index v1_load_statistics_dt_idx on v1_load_statistics
(
   dt
)
';

$sql_cmds[] = '
create table v1_moderator_log
(
   id                   int not null auto_increment,
   event_time           datetime not null,
   moderator_name       varchar(255),
   moderator_id         int,
   action               varchar(255),
   action_expires       datetime,
   author_name          varchar(255),
   author_id            int,
   ip                   varchar(250),
   post_id              int,
   topic_name           varchar(1000),
   topic_id             int,
   forum_name           varchar(255),
   forum_id             int,
   comment              text,
   redundant            tinyint not null default 0,
   source_topic_name    varchar(1000),
   source_topic_id      int,
   primary key (id)
)
';

$sql_cmds[] = '
create index v1_moderator_log_mod_name_idx on v1_moderator_log
(
   moderator_name
)
';

$sql_cmds[] = '
create index v1_moderator_log_action_idx on v1_moderator_log
(
   action
)
';

$sql_cmds[] = '
create index v1_moderator_log_forum_id_idx on v1_moderator_log
(
   forum_id
)
';

$sql_cmds[] = '
create index v1_moderator_log_author_name_idx on v1_moderator_log
(
   author_name
)
';

$sql_cmds[] = '
create index v1_moderator_log_evt_tm_idx on v1_moderator_log
(
   event_time
)
';

$sql_cmds[] = '
create index v1_moderator_log_redundant_idx on v1_moderator_log
(
   redundant
)
';

$sql_cmds[] = '
create index v1_moderator_log_topic_id_idx on v1_moderator_log
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_moderator_log_src_topic_id_idx on v1_moderator_log
(
   source_topic_id
)
';

$sql_cmds[] = '
create table v1_morphology_dictionary
(
   root                 varchar(255) not null,
   word                 varchar(255) not null
)
';

$sql_cmds[] = '
create index v1_morphology_dictionary_root_idx on v1_morphology_dictionary
(
   root
)
';

$sql_cmds[] = '
create index v1_morphology_dictionary_word_idx on v1_morphology_dictionary
(
   word
)
';

$sql_cmds[] = '
create table v1_pinned_topics
(
   topic_id             int not null,
   user_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_pinned_topics_unq on v1_pinned_topics
(
   topic_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_pinned_topics_topic_id_idx on v1_pinned_topics
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_pinned_topics_user_id_idx on v1_pinned_topics
(
   user_id
)
';

$sql_cmds[] = '
create table v1_poll_options
(
   id                   int not null auto_increment,
   name                 varchar(700) not null,
   topic_id             int not null,
   primary key (id)
)
';

$sql_cmds[] = '
alter table v1_poll_options modify name varchar(700) null collate utf8mb4_bin
';

$sql_cmds[] = '
create unique index v1_poll_options_unq on v1_poll_options
(
   name,
   topic_id
)
';

$sql_cmds[] = '
create index v1_poll_options_topic_id_idx on v1_poll_options
(
   topic_id
)
';

$sql_cmds[] = '
create table v1_poll_user_answers
(
   tm                   datetime,
   user_id              int not null,
   option_id            int not null
)
';

$sql_cmds[] = '
create unique index v1_poll_user_answers_unq on v1_poll_user_answers
(
   user_id,
   option_id
)
';

$sql_cmds[] = '
create index v1_poll_user_answers_user_id_idx on v1_poll_user_answers
(
   user_id
)
';

$sql_cmds[] = '
create index v1_poll_user_answers_option_id_idx on v1_poll_user_answers
(
   option_id
)
';

$sql_cmds[] = '
create table v1_post
(
   id                   int not null auto_increment,
   user_id              int,
   author               varchar(255) not null,
   creation_date        datetime not null,
   pinned               tinyint not null default 0,
   deleted              tinyint not null default 0,
   text_content         mediumtext,
   html_content         mediumtext,
   searchable_content   mediumtext,
   has_picture          tinyint not null default 0,
   has_audio            tinyint not null default 0,
   has_video            tinyint not null default 0,
   has_link             tinyint not null default 0,
   has_code             tinyint not null default 0,
   has_attachment       tinyint not null default 0,
   has_attachment_ref   tinyint not null default 0,
   read_marker          varchar(255),
   ip                   varchar(250),
   last_updated_by      varchar(255),
   last_updated         datetime,
   self_edited          tinyint not null default 0,
   last_warned_by       varchar(255),
   last_warning         text,
   bb_parser_version    int not null default 1,
   topic_id             int not null,
   user_marker          varchar(255),
   user_agent           varchar(700),
   is_comment           tinyint not null default 0,
   is_adult             tinyint not null default 0,
   is_system            tinyint not null default 0,
   ref                  int,
   primary key (id)
)
';

$sql_cmds[] = '
create fulltext index v1_post_ftx on v1_post
(
   searchable_content
)
';

$sql_cmds[] = '
create index v1_post_is_deleted_idx on v1_post
(
   deleted
)
';

$sql_cmds[] = '
create index v1_post_user_id_idx on v1_post
(
   user_id
)
';

$sql_cmds[] = '
create index v1_post_topic_id_idx on v1_post
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_post_has_attachment_idx on v1_post
(
   has_attachment
)
';

$sql_cmds[] = '
create index v1_post_has_attachment_ref_idx on v1_post
(
   has_attachment_ref
)
';

$sql_cmds[] = '
create index v1_post_ip_idx on v1_post
(
   ip
)
';

$sql_cmds[] = '
create index v1_post_rm_idx on v1_post
(
   read_marker
)
';

$sql_cmds[] = '
create index v1_post_author_idx on v1_post
(
   author
)
';

$sql_cmds[] = '
create index v1_post_creation_date_idx on v1_post
(
   creation_date
)
';

$sql_cmds[] = '
create index v1_post_is_pinned_idx on v1_post
(
   pinned
)
';

$sql_cmds[] = '
create index v1_post_user_marker_idx on v1_post
(
   user_marker
)
';

$sql_cmds[] = '
create index v1_post_has_video_idx on v1_post
(
   has_video
)
';

$sql_cmds[] = '
create index v1_post_has_audio_idx on v1_post
(
   has_audio
)
';

$sql_cmds[] = '
create index v1_post_has_link_idx on v1_post
(
   has_link
)
';

$sql_cmds[] = '
create index v1_post_has_code_idx on v1_post
(
   has_code
)
';

$sql_cmds[] = '
create index v1_post_is_comment_idx on v1_post
(
   is_comment
)
';

$sql_cmds[] = '
create index v1_post_is_adult_idx on v1_post
(
   is_adult
)
';

$sql_cmds[] = '
create index v1_post_has_picture_idx on v1_post
(
   has_picture
)
';

$sql_cmds[] = '
create index v1_post_ref_idx on v1_post
(
   ref
)
';

$sql_cmds[] = '
create table v1_post_hierarchy
(
   parent_post_id       int not null,
   reply_post_id        int not null
)
';

$sql_cmds[] = '
create unique index v1_post_hierarchy_unq on v1_post_hierarchy
(
   parent_post_id,
   reply_post_id
)
';

$sql_cmds[] = '
create index v1_post_hierarchy_ppost_id on v1_post_hierarchy
(
   parent_post_id
)
';

$sql_cmds[] = '
create index v1_post_hierarchy_rpost_id on v1_post_hierarchy
(
   reply_post_id
)
';

$sql_cmds[] = '
create table v1_post_history
(
   id                   int not null auto_increment,
   dt                   datetime not null,
   author               varchar(255),
   self_edited          tinyint not null default 0,
   text_content         mediumtext,
   html_content         mediumtext,
   post_id              int not null,
   primary key (id)
)
';

$sql_cmds[] = '
create index v1_post_history_post_id_idx on v1_post_history
(
   post_id
)
';

$sql_cmds[] = '
create table v1_post_rating
(
   id                   int not null auto_increment,
   rating               int not null,
   dt                   datetime,
   post_id              int not null,
   user_id              int not null,
   rater_ignored        tinyint not null default 0,
   primary key (id)
)
';

$sql_cmds[] = '
create unique index v1_post_rating_unq on v1_post_rating
(
   post_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_post_rating_post_idx on v1_post_rating
(
   post_id
)
';

$sql_cmds[] = '
create index v1_post_rating_user_idx on v1_post_rating
(
   user_id
)
';

$sql_cmds[] = '
create index v1_post_rating_raiting_idx on v1_post_rating
(
   rating
)
';

$sql_cmds[] = '
create table v1_post_statistics
(
   post_id              int not null,
   like_count           bigint not null default 0,
   dislike_count        bigint not null default 0
)
';

$sql_cmds[] = '
create unique index v1_post_statistics_unq on v1_post_statistics
(
   post_id
)
';

$sql_cmds[] = '
create table v1_post_subscription
(
   post_id              int not null,
   user_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_post_subscription_unq on v1_post_subscription
(
   post_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_post_subscription_post_id_idx on v1_post_subscription
(
   post_id
)
';

$sql_cmds[] = '
create index v1_post_subscription_user_id_idx on v1_post_subscription
(
   user_id
)
';

$sql_cmds[] = '
create table v1_private_topics
(
   last_visit_date      datetime,
   topic_id             int not null,
   participant_id       int not null
)
';

$sql_cmds[] = '
create unique index v1_private_topics_unq on v1_private_topics
(
   topic_id,
   participant_id
)
';

$sql_cmds[] = '
create index v1_private_topics_topic_id_idx on v1_private_topics
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_private_topics_participant_id_idx on v1_private_topics
(
   participant_id
)
';

$sql_cmds[] = '
create table v1_protected_guests
(
   guest_name           varchar(255) not null,
   guest_name_hash      varchar(255)
)
';

$sql_cmds[] = '
create unique index v1_protected_guests_unq on v1_protected_guests
(
   guest_name
)
';

$sql_cmds[] = '
create index v1_protected_guests_hash_idx on v1_protected_guests
(
   guest_name_hash
)
';

$sql_cmds[] = '
create table v1_read_marker_activity
(
   read_marker          varchar(255) not null,
   last_activity        datetime,
   first_activity       datetime,
   ip                   varchar(250),
   author               varchar(255),
   user_agent           varchar(500),
   hits                 int not null default 0,
   current_name_start   datetime,
   current_name_hits    int not null default 0
)
';

$sql_cmds[] = '
create unique index v1_read_marker_activity_unq on v1_read_marker_activity
(
   read_marker
)
';

$sql_cmds[] = '
create table v1_reserved_names
(
   user_name            varchar(255) not null,
   user_name_hash       varchar(255)
)
';

$sql_cmds[] = '
create unique index v1_reserved_names_unq on v1_reserved_names
(
   user_name
)
';

$sql_cmds[] = '
create table v1_settings
(
   moderator_log        varchar(100),
   default_sender       varchar(255),
   receiver             varchar(255),
   max_att_size         int,
   max_att_size_audiovideo int,
   max_messages_minute  int,
   max_messages_hour    int,
   max_messages_day     int,
   min_search_interval  int,
   whois_server         varchar(500),
   hide_online_status   tinyint not null default 0,
   approval_required    tinyint not null default 0,
   max_rates_hour       int,
   max_topics_day       int,
   rates_active         tinyint not null default 0,
   dislikes_active      tinyint not null default 0,
   dislikes_anonym      tinyint not null default 0,
   skin                 varchar(255),
   max_poll_options     int,
   max_user_name_symbols int,
   max_topic_name_symbols int,
   max_message_length   int,
   max_pinned_topics    int,
   max_private_members  int,
   block_tor_ips        tinyint not null default 0,
   celebration_active   tinyint not null default 0,
   mourning_active      tinyint not null default 0,
   snow_effect          tinyint not null default 0,
   hide_users_from_robots tinyint not null default 0,
   archive_mode         tinyint not null default 0,
   hash_ip_addresses    tinyint not null default 0
)
';

$sql_cmds[] = '
create table v1_topic
(
   id                   int not null auto_increment,
   name                 varchar(700) not null,
   author               varchar(255) not null,
   creation_date        datetime not null,
   deleted              tinyint not null default 0,
   closed               tinyint not null default 0,
   pinned               tinyint not null default 0,
   read_marker          varchar(255),
   has_pinned_post      tinyint not null default 0,
   merged               int,
   is_poll              tinyint not null default 0,
   poll_comment         text,
   poll_results_delayed tinyint not null default 0,
   no_guests            tinyint not null default 0,
   forum_id             int not null,
   user_id              int,
   user_marker          varchar(255),
   is_private           tinyint not null default 0,
   publish_delay        tinyint not null default 0,
   profiled_topic       tinyint not null default 0,
   request_moderation   tinyint not null default 0,
   ref                  int,
   primary key (id)
)
';

$sql_cmds[] = '
create fulltext index v1_topic_ftx on v1_topic
(
   name
)
';

$sql_cmds[] = '
create index v1_topic_name_idx on v1_topic
(
   name
)
';

$sql_cmds[] = '
create index v1_topic_user_id_idx on v1_topic
(
   user_id
)
';

$sql_cmds[] = '
create index v1_topic_forum_id_idx on v1_topic
(
   forum_id
)
';

$sql_cmds[] = '
create index v1_topic_is_deleted_idx on v1_topic
(
   deleted
)
';

$sql_cmds[] = '
create index v1_topic_rm_idx on v1_topic
(
   read_marker
)
';

$sql_cmds[] = '
create index v1_topic_is_pinned_idx on v1_topic
(
   pinned
)
';

$sql_cmds[] = '
create index v1_topic_is_private_idx on v1_topic
(
   is_private
)
';

$sql_cmds[] = '
create index v1_topic_publish_delay_idx on v1_topic
(
   publish_delay
)
';

$sql_cmds[] = '
create index v1_topic_author_idx on v1_topic
(
   author
)
';

$sql_cmds[] = '
create index v1_topic_creation_date_idx on v1_topic
(
   creation_date
)
';

$sql_cmds[] = '
create index v1_topic_ref_idx on v1_topic
(
   ref
)
';

$sql_cmds[] = '
create table v1_topic_blocked
(
   topic_id             int not null,
   user_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_topic_blocked_unq on v1_topic_blocked
(
   topic_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_topic_blocked_topic_id_idx on v1_topic_blocked
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_topic_blocked_user_id_idx on v1_topic_blocked
(
   user_id
)
';

$sql_cmds[] = '
create table v1_topic_moderator
(
   topic_id             int not null,
   user_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_topic_moderator_unq on v1_topic_moderator
(
   topic_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_topic_moderator_topic_id_idx on v1_topic_moderator
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_topic_moderator_user_id_idx on v1_topic_moderator
(
   user_id
)
';

$sql_cmds[] = '
create table v1_topic_participants
(
   user_id              int not null,
   topic_id             int not null
)
';

$sql_cmds[] = '
create unique index v1_topic_participants_unq on v1_topic_participants
(
   user_id,
   topic_id
)
';

$sql_cmds[] = '
create table v1_topic_read_markers
(
   topic_id             int,
   read_marker          varchar(255),
   last_read_date       datetime,
   ip                   varchar(250)
)
';

$sql_cmds[] = '
create unique index v1_topic_read_markers_tprm_idx on v1_topic_read_markers
(
   topic_id,
   read_marker
)
';

$sql_cmds[] = '
create index v1_topic_read_markers_rm_idx on v1_topic_read_markers
(
   read_marker
)
';

$sql_cmds[] = '
create index v1_topic_read_markers_lrdt_idx on v1_topic_read_markers
(
   last_read_date
)
';

$sql_cmds[] = '
create table v1_topic_statistics
(
   topic_id             int not null,
   post_count           int not null default 0,
   post_count_total     int not null default 0,
   hits_count           int not null default 0,
   bot_hits_count       int not null default 0,
   last_message_date    datetime,
   last_message_id      int
)
';

$sql_cmds[] = '
create unique index v1_topic_statistics_unq on v1_topic_statistics
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_topic_statistics_lmid_idx on v1_topic_statistics
(
   last_message_id
)
';

$sql_cmds[] = '
create index v1_topic_statistics_lmdate_idx on v1_topic_statistics
(
   last_message_date
)
';

$sql_cmds[] = '
create table v1_topic_subscription
(
   topic_id             int not null,
   user_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_topic_subscription_unq on v1_topic_subscription
(
   topic_id,
   user_id
)
';

$sql_cmds[] = '
create index v1_topic_subscription_topic_id_idx on v1_topic_subscription
(
   topic_id
)
';

$sql_cmds[] = '
create index v1_topic_subscription_user_id_idx on v1_topic_subscription
(
   user_id
)
';

$sql_cmds[] = '
create table v1_topic_view_history
(
   user_id              int,
   guest_name           varchar(255),
   topic_id             int not null,
   dt                   datetime not null,
   ip                   varchar(250)
)
';

$sql_cmds[] = '
create unique index v1_topic_view_history_unq on v1_topic_view_history
(
   user_id,
   topic_id,
   guest_name
)
';

$sql_cmds[] = '
create index v1_topic_view_history_user_idx on v1_topic_view_history
(
   user_id
)
';

$sql_cmds[] = '
create index v1_topic_view_history_dt_idx on v1_topic_view_history
(
   dt
)
';

$sql_cmds[] = '
create index v1_topic_view_history_guest_name_idx on v1_topic_view_history
(
   guest_name
)
';

$sql_cmds[] = '
create table v1_tor_ips
(
   ip                   varchar(250) not null,
   hashed_ip            varchar(250) not null,
   block_level          int not null default 0,
   refresh_date         datetime
)
';

$sql_cmds[] = '
create unique index v1_tor_ips_unq on v1_tor_ips
(
   ip
)
';

$sql_cmds[] = '
create table v1_user
(
   id                   int not null auto_increment,
   login                varchar(255) not null,
   password_hash        varchar(255) not null,
   user_name            varchar(255) not null,
   user_name_hash       varchar(255),
   email                varchar(255) not null,
   email_hash           varchar(255),
   hide_email           tinyint not null default 1,
   registration_date    datetime not null,
   last_visit_date      datetime,
   last_post_date       datetime,
   is_admin             tinyint not null default 0,
   message              varchar(500),
   info                 text,
   homepage             varchar(500),
   signature            text,
   pwd_reset_hash       varchar(255),
   pwd_reset_expire     datetime,
   activation_hash      varchar(100),
   activation_expire    datetime,
   activated            tinyint not null default 0,
   died                 tinyint not null default 0,
   autologin_hash       varchar(255),
   blocked              tinyint not null default 0,
   self_blocked         tinyint not null default 0,
   block_expires        datetime,
   block_reason         text,
   hide_user_avatars    tinyint not null default 0,
   hide_user_info       tinyint not null default 0,
   hide_pictures        tinyint not null default 0,
   donot_hide_adult_pictures tinyint not null default 0,
   hide_ignored         tinyint not null default 0,
   location             varchar(255),
   hidden               tinyint not null default 0,
   send_notifications   tinyint not null default 0,
   donot_notify_on_rates tinyint not null default 0,
   no_private_messages  tinyint not null default 0,
   turnoff_personal_appeals tinyint not null default 0,
   turnoff_events       tinyint not null default 0,
   approved             tinyint not null default 0,
   read_marker          varchar(255),
   ip                   varchar(250),
   last_ip              varchar(250),
   ignore_guests_blacklist tinyint not null default 0,
   ignore_guests_whitelist tinyint not null default 0,
   ignore_new_guests    tinyint not null default 0,
   no_video_expand      tinyint not null default 0,
   logout               tinyint not null default 0,
   last_logout_date     datetime,
   last_events_view_date datetime,
   rating_blocked       tinyint not null default 0,
   time_zone            varchar(255),
   privileged           tinyint not null default 0,
   privileged_topic_moderator tinyint not null default 0,
   notify_about_new_users tinyint not null default 0,
   notify_citation      tinyint not null default 0,
   skin                 varchar(255),
   skin_properties      text,
   interface_language   varchar(50),
   global_ban_allowed   tinyint not null default 0,
   show_ip              tinyint not null default 0,
   notify_on_words      tinyint not null default 0,
   words_to_notify      text,
   last_host            varchar(255),
   custom_css           text,
   custom_smiles        text,
   ref                  int,
   primary key (id)
)
';

$sql_cmds[] = '
create unique index v1_user_login_unq on v1_user
(
   login
)
';

$sql_cmds[] = '
create unique index v1_user_nickname_unq on v1_user
(
   user_name
)
';

$sql_cmds[] = '
create index v1_user_nickname_hash_idx on v1_user
(
   user_name_hash
)
';

$sql_cmds[] = '
create unique index v1_user_email_unq on v1_user
(
   email_hash
)
';

$sql_cmds[] = '
create index v1_user_autologin_hash_idx on v1_user
(
   autologin_hash
)
';

$sql_cmds[] = '
create index v1_user_pwd_reset_hash_idx on v1_user
(
   pwd_reset_hash
)
';

$sql_cmds[] = '
create index v1_user_block_expires_idx on v1_user
(
   block_expires
)
';

$sql_cmds[] = '
create unique index v1_user_read_marker_unq on v1_user
(
   read_marker
)
';

$sql_cmds[] = '
create table v1_user_comment
(
   user_id              int not null,
   commented_user_id    int not null,
   comment              text
)
';

$sql_cmds[] = '
create unique index v1_user_comment_unq on v1_user_comment
(
   user_id,
   commented_user_id
)
';

$sql_cmds[] = '
create table v1_user_login_tries
(
   login                varchar(250) not null,
   ip                   varchar(250) not null,
   dt                   datetime not null
)
';

$sql_cmds[] = '
create index v1_user_login_tries_ip_idx on v1_user_login_tries
(
   ip
)
';

$sql_cmds[] = '
create table v1_user_statistics
(
   user_id              int not null,
   post_count           bigint not null default 0,
   like_count           bigint not null default 0,
   dislike_count        bigint not null default 0,
   topic_count          bigint not null default 0,
   time_online          bigint not null default 0
)
';

$sql_cmds[] = '
create unique index v1_user_statistics_unq on v1_user_statistics
(
   user_id
)
';

$sql_cmds[] = '
create table v1_user_subscription
(
   user_id              int not null,
   subscribed_user_id   int,
   subscribed_user_name varchar(255),
   tm                   datetime not null,
   last_view            datetime
)
';

$sql_cmds[] = '
create unique index v1_user_subscription_unq on v1_user_subscription
(
   user_id,
   subscribed_user_id,
   subscribed_user_name
)
';

$sql_cmds[] = '
create index v1_user_subscription_usr_idx on v1_user_subscription
(
   user_id
)
';

$sql_cmds[] = '
create index v1_user_subscription_usrname_idx on v1_user_subscription
(
   subscribed_user_name
)
';

$sql_cmds[] = '
create index v1_user_subscription_usrid_idx on v1_user_subscription
(
   subscribed_user_id
)
';

$sql_cmds[] = '
create table v1_user_tag_post
(
   tag_id               int not null,
   post_id              int not null
)
';

$sql_cmds[] = '
create unique index v1_user_tag_post_unq on v1_user_tag_post
(
   tag_id,
   post_id
)
';

$sql_cmds[] = '
create index v1_user_tag_post_tag_id_idx on v1_user_tag_post
(
   tag_id
)
';

$sql_cmds[] = '
create index v1_user_tag_post_post_id_idx on v1_user_tag_post
(
   post_id
)
';

$sql_cmds[] = '
create table v1_user_tags
(
   id                   int not null auto_increment,
   name                 varchar(255),
   user_id              int not null,
   primary key (id)
)
';

$sql_cmds[] = '
create unique index v1_user_tags_unq on v1_user_tags
(
   name,
   user_id
)
';

$sql_cmds[] = '
create index v1_user_tags_user_id_idx on v1_user_tags
(
   user_id
)
';

$sql_cmds[] = '
create procedure v1_deep_collect_replies(in p_oid integer)

deterministic
sql security invoker
comment \'\'
proc:
begin
   declare affected_cnt integer;

   delete from tmp_children;
   insert into tmp_children (id) values (p_oid);

   create temporary table if not exists tmp_del_items2(id integer);
   create temporary table if not exists tmp_del_items3(id integer);

   delete from tmp_del_items2;
   insert into tmp_del_items2 (id)
   select id from tmp_children;

   delete from tmp_del_items3;
   insert into tmp_del_items3 (id)
   select id from tmp_children;
   
   set @affected_cnt = 0;
   
   repeat

     insert into tmp_children (id)
     select reply_post_id from v1_post_hierarchy
     where parent_post_id in (select id from tmp_del_items2)
     and reply_post_id not in (select id from tmp_del_items3);

     set @affected_cnt = row_count();

     delete from tmp_del_items2;
     insert into tmp_del_items2 (id)
     select id from tmp_children;

     delete from tmp_del_items3;
     insert into tmp_del_items3 (id)
     select id from tmp_children;
     
   until @affected_cnt = 0
   end repeat;
   
end;
';

?>