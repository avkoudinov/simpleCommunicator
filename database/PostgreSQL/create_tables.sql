/*==============================================================*/
/* DBMS name:      PostgreSQL 8                                 */
/* Created on:     20.09.2026 16:18:08                          */
/*==============================================================*/


/*==============================================================*/
/* Table: v1_attachment                                         */
/*==============================================================*/
create table v1_attachment (
   id                   SERIAL not null,
   post_id              INT4                 not null,
   nr                   INT4                 not null,
   name                 VARCHAR(700)         null,
   origin_name          VARCHAR(700)         null,
   type                 VARCHAR(255)         null,
   deleted              INT2                 not null default 0,
   user_id              INT4                 null,
   favourite            INT2                 not null default 0,
   last_post_id         INT4                 not null,
   constraint PK_V1_ATTACHMENT primary key (id)
);

/*==============================================================*/
/* Index: v1_attachment_unq                                     */
/*==============================================================*/
create unique index v1_attachment_unq on v1_attachment (
post_id,
nr
);

/*==============================================================*/
/* Index: v1_attachment_name_idx                                */
/*==============================================================*/
create  index v1_attachment_name_idx on v1_attachment (
name
);

/*==============================================================*/
/* Index: v1_attachment_type_idx                                */
/*==============================================================*/
create  index v1_attachment_type_idx on v1_attachment (
type
);

/*==============================================================*/
/* Index: v1_attachment_user_idx                                */
/*==============================================================*/
create  index v1_attachment_user_idx on v1_attachment (
user_id
);

/*==============================================================*/
/* Index: v1_attachment_favourite_idx                           */
/*==============================================================*/
create  index v1_attachment_favourite_idx on v1_attachment (
favourite
);

/*==============================================================*/
/* Index: v1_attachment_last_post_idx                           */
/*==============================================================*/
create  index v1_attachment_last_post_idx on v1_attachment (
last_post_id
);

/*==============================================================*/
/* Table: v1_auto_saved                                         */
/*==============================================================*/
create table v1_auto_saved (
   topic_id             INT4                 not null,
   read_marker          VARCHAR(255)         not null,
   dt                   TIMESTAMP            not null,
   text_content         TEXT                 null
);

/*==============================================================*/
/* Index: v1_auto_saved_tid_idx                                 */
/*==============================================================*/
create  index v1_auto_saved_tid_idx on v1_auto_saved (
topic_id
);

/*==============================================================*/
/* Index: v1_auto_saved_dt_idx                                  */
/*==============================================================*/
create  index v1_auto_saved_dt_idx on v1_auto_saved (
dt
);

/*==============================================================*/
/* Index: v1_auto_saved_rm_idx                                  */
/*==============================================================*/
create  index v1_auto_saved_rm_idx on v1_auto_saved (
read_marker
);

/*==============================================================*/
/* Table: v1_banned_ips                                         */
/*==============================================================*/
create table v1_banned_ips (
   ip                   VARCHAR(250)         not null,
   banned_until         TIMESTAMP            not null,
   hits                 INT4                 not null,
   hit_limit            INT4                 not null default 0,
   check_period         INT4                 not null default 60,
   atype                VARCHAR(255)         null,
   statistics_request   INT4                 not null default 0
);

/*==============================================================*/
/* Index: v1_banned_ips_idx                                     */
/*==============================================================*/
create  index v1_banned_ips_idx on v1_banned_ips (
ip
);

/*==============================================================*/
/* Table: v1_browser_daily_statistics                           */
/*==============================================================*/
create table v1_browser_daily_statistics (
   dt                   DATE                 not null,
   browser              VARCHAR(250)         null,
   os                   VARCHAR(250)         null,
   bot                  VARCHAR(250)         null,
   read_marker          VARCHAR(255)         null,
   bot_hits_count       INT4                 not null default 0
);

/*==============================================================*/
/* Index: v1_brwr_dstatistics_dt_idx                            */
/*==============================================================*/
create  index v1_brwr_dstatistics_dt_idx on v1_browser_daily_statistics (
dt
);

/*==============================================================*/
/* Index: v1_brwr_dstatistics_brwr_idx                          */
/*==============================================================*/
create  index v1_brwr_dstatistics_brwr_idx on v1_browser_daily_statistics (
browser
);

/*==============================================================*/
/* Index: v1_brwr_dstatistics_os_idx                            */
/*==============================================================*/
create  index v1_brwr_dstatistics_os_idx on v1_browser_daily_statistics (
os
);

/*==============================================================*/
/* Index: v1_brwr_dstatistics_bot_idx                           */
/*==============================================================*/
create  index v1_brwr_dstatistics_bot_idx on v1_browser_daily_statistics (
bot
);

/*==============================================================*/
/* Index: v1_brwr_dstatistics_rm_idx                            */
/*==============================================================*/
create  index v1_brwr_dstatistics_rm_idx on v1_browser_daily_statistics (
read_marker
);

/*==============================================================*/
/* Table: v1_cache_invalidation                                 */
/*==============================================================*/
create table v1_cache_invalidation (
   new_dt               TIMESTAMP            null
);

/*==============================================================*/
/* Table: v1_daily_statistics                                   */
/*==============================================================*/
create table v1_daily_statistics (
   user_id              INT4                 not null,
   forum_id             INT4                 not null,
   dt                   DATE                 not null,
   hits_count           INT4                 not null default 0,
   bot_hits_count       INT4                 not null default 0,
   post_count           INT4                 not null default 0,
   time_online          INT8                 not null default 0,
   bot                  VARCHAR(250)         not null
);

/*==============================================================*/
/* Index: v1_daily_statistics_unq                               */
/*==============================================================*/
create unique index v1_daily_statistics_unq on v1_daily_statistics (
user_id,
forum_id,
dt,
bot
);

/*==============================================================*/
/* Index: v1_daily_statistics_dt_idx                            */
/*==============================================================*/
create  index v1_daily_statistics_dt_idx on v1_daily_statistics (
dt
);

/*==============================================================*/
/* Index: v1_daily_statistics_user_idx                          */
/*==============================================================*/
create  index v1_daily_statistics_user_idx on v1_daily_statistics (
user_id
);

/*==============================================================*/
/* Index: v1_daily_statistics_forum_idx                         */
/*==============================================================*/
create  index v1_daily_statistics_forum_idx on v1_daily_statistics (
forum_id
);

/*==============================================================*/
/* Index: v1_daily_statistics_bot_idx                           */
/*==============================================================*/
create  index v1_daily_statistics_bot_idx on v1_daily_statistics (
bot
);

/*==============================================================*/
/* Table: v1_dual                                               */
/*==============================================================*/
create table v1_dual (
   dummy_val            INT2                 not null default 0
);

/*==============================================================*/
/* Table: v1_events                                             */
/*==============================================================*/
create table v1_events (
   id                   SERIAL not null,
   event_time           TIMESTAMP            not null,
   event_code           VARCHAR(255)         not null,
   params               TEXT                 null,
   author_name          VARCHAR(255)         null,
   author_id            INT4                 null,
   post_id              INT4                 null,
   is_new               INT2                 not null default 1,
   todo                 INT2                 not null default 0,
   user_id              INT4                 not null,
   redundant            INT2                 not null default 0,
   topic_name           VARCHAR(1000)        null,
   topic_id             INT4                 null,
   forum_name           VARCHAR(255)         null,
   forum_id             INT4                 null,
   note                 TEXT                 null,
   source_topic_name    VARCHAR(1000)        null,
   source_topic_id      INT4                 null,
   constraint PK_V1_EVENTS primary key (id)
);

/*==============================================================*/
/* Index: v1_events_evtime_idx                                  */
/*==============================================================*/
create  index v1_events_evtime_idx on v1_events (
event_time
);

/*==============================================================*/
/* Index: v1_events_user_id_idx                                 */
/*==============================================================*/
create  index v1_events_user_id_idx on v1_events (
user_id
);

/*==============================================================*/
/* Index: v1_events_post_id_idx                                 */
/*==============================================================*/
create  index v1_events_post_id_idx on v1_events (
post_id
);

/*==============================================================*/
/* Index: v1_events_redundant_idx                               */
/*==============================================================*/
create  index v1_events_redundant_idx on v1_events (
redundant
);

/*==============================================================*/
/* Index: v1_events_todo_idx                                    */
/*==============================================================*/
create  index v1_events_todo_idx on v1_events (
todo
);

/*==============================================================*/
/* Index: v1_events_code_idx                                    */
/*==============================================================*/
create  index v1_events_code_idx on v1_events (
event_code
);

/*==============================================================*/
/* Index: v1_events_author_name_idx                             */
/*==============================================================*/
create  index v1_events_author_name_idx on v1_events (
author_name
);

/*==============================================================*/
/* Index: v1_events_topic_id_idx                                */
/*==============================================================*/
create  index v1_events_topic_id_idx on v1_events (
topic_id
);

/*==============================================================*/
/* Index: v1_events_src_topic_id_idx                            */
/*==============================================================*/
create  index v1_events_src_topic_id_idx on v1_events (
source_topic_id
);

/*==============================================================*/
/* Index: v1_events_forum_id_idx                                */
/*==============================================================*/
create  index v1_events_forum_id_idx on v1_events (
forum_id
);

/*==============================================================*/
/* Table: v1_favourite_posts                                    */
/*==============================================================*/
create table v1_favourite_posts (
   post_id              INT4                 not null,
   user_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_favourite_posts_unq                                */
/*==============================================================*/
create unique index v1_favourite_posts_unq on v1_favourite_posts (
post_id,
user_id
);

/*==============================================================*/
/* Index: v1_favourite_posts_post_id_idx                        */
/*==============================================================*/
create  index v1_favourite_posts_post_id_idx on v1_favourite_posts (
post_id
);

/*==============================================================*/
/* Index: v1_favourite_posts_user_id_idx                        */
/*==============================================================*/
create  index v1_favourite_posts_user_id_idx on v1_favourite_posts (
user_id
);

/*==============================================================*/
/* Table: v1_favourite_topics                                   */
/*==============================================================*/
create table v1_favourite_topics (
   topic_id             INT4                 not null,
   user_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_fav_topics_unq                                     */
/*==============================================================*/
create unique index v1_fav_topics_unq on v1_favourite_topics (
topic_id,
user_id
);

/*==============================================================*/
/* Index: v1_fav_topics_topic_id_idx                            */
/*==============================================================*/
create  index v1_fav_topics_topic_id_idx on v1_favourite_topics (
topic_id
);

/*==============================================================*/
/* Index: v1_fav_topics_user_id_idx                             */
/*==============================================================*/
create  index v1_fav_topics_user_id_idx on v1_favourite_topics (
user_id
);

/*==============================================================*/
/* Table: v1_forum                                              */
/*==============================================================*/
create table v1_forum (
   id                   SERIAL not null,
   name                 VARCHAR(255)         not null,
   protected_by_password INT2                 not null default 0,
   password             VARCHAR(255)         null,
   creation_date        TIMESTAMP            not null,
   restricted_access    INT2                 not null default 0,
   allow_edit           INT2                 not null default 0,
   no_guests            INT2                 not null default 0,
   user_posting_as_guest INT2                 not null default 0,
   restricted_guest_mode INT2                 not null default 0,
   hide_from_robots     INT2                 not null default 0,
   description          VARCHAR(500)         null,
   deleted              INT2                 not null default 0,
   closed               INT2                 not null default 0,
   sort_order           INT4                 not null default 0,
   access_duration      INT4                 null,
   access_message_count INT4                 null,
   stringent_rules      INT2                 not null default 0,
   disable_ignore       INT2                 not null default 0,
   forum_group_id       INT4                 null,
   constraint PK_V1_FORUM primary key (id)
);

/*==============================================================*/
/* Index: v1_forum_name_unq                                     */
/*==============================================================*/
create unique index v1_forum_name_unq on v1_forum (
name
);

/*==============================================================*/
/* Index: v1_forum_is_deleted_idx                               */
/*==============================================================*/
create  index v1_forum_is_deleted_idx on v1_forum (
deleted
);

/*==============================================================*/
/* Index: v1_forum_protected_by_pwd_idx                         */
/*==============================================================*/
create  index v1_forum_protected_by_pwd_idx on v1_forum (
protected_by_password
);

/*==============================================================*/
/* Index: v1_forum_access_idx                                   */
/*==============================================================*/
create  index v1_forum_access_idx on v1_forum (
restricted_access
);

/*==============================================================*/
/* Table: v1_forum_blocked                                      */
/*==============================================================*/
create table v1_forum_blocked (
   block_expires        TIMESTAMP            null,
   user_id              INT4                 not null,
   forum_id             INT4                 not null
);

/*==============================================================*/
/* Index: v1_forum_blocked_unq                                  */
/*==============================================================*/
create unique index v1_forum_blocked_unq on v1_forum_blocked (
user_id,
forum_id
);

/*==============================================================*/
/* Index: v1_forum_blocked_user_id_idx                          */
/*==============================================================*/
create  index v1_forum_blocked_user_id_idx on v1_forum_blocked (
user_id
);

/*==============================================================*/
/* Index: v1_forum_blocked_forum_id_idx                         */
/*==============================================================*/
create  index v1_forum_blocked_forum_id_idx on v1_forum_blocked (
forum_id
);

/*==============================================================*/
/* Table: v1_forum_group                                        */
/*==============================================================*/
create table v1_forum_group (
   id                   SERIAL not null,
   name                 VARCHAR(255)         not null,
   sort_order           INT4                 not null default 0,
   constraint PK_V1_FORUM_GROUP primary key (id)
);

/*==============================================================*/
/* Index: v1_forum_group_name_unq                               */
/*==============================================================*/
create unique index v1_forum_group_name_unq on v1_forum_group (
name
);

/*==============================================================*/
/* Table: v1_forum_hits                                         */
/*==============================================================*/
create table v1_forum_hits (
   forum_id             INT4                 null,
   topic_id             INT4                 null,
   dt                   TIMESTAMP            not null,
   user_id              INT4                 null,
   hits_count           INT4                 not null default 0,
   duration             INT4                 not null default 0,
   guest_name           VARCHAR(250)         null,
   user_agent           VARCHAR(700)         null,
   referrer             VARCHAR(700)         null,
   uri                  VARCHAR(2000)        null,
   ip                   VARCHAR(250)         null,
   browser              VARCHAR(250)         null,
   os                   VARCHAR(250)         null,
   bot                  VARCHAR(250)         null,
   read_marker          VARCHAR(255)         null,
   statistics_request   INT4                 not null default 0,
   headers              TEXT                 null
);

/*==============================================================*/
/* Index: v1_forum_hits_forum_idx                               */
/*==============================================================*/
create  index v1_forum_hits_forum_idx on v1_forum_hits (
forum_id
);

/*==============================================================*/
/* Index: v1_forum_hits_dt_idx                                  */
/*==============================================================*/
create  index v1_forum_hits_dt_idx on v1_forum_hits (
dt
);

/*==============================================================*/
/* Index: v1_forum_hits_user_idx                                */
/*==============================================================*/
create  index v1_forum_hits_user_idx on v1_forum_hits (
user_id
);

/*==============================================================*/
/* Index: v1_forum_hits_topic_idx                               */
/*==============================================================*/
create  index v1_forum_hits_topic_idx on v1_forum_hits (
topic_id
);

/*==============================================================*/
/* Index: v1_forum_hits_ua_idx                                  */
/*==============================================================*/
create  index v1_forum_hits_ua_idx on v1_forum_hits (
user_agent
);

/*==============================================================*/
/* Index: v1_forum_hits_rm_idx                                  */
/*==============================================================*/
create  index v1_forum_hits_rm_idx on v1_forum_hits (
read_marker
);

/*==============================================================*/
/* Index: v1_forum_hits_guest_name_idx                          */
/*==============================================================*/
create  index v1_forum_hits_guest_name_idx on v1_forum_hits (
guest_name
);

/*==============================================================*/
/* Index: v1_forum_hits_ip_idx                                  */
/*==============================================================*/
create  index v1_forum_hits_ip_idx on v1_forum_hits (
ip
);

/*==============================================================*/
/* Index: v1_forum_hits_bot_idx                                 */
/*==============================================================*/
create  index v1_forum_hits_bot_idx on v1_forum_hits (
bot
);

/*==============================================================*/
/* Index: v1_forum_hits_browser_idx                             */
/*==============================================================*/
create  index v1_forum_hits_browser_idx on v1_forum_hits (
browser
);

/*==============================================================*/
/* Index: v1_forum_hits_os_idx                                  */
/*==============================================================*/
create  index v1_forum_hits_os_idx on v1_forum_hits (
os
);

/*==============================================================*/
/* Table: v1_forum_member                                       */
/*==============================================================*/
create table v1_forum_member (
   user_id              INT4                 not null,
   forum_id             INT4                 not null
);

/*==============================================================*/
/* Index: v1_forum_member_unq                                   */
/*==============================================================*/
create unique index v1_forum_member_unq on v1_forum_member (
user_id,
forum_id
);

/*==============================================================*/
/* Index: v1_forum_member_user_id_idx                           */
/*==============================================================*/
create  index v1_forum_member_user_id_idx on v1_forum_member (
user_id
);

/*==============================================================*/
/* Index: v1_forum_member_forum_id_idx                          */
/*==============================================================*/
create  index v1_forum_member_forum_id_idx on v1_forum_member (
forum_id
);

/*==============================================================*/
/* Table: v1_forum_moderator                                    */
/*==============================================================*/
create table v1_forum_moderator (
   user_id              INT4                 not null,
   forum_id             INT4                 not null
);

/*==============================================================*/
/* Index: v1_forum_moderator_unq                                */
/*==============================================================*/
create unique index v1_forum_moderator_unq on v1_forum_moderator (
user_id,
forum_id
);

/*==============================================================*/
/* Index: v1_forum_moderator_user_id_idx                        */
/*==============================================================*/
create  index v1_forum_moderator_user_id_idx on v1_forum_moderator (
user_id
);

/*==============================================================*/
/* Index: v1_forum_moderator_forum_id_idx                       */
/*==============================================================*/
create  index v1_forum_moderator_forum_id_idx on v1_forum_moderator (
forum_id
);

/*==============================================================*/
/* Table: v1_forum_read_markers                                 */
/*==============================================================*/
create table v1_forum_read_markers (
   read_marker          VARCHAR(255)         not null,
   forum_id             INT4                 not null,
   first_read_date      TIMESTAMP            null,
   last_activity        TIMESTAMP            null,
   first_activity       TIMESTAMP            null,
   ip                   VARCHAR(250)         null
);

/*==============================================================*/
/* Index: v1_fread_markers_fmrm_idx                             */
/*==============================================================*/
create unique index v1_fread_markers_fmrm_idx on v1_forum_read_markers (
read_marker,
forum_id
);

/*==============================================================*/
/* Index: v1_fread_markers_lastact_idx                          */
/*==============================================================*/
create  index v1_fread_markers_lastact_idx on v1_forum_read_markers (
last_activity
);

/*==============================================================*/
/* Index: v1_fread_markers_rm_idx                               */
/*==============================================================*/
create  index v1_fread_markers_rm_idx on v1_forum_read_markers (
read_marker
);

/*==============================================================*/
/* Index: v1_fread_markers_frdt_idx                             */
/*==============================================================*/
create  index v1_fread_markers_frdt_idx on v1_forum_read_markers (
first_read_date
);

/*==============================================================*/
/* Table: v1_forum_statistics                                   */
/*==============================================================*/
create table v1_forum_statistics (
   forum_id             INT4                 not null,
   topic_count          INT4                 not null default 0,
   topic_count_total    INT4                 not null default 0,
   last_message_date    TIMESTAMP            null,
   last_message_id      INT4                 null
);

/*==============================================================*/
/* Index: v1_forum_statistics_unq                               */
/*==============================================================*/
create unique index v1_forum_statistics_unq on v1_forum_statistics (
forum_id
);

/*==============================================================*/
/* Table: v1_found_post_cache                                   */
/*==============================================================*/
create table v1_found_post_cache (
   post_id              INT4                 not null,
   topic_id             INT4                 not null,
   session_id           VARCHAR(255)         not null,
   dt                   TIMESTAMP            not null,
   search_hash          VARCHAR(255)         null
);

/*==============================================================*/
/* Index: v1_found_post_cache_session_idx                       */
/*==============================================================*/
create  index v1_found_post_cache_session_idx on v1_found_post_cache (
session_id
);

/*==============================================================*/
/* Index: v1_found_post_cache_post_idx                          */
/*==============================================================*/
create  index v1_found_post_cache_post_idx on v1_found_post_cache (
post_id
);

/*==============================================================*/
/* Index: v1_found_post_cache_topic_idx                         */
/*==============================================================*/
create  index v1_found_post_cache_topic_idx on v1_found_post_cache (
topic_id
);

/*==============================================================*/
/* Index: v1_found_post_cache_hash_idx                          */
/*==============================================================*/
create  index v1_found_post_cache_hash_idx on v1_found_post_cache (
search_hash
);

/*==============================================================*/
/* Index: v1_found_post_cache_dt_idx                            */
/*==============================================================*/
create  index v1_found_post_cache_dt_idx on v1_found_post_cache (
dt
);

/*==============================================================*/
/* Table: v1_found_topic_cache                                  */
/*==============================================================*/
create table v1_found_topic_cache (
   topic_id             INT4                 not null,
   session_id           VARCHAR(255)         not null,
   dt                   TIMESTAMP            not null,
   search_hash          VARCHAR(255)         null
);

/*==============================================================*/
/* Index: v1_found_tp_cache_session_idx                         */
/*==============================================================*/
create  index v1_found_tp_cache_session_idx on v1_found_topic_cache (
session_id
);

/*==============================================================*/
/* Index: v1_found_topic_cache_topic_idx                        */
/*==============================================================*/
create  index v1_found_topic_cache_topic_idx on v1_found_topic_cache (
topic_id
);

/*==============================================================*/
/* Index: v1_found_topic_cache_hash_idx                         */
/*==============================================================*/
create  index v1_found_topic_cache_hash_idx on v1_found_topic_cache (
search_hash
);

/*==============================================================*/
/* Index: v1_found_topic_cache_dt_idx                           */
/*==============================================================*/
create  index v1_found_topic_cache_dt_idx on v1_found_topic_cache (
dt
);

/*==============================================================*/
/* Table: v1_hide_guest_avatars                                 */
/*==============================================================*/
create table v1_hide_guest_avatars (
   user_id              INT4                 not null,
   avatar               VARCHAR(250)         not null
);

/*==============================================================*/
/* Index: v1_hide_guest_avatars_unq                             */
/*==============================================================*/
create unique index v1_hide_guest_avatars_unq on v1_hide_guest_avatars (
user_id,
avatar
);

/*==============================================================*/
/* Table: v1_hide_profile                                       */
/*==============================================================*/
create table v1_hide_profile (
   user_id              INT4                 not null,
   hidden_user_id       INT4                 not null
);

/*==============================================================*/
/* Index: v1_hide_profile_unq                                   */
/*==============================================================*/
create unique index v1_hide_profile_unq on v1_hide_profile (
user_id,
hidden_user_id
);

/*==============================================================*/
/* Table: v1_ignore_history                                     */
/*==============================================================*/
create table v1_ignore_history (
   user_id              INT4                 not null,
   ignored_user_id      INT4                 not null,
   exclude_date         TIMESTAMP            not null
);

/*==============================================================*/
/* Index: v1_ignore_history_unq                                 */
/*==============================================================*/
create unique index v1_ignore_history_unq on v1_ignore_history (
user_id,
ignored_user_id
);

/*==============================================================*/
/* Table: v1_ignored_forums                                     */
/*==============================================================*/
create table v1_ignored_forums (
   forum_id             INT4                 not null,
   user_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_ignored_forums_unq                                 */
/*==============================================================*/
create unique index v1_ignored_forums_unq on v1_ignored_forums (
forum_id,
user_id
);

/*==============================================================*/
/* Index: v1_ignored_forums_user_id_idx                         */
/*==============================================================*/
create  index v1_ignored_forums_user_id_idx on v1_ignored_forums (
user_id
);

/*==============================================================*/
/* Index: v1_ignored_forums_forum_id_idx                        */
/*==============================================================*/
create  index v1_ignored_forums_forum_id_idx on v1_ignored_forums (
forum_id
);

/*==============================================================*/
/* Table: v1_ignored_guests                                     */
/*==============================================================*/
create table v1_ignored_guests (
   user_id              INT4                 not null,
   guest_name           VARCHAR(255)         not null,
   whitelist            INT2                 not null default 0
);

/*==============================================================*/
/* Index: v1_ignored_guests_unq                                 */
/*==============================================================*/
create unique index v1_ignored_guests_unq on v1_ignored_guests (
guest_name,
user_id,
whitelist
);

/*==============================================================*/
/* Index: v1_ignored_guests_name_idx                            */
/*==============================================================*/
create  index v1_ignored_guests_name_idx on v1_ignored_guests (
guest_name
);

/*==============================================================*/
/* Index: v1_ignored_guests_user_id_idx                         */
/*==============================================================*/
create  index v1_ignored_guests_user_id_idx on v1_ignored_guests (
user_id
);

/*==============================================================*/
/* Table: v1_ignored_topics                                     */
/*==============================================================*/
create table v1_ignored_topics (
   topic_id             INT4                 not null,
   user_id              INT4                 not null,
   auto_ignored         INT2                 not null default 0
);

/*==============================================================*/
/* Index: v1_ignored_topic_unq                                  */
/*==============================================================*/
create unique index v1_ignored_topic_unq on v1_ignored_topics (
topic_id,
user_id
);

/*==============================================================*/
/* Index: v1_ignored_topic_topic_id_idx                         */
/*==============================================================*/
create  index v1_ignored_topic_topic_id_idx on v1_ignored_topics (
topic_id
);

/*==============================================================*/
/* Index: v1_ignored_topic_user_id_idx                          */
/*==============================================================*/
create  index v1_ignored_topic_user_id_idx on v1_ignored_topics (
user_id
);

/*==============================================================*/
/* Table: v1_ignored_topics_archive                             */
/*==============================================================*/
create table v1_ignored_topics_archive (
   topic_id             INT4                 not null,
   user_id              INT4                 not null,
   auto_ignored         INT2                 not null default 0
);

/*==============================================================*/
/* Index: v1_ignored_topics_archive_unq                         */
/*==============================================================*/
create unique index v1_ignored_topics_archive_unq on v1_ignored_topics_archive (
topic_id,
user_id
);

/*==============================================================*/
/* Table: v1_ignored_users                                      */
/*==============================================================*/
create table v1_ignored_users (
   user_id              INT4                 not null,
   ignored_user_id      INT4                 not null,
   note                 TEXT                 null
);

/*==============================================================*/
/* Index: v1_ignored_users_unq                                  */
/*==============================================================*/
create unique index v1_ignored_users_unq on v1_ignored_users (
user_id,
ignored_user_id
);

/*==============================================================*/
/* Index: v1_ignored_users_user_id_idx                          */
/*==============================================================*/
create  index v1_ignored_users_user_id_idx on v1_ignored_users (
user_id
);

/*==============================================================*/
/* Index: v1_ign_users_ign_user_id_idx                          */
/*==============================================================*/
create  index v1_ign_users_ign_user_id_idx on v1_ignored_users (
ignored_user_id
);

/*==============================================================*/
/* Table: v1_ip_blocked                                         */
/*==============================================================*/
create table v1_ip_blocked (
   ip                   VARCHAR(250)         not null,
   block_expires        TIMESTAMP            null,
   tp                   VARCHAR(10)          not null default 'IP',
   block_reason         TEXT                 null
);

/*==============================================================*/
/* Index: v1_ip_blocked_unq                                     */
/*==============================================================*/
create unique index v1_ip_blocked_unq on v1_ip_blocked (
ip
);

/*==============================================================*/
/* Table: v1_ip_daily_statistics                                */
/*==============================================================*/
create table v1_ip_daily_statistics (
   dt                   DATE                 not null,
   ip                   VARCHAR(250)         null,
   country_code         VARCHAR(10)          null,
   country              VARCHAR(250)         null,
   city                 VARCHAR(250)         null,
   bot                  VARCHAR(250)         null,
   is_tor               INT2                 not null default 0,
   is_proxy             INT2                 not null default 0,
   is_ipv6              INT2                 not null default 0,
   read_marker          VARCHAR(255)         null,
   hits_count           INT4                 not null default 0
);

/*==============================================================*/
/* Index: v1_ip_daily_statistics_dt_idx                         */
/*==============================================================*/
create  index v1_ip_daily_statistics_dt_idx on v1_ip_daily_statistics (
dt
);

/*==============================================================*/
/* Index: v1_ip_daily_statistics_ip_idx                         */
/*==============================================================*/
create  index v1_ip_daily_statistics_ip_idx on v1_ip_daily_statistics (
ip
);

/*==============================================================*/
/* Index: v1_ip_dstatistics_country_idx                         */
/*==============================================================*/
create  index v1_ip_dstatistics_country_idx on v1_ip_daily_statistics (
country
);

/*==============================================================*/
/* Index: v1_ip_daily_statistics_city_idx                       */
/*==============================================================*/
create  index v1_ip_daily_statistics_city_idx on v1_ip_daily_statistics (
city
);

/*==============================================================*/
/* Table: v1_ip_white_list                                      */
/*==============================================================*/
create table v1_ip_white_list (
   ip                   VARCHAR(250)         not null
);

/*==============================================================*/
/* Table: v1_load_statistics                                    */
/*==============================================================*/
create table v1_load_statistics (
   dt                   TIMESTAMP            not null,
   url                  VARCHAR(255)         not null,
   user_name            VARCHAR(255)         null,
   user_id              INT4                 null,
   ip                   VARCHAR(255)         null,
   exec_time            INT4                 not null,
   forum_rm_count       INT4                 not null default 0,
   topic_rm_count       INT4                 not null default 0,
   total_forum_rm_count INT4                 not null default 0,
   total_topic_rm_count INT4                 not null default 0
);

/*==============================================================*/
/* Index: v1_load_statistics_dt_idx                             */
/*==============================================================*/
create  index v1_load_statistics_dt_idx on v1_load_statistics (
dt
);

/*==============================================================*/
/* Table: v1_moderator_log                                      */
/*==============================================================*/
create table v1_moderator_log (
   id                   SERIAL not null,
   event_time           TIMESTAMP            not null,
   moderator_name       VARCHAR(255)         null,
   moderator_id         INT4                 null,
   action               VARCHAR(255)         null,
   action_expires       TIMESTAMP            null,
   author_name          VARCHAR(255)         null,
   author_id            INT4                 null,
   ip                   VARCHAR(250)         null,
   post_id              INT4                 null,
   topic_name           VARCHAR(1000)        null,
   topic_id             INT4                 null,
   forum_name           VARCHAR(255)         null,
   forum_id             INT4                 null,
   note                 TEXT                 null,
   redundant            INT2                 not null default 0,
   source_topic_name    VARCHAR(1000)        null,
   source_topic_id      INT4                 null,
   constraint PK_V1_MODERATOR_LOG primary key (id)
);

/*==============================================================*/
/* Index: v1_moderator_log_mod_name_idx                         */
/*==============================================================*/
create  index v1_moderator_log_mod_name_idx on v1_moderator_log (
moderator_name
);

/*==============================================================*/
/* Index: v1_moderator_log_action_idx                           */
/*==============================================================*/
create  index v1_moderator_log_action_idx on v1_moderator_log (
action
);

/*==============================================================*/
/* Index: v1_moderator_log_forum_id_idx                         */
/*==============================================================*/
create  index v1_moderator_log_forum_id_idx on v1_moderator_log (
forum_id
);

/*==============================================================*/
/* Index: v1_moder_log_author_name_idx                          */
/*==============================================================*/
create  index v1_moder_log_author_name_idx on v1_moderator_log (
author_name
);

/*==============================================================*/
/* Index: v1_moderator_log_evt_tm_idx                           */
/*==============================================================*/
create  index v1_moderator_log_evt_tm_idx on v1_moderator_log (
event_time
);

/*==============================================================*/
/* Index: v1_moderator_log_redundant_idx                        */
/*==============================================================*/
create  index v1_moderator_log_redundant_idx on v1_moderator_log (
redundant
);

/*==============================================================*/
/* Index: v1_moderator_log_topic_id_idx                         */
/*==============================================================*/
create  index v1_moderator_log_topic_id_idx on v1_moderator_log (
topic_id
);

/*==============================================================*/
/* Index: v1_moder_log_src_topic_id_idx                         */
/*==============================================================*/
create  index v1_moder_log_src_topic_id_idx on v1_moderator_log (
source_topic_id
);

/*==============================================================*/
/* Table: v1_morphology_dictionary                              */
/*==============================================================*/
create table v1_morphology_dictionary (
   root                 VARCHAR(255)         not null,
   word                 VARCHAR(255)         not null
);

/*==============================================================*/
/* Index: v1_morp_dictionary_root_idx                           */
/*==============================================================*/
create  index v1_morp_dictionary_root_idx on v1_morphology_dictionary (
root
);

/*==============================================================*/
/* Index: v1_morp_dictionary_word_idx                           */
/*==============================================================*/
create  index v1_morp_dictionary_word_idx on v1_morphology_dictionary (
word
);

/*==============================================================*/
/* Table: v1_pinned_topics                                      */
/*==============================================================*/
create table v1_pinned_topics (
   topic_id             INT4                 not null,
   user_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_pinned_topics_unq                                  */
/*==============================================================*/
create unique index v1_pinned_topics_unq on v1_pinned_topics (
topic_id,
user_id
);

/*==============================================================*/
/* Index: v1_pinned_topics_topic_id_idx                         */
/*==============================================================*/
create  index v1_pinned_topics_topic_id_idx on v1_pinned_topics (
topic_id
);

/*==============================================================*/
/* Index: v1_pinned_topics_user_id_idx                          */
/*==============================================================*/
create  index v1_pinned_topics_user_id_idx on v1_pinned_topics (
user_id
);

/*==============================================================*/
/* Table: v1_poll_options                                       */
/*==============================================================*/
create table v1_poll_options (
   id                   SERIAL not null,
   name                 VARCHAR(700)         not null,
   topic_id             INT4                 not null,
   constraint PK_V1_POLL_OPTIONS primary key (id)
);

/*==============================================================*/
/* Index: v1_poll_options_unq                                   */
/*==============================================================*/
create unique index v1_poll_options_unq on v1_poll_options (
name,
topic_id
);

/*==============================================================*/
/* Index: v1_poll_options_topic_id_idx                          */
/*==============================================================*/
create  index v1_poll_options_topic_id_idx on v1_poll_options (
topic_id
);

/*==============================================================*/
/* Table: v1_poll_user_answers                                  */
/*==============================================================*/
create table v1_poll_user_answers (
   tm                   TIMESTAMP            null,
   user_id              INT4                 not null,
   option_id            INT4                 not null
);

/*==============================================================*/
/* Index: v1_poll_user_answers_unq                              */
/*==============================================================*/
create unique index v1_poll_user_answers_unq on v1_poll_user_answers (
user_id,
option_id
);

/*==============================================================*/
/* Index: v1_poll_uanswers_user_id_idx                          */
/*==============================================================*/
create  index v1_poll_uanswers_user_id_idx on v1_poll_user_answers (
user_id
);

/*==============================================================*/
/* Index: v1_poll_uanswers_option_id_idx                        */
/*==============================================================*/
create  index v1_poll_uanswers_option_id_idx on v1_poll_user_answers (
option_id
);

/*==============================================================*/
/* Table: v1_post                                               */
/*==============================================================*/
create table v1_post (
   id                   SERIAL not null,
   user_id              INT4                 null,
   author               VARCHAR(255)         not null,
   creation_date        TIMESTAMP            not null,
   pinned               INT2                 not null default 0,
   deleted              INT2                 not null default 0,
   text_content         TEXT                 null,
   html_content         TEXT                 null,
   searchable_content   TEXT                 null,
   has_picture          INT2                 not null default 0,
   has_audio            INT2                 not null default 0,
   has_telegram         INT2                 not null default 0,
   has_video            INT2                 not null default 0,
   has_link             INT2                 not null default 0,
   has_code             INT2                 not null default 0,
   has_attachment       INT2                 not null default 0,
   has_attachment_ref   INT2                 not null default 0,
   read_marker          VARCHAR(255)         null,
   ip                   VARCHAR(250)         null,
   last_updated_by      VARCHAR(255)         null,
   last_updated         TIMESTAMP            null,
   self_edited          INT2                 not null default 0,
   last_warned_by       VARCHAR(255)         null,
   last_warning         TEXT                 null,
   bb_parser_version    INT4                 not null default 1,
   topic_id             INT4                 not null,
   user_marker          VARCHAR(255)         null,
   user_agent           VARCHAR(700)         null,
   is_comment           INT2                 not null default 0,
   is_adult             INT2                 not null default 0,
   is_system            INT2                 not null default 0,
   ref                  INT4                 null,
   constraint PK_V1_POST primary key (id)
);

ALTER TABLE v1_post 
ADD COLUMN search_vector tsvector 
    GENERATED ALWAYS AS 
    (to_tsvector('russian', searchable_content) || 
     to_tsvector('english', searchable_content) || 
     to_tsvector('german', searchable_content)) STORED;


CREATE INDEX v1_post_ftx ON v1_post USING GIN (search_vector);

/*==============================================================*/
/* Index: v1_post_is_deleted_idx                                */
/*==============================================================*/
create  index v1_post_is_deleted_idx on v1_post (
deleted
);

/*==============================================================*/
/* Index: v1_post_user_id_idx                                   */
/*==============================================================*/
create  index v1_post_user_id_idx on v1_post (
user_id
);

/*==============================================================*/
/* Index: v1_post_topic_id_idx                                  */
/*==============================================================*/
create  index v1_post_topic_id_idx on v1_post (
topic_id
);

/*==============================================================*/
/* Index: v1_post_has_attachment_idx                            */
/*==============================================================*/
create  index v1_post_has_attachment_idx on v1_post (
has_attachment
);

/*==============================================================*/
/* Index: v1_post_has_attachment_ref_idx                        */
/*==============================================================*/
create  index v1_post_has_attachment_ref_idx on v1_post (
has_attachment_ref
);

/*==============================================================*/
/* Index: v1_post_ip_idx                                        */
/*==============================================================*/
create  index v1_post_ip_idx on v1_post (
ip
);

/*==============================================================*/
/* Index: v1_post_rm_idx                                        */
/*==============================================================*/
create  index v1_post_rm_idx on v1_post (
read_marker
);

/*==============================================================*/
/* Index: v1_post_author_idx                                    */
/*==============================================================*/
create  index v1_post_author_idx on v1_post (
author
);

/*==============================================================*/
/* Index: v1_post_creation_date_idx                             */
/*==============================================================*/
create  index v1_post_creation_date_idx on v1_post (
creation_date
);

/*==============================================================*/
/* Index: v1_post_is_pinned_idx                                 */
/*==============================================================*/
create  index v1_post_is_pinned_idx on v1_post (
pinned
);

/*==============================================================*/
/* Index: v1_post_user_marker_idx                               */
/*==============================================================*/
create  index v1_post_user_marker_idx on v1_post (
user_marker
);

/*==============================================================*/
/* Index: v1_post_has_telegram_idx                              */
/*==============================================================*/
create  index v1_post_has_telegram_idx on v1_post (
has_telegram
);

/*==============================================================*/
/* Index: v1_post_has_video_idx                                 */
/*==============================================================*/
create  index v1_post_has_video_idx on v1_post (
has_video
);

/*==============================================================*/
/* Index: v1_post_has_audio_idx                                 */
/*==============================================================*/
create  index v1_post_has_audio_idx on v1_post (
has_audio
);

/*==============================================================*/
/* Index: v1_post_has_link_idx                                  */
/*==============================================================*/
create  index v1_post_has_link_idx on v1_post (
has_link
);

/*==============================================================*/
/* Index: v1_post_has_code_idx                                  */
/*==============================================================*/
create  index v1_post_has_code_idx on v1_post (
has_code
);

/*==============================================================*/
/* Index: v1_post_is_comment_idx                                */
/*==============================================================*/
create  index v1_post_is_comment_idx on v1_post (
is_comment
);

/*==============================================================*/
/* Index: v1_post_is_adult_idx                                  */
/*==============================================================*/
create  index v1_post_is_adult_idx on v1_post (
is_adult
);

/*==============================================================*/
/* Index: v1_post_has_picture_idx                               */
/*==============================================================*/
create  index v1_post_has_picture_idx on v1_post (
has_picture
);

/*==============================================================*/
/* Index: v1_post_ref_idx                                       */
/*==============================================================*/
create  index v1_post_ref_idx on v1_post (
ref
);

/*==============================================================*/
/* Table: v1_post_hierarchy                                     */
/*==============================================================*/
create table v1_post_hierarchy (
   parent_post_id       INT4                 not null,
   reply_post_id        INT4                 not null
);

/*==============================================================*/
/* Index: v1_post_hierarchy_unq                                 */
/*==============================================================*/
create unique index v1_post_hierarchy_unq on v1_post_hierarchy (
parent_post_id,
reply_post_id
);

/*==============================================================*/
/* Index: v1_post_hierarchy_ppost_id                            */
/*==============================================================*/
create  index v1_post_hierarchy_ppost_id on v1_post_hierarchy (
parent_post_id
);

/*==============================================================*/
/* Index: v1_post_hierarchy_rpost_id                            */
/*==============================================================*/
create  index v1_post_hierarchy_rpost_id on v1_post_hierarchy (
reply_post_id
);

/*==============================================================*/
/* Table: v1_post_history                                       */
/*==============================================================*/
create table v1_post_history (
   id                   SERIAL not null,
   dt                   TIMESTAMP            not null,
   author               VARCHAR(255)         null,
   self_edited          INT2                 not null default 0,
   text_content         TEXT                 null,
   html_content         TEXT                 null,
   post_id              INT4                 not null,
   constraint PK_V1_POST_HISTORY primary key (id)
);

/*==============================================================*/
/* Index: v1_post_history_post_id_idx                           */
/*==============================================================*/
create  index v1_post_history_post_id_idx on v1_post_history (
post_id
);

/*==============================================================*/
/* Table: v1_post_rating                                        */
/*==============================================================*/
create table v1_post_rating (
   id                   SERIAL not null,
   rating               INT4                 not null,
   dt                   TIMESTAMP            null,
   post_id              INT4                 not null,
   user_id              INT4                 not null,
   rater_ignored        INT2                 not null default 0,
   constraint PK_V1_POST_RATING primary key (id)
);

/*==============================================================*/
/* Index: v1_post_rating_unq                                    */
/*==============================================================*/
create unique index v1_post_rating_unq on v1_post_rating (
post_id,
user_id
);

/*==============================================================*/
/* Index: v1_post_rating_post_idx                               */
/*==============================================================*/
create  index v1_post_rating_post_idx on v1_post_rating (
post_id
);

/*==============================================================*/
/* Index: v1_post_rating_user_idx                               */
/*==============================================================*/
create  index v1_post_rating_user_idx on v1_post_rating (
user_id
);

/*==============================================================*/
/* Index: v1_post_rating_raiting_idx                            */
/*==============================================================*/
create  index v1_post_rating_raiting_idx on v1_post_rating (
rating
);

/*==============================================================*/
/* Table: v1_post_statistics                                    */
/*==============================================================*/
create table v1_post_statistics (
   post_id              INT4                 not null,
   like_count           INT8                 not null default 0,
   dislike_count        INT8                 not null default 0
);

/*==============================================================*/
/* Index: v1_post_statistics_unq                                */
/*==============================================================*/
create unique index v1_post_statistics_unq on v1_post_statistics (
post_id
);

/*==============================================================*/
/* Table: v1_post_subscription                                  */
/*==============================================================*/
create table v1_post_subscription (
   post_id              INT4                 not null,
   user_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_post_subscription_unq                              */
/*==============================================================*/
create unique index v1_post_subscription_unq on v1_post_subscription (
post_id,
user_id
);

/*==============================================================*/
/* Index: v1_post_subscr_post_id_idx                            */
/*==============================================================*/
create  index v1_post_subscr_post_id_idx on v1_post_subscription (
post_id
);

/*==============================================================*/
/* Index: v1_post_subscr_user_id_idx                            */
/*==============================================================*/
create  index v1_post_subscr_user_id_idx on v1_post_subscription (
user_id
);

/*==============================================================*/
/* Table: v1_private_topics                                     */
/*==============================================================*/
create table v1_private_topics (
   last_visit_date      TIMESTAMP            null,
   topic_id             INT4                 not null,
   participant_id       INT4                 not null
);

/*==============================================================*/
/* Index: v1_private_topics_unq                                 */
/*==============================================================*/
create unique index v1_private_topics_unq on v1_private_topics (
topic_id,
participant_id
);

/*==============================================================*/
/* Index: v1_private_topics_topic_id_idx                        */
/*==============================================================*/
create  index v1_private_topics_topic_id_idx on v1_private_topics (
topic_id
);

/*==============================================================*/
/* Index: v1_private_tp_participt_id_idx                        */
/*==============================================================*/
create  index v1_private_tp_participt_id_idx on v1_private_topics (
participant_id
);

/*==============================================================*/
/* Table: v1_protected_guests                                   */
/*==============================================================*/
create table v1_protected_guests (
   guest_name           VARCHAR(255)         not null,
   guest_name_hash      VARCHAR(255)         null
);

/*==============================================================*/
/* Index: v1_protected_guests_unq                               */
/*==============================================================*/
create unique index v1_protected_guests_unq on v1_protected_guests (
guest_name
);

/*==============================================================*/
/* Index: v1_protected_guests_hash_idx                          */
/*==============================================================*/
create  index v1_protected_guests_hash_idx on v1_protected_guests (
guest_name_hash
);

/*==============================================================*/
/* Table: v1_read_marker_activity                               */
/*==============================================================*/
create table v1_read_marker_activity (
   read_marker          VARCHAR(255)         not null,
   last_activity        TIMESTAMP            null,
   first_activity       TIMESTAMP            null,
   ip                   VARCHAR(250)         null,
   author               VARCHAR(255)         null,
   user_agent           VARCHAR(500)         null,
   hits                 INT4                 not null default 0,
   current_name_start   TIMESTAMP            null,
   current_name_hits    INT4                 not null default 0
);

/*==============================================================*/
/* Index: v1_read_marker_activity_unq                           */
/*==============================================================*/
create unique index v1_read_marker_activity_unq on v1_read_marker_activity (
read_marker
);

/*==============================================================*/
/* Table: v1_reserved_names                                     */
/*==============================================================*/
create table v1_reserved_names (
   user_name            VARCHAR(255)         not null,
   user_name_hash       VARCHAR(255)         null
);

/*==============================================================*/
/* Index: v1_reserved_names_unq                                 */
/*==============================================================*/
create unique index v1_reserved_names_unq on v1_reserved_names (
user_name
);

/*==============================================================*/
/* Table: v1_settings                                           */
/*==============================================================*/
create table v1_settings (
   moderator_log        VARCHAR(100)         null,
   default_sender       VARCHAR(255)         null,
   receiver             VARCHAR(255)         null,
   max_att_size         INT4                 null,
   max_att_size_audiovideo INT4                 null,
   max_messages_minute  INT4                 null,
   max_messages_hour    INT4                 null,
   max_messages_day     INT4                 null,
   min_search_interval  INT4                 null,
   whois_server         VARCHAR(500)         null,
   hide_online_status   INT2                 not null default 0,
   approval_required    INT2                 not null default 0,
   delayed_reg_mailing  INT2                 not null default 0,
   max_rates_hour       INT4                 null,
   max_topics_day       INT4                 null,
   rates_active         INT2                 not null default 0,
   dislikes_active      INT2                 not null default 0,
   dislikes_anonym      INT2                 not null default 0,
   skin                 VARCHAR(255)         null,
   max_poll_options     INT4                 null,
   max_user_name_symbols INT4                 null,
   max_topic_name_symbols INT4                 null,
   max_message_length   INT4                 null,
   max_pinned_topics    INT4                 null,
   max_private_members  INT4                 null,
   block_tor_ips        INT2                 not null default 0,
   celebration_active   INT2                 not null default 0,
   mourning_active      INT2                 not null default 0,
   snow_effect          INT2                 not null default 0,
   hide_users_from_robots INT2                 not null default 0,
   archive_mode         INT2                 not null default 0,
   request_cookie_consent INT2                 not null default 0,
   hash_ip_addresses    INT2                 not null default 0
);

/*==============================================================*/
/* Table: v1_topic                                              */
/*==============================================================*/
create table v1_topic (
   id                   SERIAL not null,
   name                 VARCHAR(700)         not null,
   author               VARCHAR(255)         not null,
   creation_date        TIMESTAMP            not null,
   deleted              INT2                 not null default 0,
   closed               INT2                 not null default 0,
   pinned               INT2                 not null default 0,
   read_marker          VARCHAR(255)         null,
   has_pinned_post      INT2                 not null default 0,
   merged               INT4                 null,
   is_poll              INT2                 not null default 0,
   poll_comment         TEXT                 null,
   poll_results_delayed INT2                 not null default 0,
   no_guests            INT2                 not null default 0,
   forum_id             INT4                 not null,
   user_id              INT4                 null,
   user_marker          VARCHAR(255)         null,
   is_private           INT2                 not null default 0,
   publish_delay        INT2                 not null default 0,
   profiled_topic       INT2                 not null default 0,
   request_moderation   INT2                 not null default 0,
   ref                  INT4                 null,
   constraint PK_V1_TOPIC primary key (id)
);

ALTER TABLE v1_topic 
ADD COLUMN search_vector tsvector 
    GENERATED ALWAYS AS 
    (to_tsvector('russian', name) || 
     to_tsvector('english', name) || 
     to_tsvector('german', name)) STORED;


CREATE INDEX v1_topic_ftx ON v1_topic USING GIN (search_vector);

/*==============================================================*/
/* Index: v1_topic_name_idx                                     */
/*==============================================================*/
create  index v1_topic_name_idx on v1_topic (
name
);

/*==============================================================*/
/* Index: v1_topic_user_id_idx                                  */
/*==============================================================*/
create  index v1_topic_user_id_idx on v1_topic (
user_id
);

/*==============================================================*/
/* Index: v1_topic_forum_id_idx                                 */
/*==============================================================*/
create  index v1_topic_forum_id_idx on v1_topic (
forum_id
);

/*==============================================================*/
/* Index: v1_topic_is_deleted_idx                               */
/*==============================================================*/
create  index v1_topic_is_deleted_idx on v1_topic (
deleted
);

/*==============================================================*/
/* Index: v1_topic_rm_idx                                       */
/*==============================================================*/
create  index v1_topic_rm_idx on v1_topic (
read_marker
);

/*==============================================================*/
/* Index: v1_topic_is_pinned_idx                                */
/*==============================================================*/
create  index v1_topic_is_pinned_idx on v1_topic (
pinned
);

/*==============================================================*/
/* Index: v1_topic_is_private_idx                               */
/*==============================================================*/
create  index v1_topic_is_private_idx on v1_topic (
is_private
);

/*==============================================================*/
/* Index: v1_topic_publish_delay_idx                            */
/*==============================================================*/
create  index v1_topic_publish_delay_idx on v1_topic (
publish_delay
);

/*==============================================================*/
/* Index: v1_topic_author_idx                                   */
/*==============================================================*/
create  index v1_topic_author_idx on v1_topic (
author
);

/*==============================================================*/
/* Index: v1_topic_creation_date_idx                            */
/*==============================================================*/
create  index v1_topic_creation_date_idx on v1_topic (
creation_date
);

/*==============================================================*/
/* Index: v1_topic_ref_idx                                      */
/*==============================================================*/
create  index v1_topic_ref_idx on v1_topic (
ref
);

/*==============================================================*/
/* Table: v1_topic_blocked                                      */
/*==============================================================*/
create table v1_topic_blocked (
   topic_id             INT4                 not null,
   user_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_topic_blocked_unq                                  */
/*==============================================================*/
create unique index v1_topic_blocked_unq on v1_topic_blocked (
topic_id,
user_id
);

/*==============================================================*/
/* Index: v1_topic_blocked_topic_id_idx                         */
/*==============================================================*/
create  index v1_topic_blocked_topic_id_idx on v1_topic_blocked (
topic_id
);

/*==============================================================*/
/* Index: v1_topic_blocked_user_id_idx                          */
/*==============================================================*/
create  index v1_topic_blocked_user_id_idx on v1_topic_blocked (
user_id
);

/*==============================================================*/
/* Table: v1_topic_moderator                                    */
/*==============================================================*/
create table v1_topic_moderator (
   topic_id             INT4                 not null,
   user_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_topic_moderator_unq                                */
/*==============================================================*/
create unique index v1_topic_moderator_unq on v1_topic_moderator (
topic_id,
user_id
);

/*==============================================================*/
/* Index: v1_topic_moderator_topic_id_idx                       */
/*==============================================================*/
create  index v1_topic_moderator_topic_id_idx on v1_topic_moderator (
topic_id
);

/*==============================================================*/
/* Index: v1_topic_moderator_user_id_idx                        */
/*==============================================================*/
create  index v1_topic_moderator_user_id_idx on v1_topic_moderator (
user_id
);

/*==============================================================*/
/* Table: v1_topic_participants                                 */
/*==============================================================*/
create table v1_topic_participants (
   user_id              INT4                 not null,
   topic_id             INT4                 not null
);

/*==============================================================*/
/* Index: v1_topic_participants_unq                             */
/*==============================================================*/
create unique index v1_topic_participants_unq on v1_topic_participants (
user_id,
topic_id
);

/*==============================================================*/
/* Table: v1_topic_read_markers                                 */
/*==============================================================*/
create table v1_topic_read_markers (
   topic_id             INT4                 not null,
   read_marker          VARCHAR(255)         not null,
   last_read_date       TIMESTAMP            null,
   ip                   VARCHAR(250)         null
);

/*==============================================================*/
/* Index: v1_topic_read_markers_tprm_idx                        */
/*==============================================================*/
create unique index v1_topic_read_markers_tprm_idx on v1_topic_read_markers (
topic_id,
read_marker
);

/*==============================================================*/
/* Index: v1_topic_read_markers_rm_idx                          */
/*==============================================================*/
create  index v1_topic_read_markers_rm_idx on v1_topic_read_markers (
read_marker
);

/*==============================================================*/
/* Index: v1_topic_read_markers_lrdt_idx                        */
/*==============================================================*/
create  index v1_topic_read_markers_lrdt_idx on v1_topic_read_markers (
last_read_date
);

/*==============================================================*/
/* Table: v1_topic_statistics                                   */
/*==============================================================*/
create table v1_topic_statistics (
   topic_id             INT4                 not null,
   post_count           INT4                 not null default 0,
   post_count_total     INT4                 not null default 0,
   hits_count           INT4                 not null default 0,
   bot_hits_count       INT4                 not null default 0,
   last_message_date    TIMESTAMP            null,
   last_message_id      INT4                 null
);

/*==============================================================*/
/* Index: v1_topic_statistics_unq                               */
/*==============================================================*/
create unique index v1_topic_statistics_unq on v1_topic_statistics (
topic_id
);

/*==============================================================*/
/* Index: v1_topic_statistics_lmid_idx                          */
/*==============================================================*/
create  index v1_topic_statistics_lmid_idx on v1_topic_statistics (
last_message_id
);

/*==============================================================*/
/* Index: v1_topic_statistics_lmdate_idx                        */
/*==============================================================*/
create  index v1_topic_statistics_lmdate_idx on v1_topic_statistics (
last_message_date
);

/*==============================================================*/
/* Table: v1_topic_subscription                                 */
/*==============================================================*/
create table v1_topic_subscription (
   topic_id             INT4                 not null,
   user_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_topic_subscription_unq                             */
/*==============================================================*/
create unique index v1_topic_subscription_unq on v1_topic_subscription (
topic_id,
user_id
);

/*==============================================================*/
/* Index: v1_topic_subscr_topic_id_idx                          */
/*==============================================================*/
create  index v1_topic_subscr_topic_id_idx on v1_topic_subscription (
topic_id
);

/*==============================================================*/
/* Index: v1_topic_subscr_user_id_idx                           */
/*==============================================================*/
create  index v1_topic_subscr_user_id_idx on v1_topic_subscription (
user_id
);

/*==============================================================*/
/* Table: v1_topic_view_history                                 */
/*==============================================================*/
create table v1_topic_view_history (
   user_id              INT4                 not null,
   guest_name           VARCHAR(255)         not null,
   topic_id             INT4                 not null,
   dt                   TIMESTAMP            not null,
   ip                   VARCHAR(250)         null
);

/*==============================================================*/
/* Index: v1_topic_view_history_unq                             */
/*==============================================================*/
create unique index v1_topic_view_history_unq on v1_topic_view_history (
user_id,
topic_id,
guest_name
);

/*==============================================================*/
/* Index: v1_topic_view_history_user_idx                        */
/*==============================================================*/
create  index v1_topic_view_history_user_idx on v1_topic_view_history (
user_id
);

/*==============================================================*/
/* Index: v1_topic_view_history_dt_idx                          */
/*==============================================================*/
create  index v1_topic_view_history_dt_idx on v1_topic_view_history (
dt
);

/*==============================================================*/
/* Index: v1_topic_vhist_guest_name_idx                         */
/*==============================================================*/
create  index v1_topic_vhist_guest_name_idx on v1_topic_view_history (
guest_name
);

/*==============================================================*/
/* Table: v1_tor_ips                                            */
/*==============================================================*/
create table v1_tor_ips (
   ip                   VARCHAR(250)         not null,
   block_level          INT4                 not null default 0,
   refresh_date         TIMESTAMP            null
);

/*==============================================================*/
/* Index: v1_tor_ips_unq                                        */
/*==============================================================*/
create unique index v1_tor_ips_unq on v1_tor_ips (
ip
);

/*==============================================================*/
/* Table: v1_user                                               */
/*==============================================================*/
create table v1_user (
   id                   SERIAL not null,
   login                VARCHAR(255)         not null,
   password_hash        VARCHAR(255)         not null,
   user_name            VARCHAR(255)         not null,
   user_name_hash       VARCHAR(255)         null,
   email                VARCHAR(255)         not null,
   email_hash           VARCHAR(255)         not null,
   hide_email           INT2                 not null default 1,
   registration_date    TIMESTAMP            not null,
   last_visit_date      TIMESTAMP            null,
   last_post_date       TIMESTAMP            null,
   is_admin             INT2                 not null default 0,
   message              VARCHAR(500)         null,
   info                 TEXT                 null,
   homepage             VARCHAR(500)         null,
   signature            TEXT                 null,
   api_active           INT2                 not null default 0,
   api_token            VARCHAR(100)         null,
   pwd_reset_hash       VARCHAR(255)         null,
   pwd_reset_expire     TIMESTAMP            null,
   activation_hash      VARCHAR(100)         null,
   activation_expire    TIMESTAMP            null,
   activated            INT2                 not null default 0,
   died                 INT2                 not null default 0,
   autologin_hash       VARCHAR(255)         null,
   blocked              INT2                 not null default 0,
   self_blocked         INT2                 not null default 0,
   block_expires        TIMESTAMP            null,
   block_reason         TEXT                 null,
   hide_user_avatars    INT2                 not null default 0,
   hide_user_info       INT2                 not null default 0,
   hide_pictures        INT2                 not null default 0,
   donot_hide_adult_pictures INT2                 not null default 0,
   hide_ignored         INT2                 not null default 0,
   location             VARCHAR(255)         null,
   hidden               INT2                 not null default 0,
   send_notifications   INT2                 not null default 0,
   donot_notify_on_rates INT2                 not null default 0,
   no_private_messages  INT2                 not null default 0,
   turnoff_personal_appeals INT2                 not null default 0,
   turnoff_events       INT2                 not null default 0,
   approved             INT2                 not null default 0,
   read_marker          VARCHAR(255)         not null,
   ip                   VARCHAR(250)         null,
   last_ip              VARCHAR(250)         null,
   ignore_guests_blacklist INT2                 not null default 0,
   ignore_guests_whitelist INT2                 not null default 0,
   ignore_new_guests    INT2                 not null default 0,
   no_video_expand      INT2                 not null default 0,
   logout               INT2                 not null default 0,
   last_logout_date     TIMESTAMP            null,
   last_events_view_date TIMESTAMP            null,
   video_audio_blocked  INT2                 not null default 0,
   attachments_blocked  INT2                 not null default 0,
   rating_blocked       INT2                 not null default 0,
   time_zone            VARCHAR(255)         null,
   privileged           INT2                 not null default 0,
   privileged_topic_moderator INT2                 not null default 0,
   notify_about_new_users INT2                 not null default 0,
   notify_citation      INT2                 not null default 0,
   skin                 VARCHAR(255)         null,
   skin_properties      TEXT                 null,
   interface_language   VARCHAR(50)          null,
   global_ban_allowed   INT2                 not null default 0,
   show_ip              INT2                 not null default 0,
   notify_on_words      INT2                 not null default 0,
   words_to_notify      TEXT                 null,
   last_host            VARCHAR(255)         null,
   custom_css           TEXT                 null,
   custom_smiles        TEXT                 null,
   ref                  INT4                 null,
   email_changed        INT2                 not null default 0,
   constraint PK_V1_USER primary key (id)
);

/*==============================================================*/
/* Index: v1_user_login_unq                                     */
/*==============================================================*/
create unique index v1_user_login_unq on v1_user (
login
);

/*==============================================================*/
/* Index: v1_user_nickname_unq                                  */
/*==============================================================*/
create unique index v1_user_nickname_unq on v1_user (
user_name
);

/*==============================================================*/
/* Index: v1_user_nickname_hash_idx                             */
/*==============================================================*/
create  index v1_user_nickname_hash_idx on v1_user (
user_name_hash
);

/*==============================================================*/
/* Index: v1_user_email_unq                                     */
/*==============================================================*/
create unique index v1_user_email_unq on v1_user (
email_hash
);

/*==============================================================*/
/* Index: v1_user_autologin_hash_idx                            */
/*==============================================================*/
create  index v1_user_autologin_hash_idx on v1_user (
autologin_hash
);

/*==============================================================*/
/* Index: v1_user_pwd_reset_hash_idx                            */
/*==============================================================*/
create  index v1_user_pwd_reset_hash_idx on v1_user (
pwd_reset_hash
);

/*==============================================================*/
/* Index: v1_user_block_expires_idx                             */
/*==============================================================*/
create  index v1_user_block_expires_idx on v1_user (
block_expires
);

/*==============================================================*/
/* Index: v1_user_read_marker_unq                               */
/*==============================================================*/
create unique index v1_user_read_marker_unq on v1_user (
read_marker
);

/*==============================================================*/
/* Table: v1_user_comment                                       */
/*==============================================================*/
create table v1_user_comment (
   user_id              INT4                 not null,
   commented_user_id    INT4                 not null,
   note                 TEXT                 null
);

/*==============================================================*/
/* Index: v1_user_comment_unq                                   */
/*==============================================================*/
create unique index v1_user_comment_unq on v1_user_comment (
user_id,
commented_user_id
);

/*==============================================================*/
/* Table: v1_user_login_tries                                   */
/*==============================================================*/
create table v1_user_login_tries (
   login                VARCHAR(250)         not null,
   ip                   VARCHAR(250)         not null,
   dt                   TIMESTAMP            not null
);

/*==============================================================*/
/* Index: v1_user_login_tries_ip_idx                            */
/*==============================================================*/
create  index v1_user_login_tries_ip_idx on v1_user_login_tries (
ip
);

/*==============================================================*/
/* Table: v1_user_statistics                                    */
/*==============================================================*/
create table v1_user_statistics (
   user_id              INT4                 not null,
   post_count           INT8                 not null default 0,
   like_count           INT8                 not null default 0,
   dislike_count        INT8                 not null default 0,
   topic_count          INT8                 not null default 0,
   time_online          INT8                 not null default 0
);

/*==============================================================*/
/* Index: v1_user_statistics_unq                                */
/*==============================================================*/
create unique index v1_user_statistics_unq on v1_user_statistics (
user_id
);

/*==============================================================*/
/* Table: v1_user_subscription                                  */
/*==============================================================*/
create table v1_user_subscription (
   user_id              INT4                 not null,
   subscribed_user_id   INT4                 not null,
   subscribed_user_name VARCHAR(255)         not null,
   tm                   TIMESTAMP            not null,
   last_view            TIMESTAMP            null
);

/*==============================================================*/
/* Index: v1_usubscription_unq                                  */
/*==============================================================*/
create unique index v1_usubscription_unq on v1_user_subscription (
user_id,
subscribed_user_id,
subscribed_user_name
);

/*==============================================================*/
/* Index: v1_usubscription_usr_idx                              */
/*==============================================================*/
create  index v1_usubscription_usr_idx on v1_user_subscription (
user_id
);

/*==============================================================*/
/* Index: v1_usubscription_usrname_idx                          */
/*==============================================================*/
create  index v1_usubscription_usrname_idx on v1_user_subscription (
subscribed_user_name
);

/*==============================================================*/
/* Index: v1_usubscription_usrid_idx                            */
/*==============================================================*/
create  index v1_usubscription_usrid_idx on v1_user_subscription (
subscribed_user_id
);

/*==============================================================*/
/* Table: v1_user_tag_post                                      */
/*==============================================================*/
create table v1_user_tag_post (
   tag_id               INT4                 not null,
   post_id              INT4                 not null
);

/*==============================================================*/
/* Index: v1_user_tag_post_unq                                  */
/*==============================================================*/
create unique index v1_user_tag_post_unq on v1_user_tag_post (
tag_id,
post_id
);

/*==============================================================*/
/* Index: v1_user_tag_post_tag_id_idx                           */
/*==============================================================*/
create  index v1_user_tag_post_tag_id_idx on v1_user_tag_post (
tag_id
);

/*==============================================================*/
/* Index: v1_user_tag_post_post_id_idx                          */
/*==============================================================*/
create  index v1_user_tag_post_post_id_idx on v1_user_tag_post (
post_id
);

/*==============================================================*/
/* Table: v1_user_tags                                          */
/*==============================================================*/
create table v1_user_tags (
   id                   SERIAL not null,
   name                 VARCHAR(255)         not null,
   user_id              INT4                 not null,
   constraint PK_V1_USER_TAGS primary key (id)
);

/*==============================================================*/
/* Index: v1_user_tags_unq                                      */
/*==============================================================*/
create unique index v1_user_tags_unq on v1_user_tags (
name,
user_id
);

/*==============================================================*/
/* Index: v1_user_tags_user_id_idx                              */
/*==============================================================*/
create  index v1_user_tags_user_id_idx on v1_user_tags (
user_id
);


CREATE OR REPLACE PROCEDURE v1_deep_collect_replies(p_oid INTEGER, p_deep INTEGER)
LANGUAGE plpgsql
AS $$
DECLARE
    affected_cnt INTEGER;
BEGIN
    DELETE FROM tmp_children;
    INSERT INTO tmp_children (id) VALUES (p_oid);

    LOOP
        INSERT INTO tmp_children (id)
        SELECT reply_post_id FROM v1_post_hierarchy
        WHERE parent_post_id IN (SELECT id FROM tmp_children)
        AND reply_post_id NOT IN (SELECT id FROM tmp_children);

        GET DIAGNOSTICS affected_cnt = ROW_COUNT;

        EXIT WHEN affected_cnt = 0 OR p_deep = 0;
    END LOOP;
END;
$$;

