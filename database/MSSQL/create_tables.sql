/*==============================================================*/
/* DBMS name:      Microsoft SQL Server 2005                    */
/* Created on:     28.11.2024 23:27:19                          */
/*==============================================================*/


/*==============================================================*/
/* Table: v1_attachment                                         */
/*==============================================================*/
create table v1_attachment (
   id                   int                  identity,
   post_id              int                  not null,
   nr                   int                  not null,
   name                 nvarchar(700)        null,
   origin_name          nvarchar(700)        null,
   type                 varchar(255)         null,
   deleted              tinyint              not null default 0,
   user_id              int                  null,
   favourite            tinyint              not null default 0,
   last_post_id         int                  not null,
   constraint v1_attachment_pk primary key (id)
)
go

/*==============================================================*/
/* Index: v1_attachment_unq                                     */
/*==============================================================*/
create unique index v1_attachment_unq on v1_attachment (
post_id ASC,
nr ASC
)
go

/*==============================================================*/
/* Index: v1_attachment_name_idx                                */
/*==============================================================*/
create index v1_attachment_name_idx on v1_attachment (
name ASC
)
go

/*==============================================================*/
/* Index: v1_attachment_type_idx                                */
/*==============================================================*/
create index v1_attachment_type_idx on v1_attachment (
type ASC
)
go

/*==============================================================*/
/* Index: v1_attachment_user_idx                                */
/*==============================================================*/
create index v1_attachment_user_idx on v1_attachment (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_attachment_favourite_idx                           */
/*==============================================================*/
create index v1_attachment_favourite_idx on v1_attachment (
favourite ASC
)
go

/*==============================================================*/
/* Index: v1_attachment_last_post_idx                           */
/*==============================================================*/
create index v1_attachment_last_post_idx on v1_attachment (
last_post_id ASC
)
go

/*==============================================================*/
/* Table: v1_auto_saved                                         */
/*==============================================================*/
create table v1_auto_saved (
   topic_id             int                  not null,
   read_marker          varchar(255)         not null,
   dt                   datetime             not null,
   text_content         nvarchar(max)        null
)
go

/*==============================================================*/
/* Index: v1_auto_saved_tid_idx                                 */
/*==============================================================*/
create index v1_auto_saved_tid_idx on v1_auto_saved (
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_auto_saved_dt_idx                                  */
/*==============================================================*/
create index v1_auto_saved_dt_idx on v1_auto_saved (
dt ASC
)
go

/*==============================================================*/
/* Index: v1_auto_saved_rm_idx                                  */
/*==============================================================*/
create index v1_auto_saved_rm_idx on v1_auto_saved (
read_marker ASC
)
go

/*==============================================================*/
/* Table: v1_banned_ips                                         */
/*==============================================================*/
create table v1_banned_ips (
   ip                   varchar(250)         not null,
   banned_until         datetime             not null,
   hits                 int                  not null,
   hit_limit            int                  not null default 0,
   check_period         int                  not null default 60,
   atype                varchar(255)         null,
   statistics_request   int                  not null default 0
)
go

/*==============================================================*/
/* Index: v1_banned_ips_idx                                     */
/*==============================================================*/
create index v1_banned_ips_idx on v1_banned_ips (
ip ASC
)
go

/*==============================================================*/
/* Table: v1_browser_daily_statistics                           */
/*==============================================================*/
create table v1_browser_daily_statistics (
   dt                   date                 not null,
   browser              nvarchar(250)        null,
   os                   nvarchar(250)        null,
   bot                  nvarchar(250)        null,
   read_marker          varchar(255)         null,
   bot_hits_count       int                  not null default 0
)
go

/*==============================================================*/
/* Index: v1_browser_daily_statistics_dt_idx                    */
/*==============================================================*/
create index v1_browser_daily_statistics_dt_idx on v1_browser_daily_statistics (
dt ASC
)
go

/*==============================================================*/
/* Index: v1_browser_daily_statistics_browser_idx               */
/*==============================================================*/
create index v1_browser_daily_statistics_browser_idx on v1_browser_daily_statistics (
browser ASC
)
go

/*==============================================================*/
/* Index: v1_browser_daily_statistics_os_idx                    */
/*==============================================================*/
create index v1_browser_daily_statistics_os_idx on v1_browser_daily_statistics (
os ASC
)
go

/*==============================================================*/
/* Index: v1_browser_daily_statistics_bot_idx                   */
/*==============================================================*/
create index v1_browser_daily_statistics_bot_idx on v1_browser_daily_statistics (
bot ASC
)
go

/*==============================================================*/
/* Index: v1_browser_daily_statistics_rm_idx                    */
/*==============================================================*/
create index v1_browser_daily_statistics_rm_idx on v1_browser_daily_statistics (
read_marker ASC
)
go

/*==============================================================*/
/* Table: v1_cache_invalidation                                 */
/*==============================================================*/
create table v1_cache_invalidation (
   new_dt               datetime             null
)
go

/*==============================================================*/
/* Table: v1_daily_statistics                                   */
/*==============================================================*/
create table v1_daily_statistics (
   user_id              int                  null,
   forum_id             int                  null,
   dt                   date                 not null,
   hits_count           int                  not null default 0,
   bot_hits_count       int                  not null default 0,
   post_count           int                  not null default 0,
   time_online          bigint               not null default 0,
   bot                  varchar(250)         null
)
go

/*==============================================================*/
/* Index: v1_daily_statistics_unq                               */
/*==============================================================*/
create unique index v1_daily_statistics_unq on v1_daily_statistics (
user_id ASC,
forum_id ASC,
dt ASC,
bot ASC
)
go

/*==============================================================*/
/* Index: v1_daily_statistics_dt_idx                            */
/*==============================================================*/
create index v1_daily_statistics_dt_idx on v1_daily_statistics (
dt ASC
)
go

/*==============================================================*/
/* Index: v1_daily_statistics_user_idx                          */
/*==============================================================*/
create index v1_daily_statistics_user_idx on v1_daily_statistics (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_daily_statistics_forum_idx                         */
/*==============================================================*/
create index v1_daily_statistics_forum_idx on v1_daily_statistics (
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_daily_statistics_bot_idx                           */
/*==============================================================*/
create index v1_daily_statistics_bot_idx on v1_daily_statistics (
bot ASC
)
go

/*==============================================================*/
/* Table: v1_dual                                               */
/*==============================================================*/
create table v1_dual (
   dummy_val            tinyint              not null default 0
)
go

/*==============================================================*/
/* Table: v1_events                                             */
/*==============================================================*/
create table v1_events (
   id                   int                  identity,
   event_time           datetime             not null,
   event_code           varchar(255)         not null,
   params               nvarchar(max)        null,
   author_name          nvarchar(255)        null,
   author_id            int                  null,
   post_id              int                  null,
   is_new               tinyint              not null default 1,
   todo                 tinyint              not null default 0,
   user_id              int                  not null,
   redundant            tinyint              not null default 0,
   topic_name           nvarchar(1000)       null,
   topic_id             int                  null,
   forum_name           nvarchar(255)        null,
   forum_id             int                  null,
   comment              nvarchar(max)        null,
   source_topic_name    nvarchar(1000)       null,
   source_topic_id      int                  null,
   constraint v1_events_pk primary key (id)
)
go

/*==============================================================*/
/* Index: v1_events_evtime_idx                                  */
/*==============================================================*/
create index v1_events_evtime_idx on v1_events (
event_time ASC
)
go

/*==============================================================*/
/* Index: v1_events_user_id_idx                                 */
/*==============================================================*/
create index v1_events_user_id_idx on v1_events (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_events_post_id_idx                                 */
/*==============================================================*/
create index v1_events_post_id_idx on v1_events (
post_id ASC
)
go

/*==============================================================*/
/* Index: v1_events_redundant_idx                               */
/*==============================================================*/
create index v1_events_redundant_idx on v1_events (
redundant ASC
)
go

/*==============================================================*/
/* Index: v1_events_todo_idx                                    */
/*==============================================================*/
create index v1_events_todo_idx on v1_events (
todo ASC
)
go

/*==============================================================*/
/* Index: v1_events_code_idx                                    */
/*==============================================================*/
create index v1_events_code_idx on v1_events (
event_code ASC
)
go

/*==============================================================*/
/* Index: v1_events_author_name_idx                             */
/*==============================================================*/
create index v1_events_author_name_idx on v1_events (
author_name ASC
)
go

/*==============================================================*/
/* Index: v1_events_topic_id_idx                                */
/*==============================================================*/
create index v1_events_topic_id_idx on v1_events (
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_events_src_topic_id_idx                            */
/*==============================================================*/
create index v1_events_src_topic_id_idx on v1_events (
source_topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_events_forum_id_idx                                */
/*==============================================================*/
create index v1_events_forum_id_idx on v1_events (
forum_id ASC
)
go

/*==============================================================*/
/* Table: v1_favourite_posts                                    */
/*==============================================================*/
create table v1_favourite_posts (
   post_id              int                  not null,
   user_id              int                  not null
)
go

/*==============================================================*/
/* Index: v1_favourite_posts_unq                                */
/*==============================================================*/
create unique index v1_favourite_posts_unq on v1_favourite_posts (
post_id ASC,
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_favourite_posts_post_id                            */
/*==============================================================*/
create index v1_favourite_posts_post_id on v1_favourite_posts (
post_id ASC
)
go

/*==============================================================*/
/* Index: v1_favourite_posts_user_id                            */
/*==============================================================*/
create index v1_favourite_posts_user_id on v1_favourite_posts (
user_id ASC
)
go

/*==============================================================*/
/* Table: v1_favourite_topics                                   */
/*==============================================================*/
create table v1_favourite_topics (
   user_id              int                  not null,
   topic_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_favourite_topics_unq                               */
/*==============================================================*/
create unique index v1_favourite_topics_unq on v1_favourite_topics (
user_id ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_favourite_topics_user_id_idx                       */
/*==============================================================*/
create index v1_favourite_topics_user_id_idx on v1_favourite_topics (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_favourite_topics_topic_id_idx                      */
/*==============================================================*/
create index v1_favourite_topics_topic_id_idx on v1_favourite_topics (
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_forum                                              */
/*==============================================================*/
create table v1_forum (
   id                   int                  identity,
   name                 nvarchar(255)        not null,
   protected_by_password tinyint              not null default 0,
   password             nvarchar(255)        null,
   creation_date        datetime             not null,
   restricted_access    tinyint              not null default 0,
   no_guests            tinyint              not null default 0,
   restricted_guest_mode tinyint              not null default 0,
   user_posting_as_guest tinyint              not null default 0,
   hide_from_robots     tinyint              not null default 0,
   description          nvarchar(500)        null,
   deleted              tinyint              not null default 0,
   closed               tinyint              not null default 0,
   allow_edit           tinyint              not null default 0,
   sort_order           int                  not null default 0,
   access_duration      int                  null,
   access_message_count int                  null,
   stringent_rules      tinyint              not null default 0,
   disable_ignore       tinyint              not null default 0,
   forum_group_id       int                  null,
   constraint v1_forum_pk primary key nonclustered (id)
)
go

/*==============================================================*/
/* Index: v1_forum_name_unq                                     */
/*==============================================================*/
create unique index v1_forum_name_unq on v1_forum (
name ASC
)
go

/*==============================================================*/
/* Index: v1_forum_is_deleted_idx                               */
/*==============================================================*/
create index v1_forum_is_deleted_idx on v1_forum (
deleted ASC
)
go

/*==============================================================*/
/* Index: v1_forum_protected_by_pwd_idx                         */
/*==============================================================*/
create index v1_forum_protected_by_pwd_idx on v1_forum (
protected_by_password ASC
)
go

/*==============================================================*/
/* Index: v1_forum_access_idx                                   */
/*==============================================================*/
create index v1_forum_access_idx on v1_forum (
restricted_access ASC
)
go

/*==============================================================*/
/* Table: v1_forum_blocked                                      */
/*==============================================================*/
create table v1_forum_blocked (
   block_expires        datetime             null,
   user_id              int                  not null,
   forum_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_forum_blocked_unq                                  */
/*==============================================================*/
create unique index v1_forum_blocked_unq on v1_forum_blocked (
user_id ASC,
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_blocked_user_id_idx                          */
/*==============================================================*/
create index v1_forum_blocked_user_id_idx on v1_forum_blocked (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_blocked_forum_id_idx                         */
/*==============================================================*/
create index v1_forum_blocked_forum_id_idx on v1_forum_blocked (
forum_id ASC
)
go

/*==============================================================*/
/* Table: v1_forum_group                                        */
/*==============================================================*/
create table v1_forum_group (
   id                   int                  identity,
   name                 varchar(255)         not null,
   sort_order           int                  not null default 0,
   constraint v1_forum_group_pk primary key nonclustered (id)
)
go

/*==============================================================*/
/* Index: v1_forum_group_name_unq                               */
/*==============================================================*/
create unique index v1_forum_group_name_unq on v1_forum_group (
name ASC
)
go

/*==============================================================*/
/* Table: v1_forum_hits                                         */
/*==============================================================*/
create table v1_forum_hits (
   forum_id             int                  null,
   topic_id             int                  null,
   dt                   datetime             not null,
   user_id              int                  null,
   hits_count           int                  not null default 0,
   duration             int                  not null default 0,
   guest_name           nvarchar(250)        null,
   referrer             varchar(700)         null,
   user_agent           nvarchar(700)        null,
   uri                  nvarchar(2000)       null,
   ip                   nvarchar(250)        null,
   browser              nvarchar(250)        null,
   os                   nvarchar(250)        null,
   bot                  nvarchar(250)        null,
   read_marker          varchar(255)         null,
   statistics_request   int                  not null default 0,
   headers              text                 null
)
go

/*==============================================================*/
/* Index: v1_forum_hits_forum_idx                               */
/*==============================================================*/
create index v1_forum_hits_forum_idx on v1_forum_hits (
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_dt_idx                                  */
/*==============================================================*/
create index v1_forum_hits_dt_idx on v1_forum_hits (
dt ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_user_idx                                */
/*==============================================================*/
create index v1_forum_hits_user_idx on v1_forum_hits (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_topic_idx                               */
/*==============================================================*/
create index v1_forum_hits_topic_idx on v1_forum_hits (
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_ua_idx                                  */
/*==============================================================*/
create index v1_forum_hits_ua_idx on v1_forum_hits (
user_agent ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_rm_idx                                  */
/*==============================================================*/
create index v1_forum_hits_rm_idx on v1_forum_hits (
read_marker ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_guest_name_idx                          */
/*==============================================================*/
create index v1_forum_hits_guest_name_idx on v1_forum_hits (
guest_name ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_ip_idx                                  */
/*==============================================================*/
create index v1_forum_hits_ip_idx on v1_forum_hits (
ip ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_bot_idx                                 */
/*==============================================================*/
create index v1_forum_hits_bot_idx on v1_forum_hits (
bot ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_browser_idx                             */
/*==============================================================*/
create index v1_forum_hits_browser_idx on v1_forum_hits (
browser ASC
)
go

/*==============================================================*/
/* Index: v1_forum_hits_os_idx                                  */
/*==============================================================*/
create index v1_forum_hits_os_idx on v1_forum_hits (
os ASC
)
go

/*==============================================================*/
/* Table: v1_forum_member                                       */
/*==============================================================*/
create table v1_forum_member (
   user_id              int                  not null,
   forum_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_forum_member_unq                                   */
/*==============================================================*/
create unique index v1_forum_member_unq on v1_forum_member (
user_id ASC,
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_member_user_id_idx                           */
/*==============================================================*/
create index v1_forum_member_user_id_idx on v1_forum_member (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_member_forum_id_idx                          */
/*==============================================================*/
create index v1_forum_member_forum_id_idx on v1_forum_member (
forum_id ASC
)
go

/*==============================================================*/
/* Table: v1_forum_moderator                                    */
/*==============================================================*/
create table v1_forum_moderator (
   user_id              int                  not null,
   forum_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_forum_moderator_unq                                */
/*==============================================================*/
create unique index v1_forum_moderator_unq on v1_forum_moderator (
user_id ASC,
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_moderator_user_id_idx                        */
/*==============================================================*/
create index v1_forum_moderator_user_id_idx on v1_forum_moderator (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_moderator_forum_id_idx                       */
/*==============================================================*/
create index v1_forum_moderator_forum_id_idx on v1_forum_moderator (
forum_id ASC
)
go

/*==============================================================*/
/* Table: v1_forum_read_markers                                 */
/*==============================================================*/
create table v1_forum_read_markers (
   read_marker          varchar(255)         null,
   forum_id             int                  null,
   first_read_date      datetime             null,
   last_activity        datetime             null,
   first_activity       datetime             null,
   ip                   varchar(250)         null
)
go

/*==============================================================*/
/* Index: v1_forum_read_markers_fmrm_idx                        */
/*==============================================================*/
create unique index v1_forum_read_markers_fmrm_idx on v1_forum_read_markers (
read_marker ASC,
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_forum_read_markers_lastact_idx                     */
/*==============================================================*/
create index v1_forum_read_markers_lastact_idx on v1_forum_read_markers (
last_activity ASC
)
go

/*==============================================================*/
/* Index: v1_forum_read_markers_rm_idx                          */
/*==============================================================*/
create index v1_forum_read_markers_rm_idx on v1_forum_read_markers (
read_marker ASC
)
go

/*==============================================================*/
/* Index: v1_forum_read_markers_frdt_idx                        */
/*==============================================================*/
create index v1_forum_read_markers_frdt_idx on v1_forum_read_markers (
first_read_date ASC
)
go

/*==============================================================*/
/* Table: v1_forum_statistics                                   */
/*==============================================================*/
create table v1_forum_statistics (
   forum_id             int                  not null,
   topic_count          int                  not null default 0,
   topic_count_total    int                  not null default 0,
   last_message_date    datetime             null,
   last_message_id      int                  null
)
go

/*==============================================================*/
/* Index: v1_forum_statistics_unq                               */
/*==============================================================*/
create unique index v1_forum_statistics_unq on v1_forum_statistics (
forum_id ASC
)
go

/*==============================================================*/
/* Table: v1_found_post_cache                                   */
/*==============================================================*/
create table v1_found_post_cache (
   post_id              int                  not null,
   topic_id             int                  not null,
   session_id           varchar(255)         not null,
   dt                   datetime             not null,
   search_hash          varchar(255)         null
)
go

/*==============================================================*/
/* Index: v1_found_post_cache_session_idx                       */
/*==============================================================*/
create index v1_found_post_cache_session_idx on v1_found_post_cache (
session_id ASC
)
go

/*==============================================================*/
/* Index: v1_found_post_cache_post_idx                          */
/*==============================================================*/
create index v1_found_post_cache_post_idx on v1_found_post_cache (
post_id ASC
)
go

/*==============================================================*/
/* Index: v1_found_post_cache_topic_idx                         */
/*==============================================================*/
create index v1_found_post_cache_topic_idx on v1_found_post_cache (
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_found_post_cache_hash_idx                          */
/*==============================================================*/
create index v1_found_post_cache_hash_idx on v1_found_post_cache (
search_hash ASC
)
go

/*==============================================================*/
/* Index: v1_found_post_cache_dt_idx                            */
/*==============================================================*/
create index v1_found_post_cache_dt_idx on v1_found_post_cache (
dt ASC
)
go

/*==============================================================*/
/* Table: v1_found_topic_cache                                  */
/*==============================================================*/
create table v1_found_topic_cache (
   topic_id             int                  not null,
   session_id           varchar(255)         not null,
   dt                   datetime             not null,
   search_hash          varchar(255)         null
)
go

/*==============================================================*/
/* Index: v1_found_topic_cache_session_idx                      */
/*==============================================================*/
create index v1_found_topic_cache_session_idx on v1_found_topic_cache (
session_id ASC
)
go

/*==============================================================*/
/* Index: v1_found_topic_cache_topic_idx                        */
/*==============================================================*/
create index v1_found_topic_cache_topic_idx on v1_found_topic_cache (
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_found_topic_cache_hash_idx                         */
/*==============================================================*/
create index v1_found_topic_cache_hash_idx on v1_found_topic_cache (
search_hash ASC
)
go

/*==============================================================*/
/* Index: v1_found_topic_cache_dt_idx                           */
/*==============================================================*/
create index v1_found_topic_cache_dt_idx on v1_found_topic_cache (
dt ASC
)
go

/*==============================================================*/
/* Table: v1_hide_guest_avatars                                 */
/*==============================================================*/
create table v1_hide_guest_avatars (
   user_id              int                  not null,
   avatar               nvarchar(250)        not null
)
go

/*==============================================================*/
/* Index: v1_hide_guest_avatars_unq                             */
/*==============================================================*/
create unique index v1_hide_guest_avatars_unq on v1_hide_guest_avatars (
user_id ASC,
avatar ASC
)
go

/*==============================================================*/
/* Table: v1_hide_profile                                       */
/*==============================================================*/
create table v1_hide_profile (
   user_id              int                  not null,
   hidden_user_id       int                  not null
)
go

/*==============================================================*/
/* Index: v1_hide_profile_unq                                   */
/*==============================================================*/
create unique index v1_hide_profile_unq on v1_hide_profile (
user_id ASC,
hidden_user_id ASC
)
go

/*==============================================================*/
/* Table: v1_ignore_history                                     */
/*==============================================================*/
create table v1_ignore_history (
   user_id              int                  not null,
   ignored_user_id      int                  not null,
   exclude_date         datetime             not null
)
go

/*==============================================================*/
/* Index: v1_ignore_history_unq                                 */
/*==============================================================*/
create unique index v1_ignore_history_unq on v1_ignore_history (
user_id ASC,
ignored_user_id ASC
)
go

/*==============================================================*/
/* Table: v1_ignored_forums                                     */
/*==============================================================*/
create table v1_ignored_forums (
   forum_id             int                  not null,
   user_id              int                  not null
)
go

/*==============================================================*/
/* Index: v1_ignored_forums_unq                                 */
/*==============================================================*/
create unique index v1_ignored_forums_unq on v1_ignored_forums (
forum_id ASC,
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_ignored_forums_user_id_idx                         */
/*==============================================================*/
create index v1_ignored_forums_user_id_idx on v1_ignored_forums (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_ignored_forums_forum_id_idx                        */
/*==============================================================*/
create index v1_ignored_forums_forum_id_idx on v1_ignored_forums (
forum_id ASC
)
go

/*==============================================================*/
/* Table: v1_ignored_guests                                     */
/*==============================================================*/
create table v1_ignored_guests (
   user_id              int                  not null,
   guest_name           nvarchar(255)        not null,
   whitelist            tinyint              not null default 0
)
go

/*==============================================================*/
/* Index: v1_ignored_guests_list_unq                            */
/*==============================================================*/
create unique index v1_ignored_guests_list_unq on v1_ignored_guests (
guest_name ASC,
user_id ASC,
whitelist ASC
)
go

/*==============================================================*/
/* Index: v1_ignored_guests_list_name_idx                       */
/*==============================================================*/
create index v1_ignored_guests_list_name_idx on v1_ignored_guests (
guest_name ASC
)
go

/*==============================================================*/
/* Index: v1_ignored_guests_list_user_id_idx                    */
/*==============================================================*/
create index v1_ignored_guests_list_user_id_idx on v1_ignored_guests (
user_id ASC
)
go

/*==============================================================*/
/* Table: v1_ignored_topics                                     */
/*==============================================================*/
create table v1_ignored_topics (
   user_id              int                  not null,
   topic_id             int                  not null,
   auto_ignored         tinyint              not null default 0
)
go

/*==============================================================*/
/* Index: v1_ignored_topic_unq                                  */
/*==============================================================*/
create unique index v1_ignored_topic_unq on v1_ignored_topics (
user_id ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_ignored_topic_user_id_idx                          */
/*==============================================================*/
create index v1_ignored_topic_user_id_idx on v1_ignored_topics (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_ignored_topic_topic_id_idx                         */
/*==============================================================*/
create index v1_ignored_topic_topic_id_idx on v1_ignored_topics (
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_ignored_topics_archive                             */
/*==============================================================*/
create table v1_ignored_topics_archive (
   topic_id             int                  not null,
   user_id              int                  not null,
   auto_ignored         tinyint              not null default 0
)
go

/*==============================================================*/
/* Index: v1_ignored_topics_archive_unq                         */
/*==============================================================*/
create unique index v1_ignored_topics_archive_unq on v1_ignored_topics_archive (
topic_id ASC,
user_id ASC
)
go

/*==============================================================*/
/* Table: v1_ignored_users                                      */
/*==============================================================*/
create table v1_ignored_users (
   user_id              int                  not null,
   ignored_user_id      int                  not null,
   comment              nvarchar(max)        null
)
go

/*==============================================================*/
/* Index: v1_ignored_users_unq                                  */
/*==============================================================*/
create unique index v1_ignored_users_unq on v1_ignored_users (
user_id ASC,
ignored_user_id ASC
)
go

/*==============================================================*/
/* Index: v1_ignored_users_user_id_idx                          */
/*==============================================================*/
create index v1_ignored_users_user_id_idx on v1_ignored_users (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_ignored_users_ignored_user_id_idx                  */
/*==============================================================*/
create index v1_ignored_users_ignored_user_id_idx on v1_ignored_users (
ignored_user_id ASC
)
go

/*==============================================================*/
/* Table: v1_ip_blocked                                         */
/*==============================================================*/
create table v1_ip_blocked (
   ip                   nvarchar(250)        not null,
   block_expires        datetime             null,
   tp                   varchar(10)          not null default 'IP',
   block_reason         nvarchar(max)        null
)
go

/*==============================================================*/
/* Index: v1_ip_blocked_unq                                     */
/*==============================================================*/
create unique index v1_ip_blocked_unq on v1_ip_blocked (
ip ASC
)
go

/*==============================================================*/
/* Table: v1_ip_daily_statistics                                */
/*==============================================================*/
create table v1_ip_daily_statistics (
   dt                   date                 not null,
   ip                   varchar(250)         null,
   country_code         varchar(10)          null,
   country              nvarchar(250)        null,
   city                 nvarchar(250)        null,
   bot                  nvarchar(250)        null,
   is_tor               tinyint              not null default 0,
   is_proxy             tinyint              not null default 0,
   is_ipv6              tinyint              not null default 0,
   read_marker          varchar(255)         null,
   hits_count           int                  not null default 0
)
go

/*==============================================================*/
/* Index: v1_ip_daily_statistics_dt_idx                         */
/*==============================================================*/
create index v1_ip_daily_statistics_dt_idx on v1_ip_daily_statistics (
dt ASC
)
go

/*==============================================================*/
/* Index: v1_ip_daily_statistics_ip_idx                         */
/*==============================================================*/
create index v1_ip_daily_statistics_ip_idx on v1_ip_daily_statistics (
ip ASC
)
go

/*==============================================================*/
/* Index: v1_ip_daily_statistics_country_idx                    */
/*==============================================================*/
create index v1_ip_daily_statistics_country_idx on v1_ip_daily_statistics (
country ASC
)
go

/*==============================================================*/
/* Index: v1_ip_daily_statistics_city_idx                       */
/*==============================================================*/
create index v1_ip_daily_statistics_city_idx on v1_ip_daily_statistics (
city ASC
)
go

/*==============================================================*/
/* Table: v1_ip_white_list                                      */
/*==============================================================*/
create table v1_ip_white_list (
   ip                   varchar(250)         not null
)
go

/*==============================================================*/
/* Table: v1_load_statistics                                    */
/*==============================================================*/
create table v1_load_statistics (
   dt                   datetime             not null,
   url                  nvarchar(255)        not null,
   user_name            nvarchar(255)        null,
   user_id              int                  null,
   ip                   varchar(255)         null,
   exec_time            int                  not null,
   forum_rm_count       int                  not null default 0,
   topic_rm_count       int                  not null default 0
)
go

/*==============================================================*/
/* Index: v1_load_statistics_dt_idx                             */
/*==============================================================*/
create index v1_load_statistics_dt_idx on v1_load_statistics (
dt ASC
)
go

/*==============================================================*/
/* Table: v1_moderator_log                                      */
/*==============================================================*/
create table v1_moderator_log (
   id                   int                  identity,
   event_time           datetime             not null,
   moderator_name       nvarchar(255)        null,
   moderator_id         int                  null,
   action               nvarchar(255)        null,
   action_expires       datetime             null,
   author_name          nvarchar(255)        null,
   author_id            int                  null,
   post_id              int                  null,
   ip                   nvarchar(250)        null,
   topic_name           nvarchar(1000)       null,
   topic_id             int                  null,
   forum_name           nvarchar(255)        null,
   forum_id             int                  null,
   comment              nvarchar(max)        null,
   redundant            tinyint              not null default 0,
   source_topic_name    nvarchar(1000)       null,
   source_topic_id      int                  null,
   constraint v1_moderator_log_pk primary key nonclustered (id)
)
go

/*==============================================================*/
/* Index: v1_moderator_log_mod_name_idx                         */
/*==============================================================*/
create index v1_moderator_log_mod_name_idx on v1_moderator_log (
moderator_name ASC
)
go

/*==============================================================*/
/* Index: v1_moderator_log_action_idx                           */
/*==============================================================*/
create index v1_moderator_log_action_idx on v1_moderator_log (
action ASC
)
go

/*==============================================================*/
/* Index: v1_moderator_log_forum_id_idx                         */
/*==============================================================*/
create index v1_moderator_log_forum_id_idx on v1_moderator_log (
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_moderator_log_author_name_idx                      */
/*==============================================================*/
create index v1_moderator_log_author_name_idx on v1_moderator_log (
author_name ASC
)
go

/*==============================================================*/
/* Index: v1_moderator_log_evt_tm_idx                           */
/*==============================================================*/
create index v1_moderator_log_evt_tm_idx on v1_moderator_log (
event_time ASC
)
go

/*==============================================================*/
/* Index: v1_moderator_log_redundant_idx                        */
/*==============================================================*/
create index v1_moderator_log_redundant_idx on v1_moderator_log (
redundant ASC
)
go

/*==============================================================*/
/* Index: v1_moderator_log_topic_id_idx                         */
/*==============================================================*/
create index v1_moderator_log_topic_id_idx on v1_moderator_log (
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_moderator_log_src_topic_id_idx                     */
/*==============================================================*/
create index v1_moderator_log_src_topic_id_idx on v1_moderator_log (
source_topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_morphology_dictionary                              */
/*==============================================================*/



create table v1_morphology_dictionary (
   root                 nvarchar(255)        not null,
   word                 nvarchar(255)        not null
)
go

/*==============================================================*/
/* Index: v1_morphology_dictionary_root_idx                     */
/*==============================================================*/
create index v1_morphology_dictionary_root_idx on v1_morphology_dictionary (
root ASC
)
go

/*==============================================================*/
/* Index: v1_morphology_dictionary_word_idx                     */
/*==============================================================*/
create index v1_morphology_dictionary_word_idx on v1_morphology_dictionary (
word ASC
)
go

/*==============================================================*/
/* Table: v1_pinned_topics                                      */
/*==============================================================*/
create table v1_pinned_topics (
   user_id              int                  not null,
   topic_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_pinned_topics_unq                                  */
/*==============================================================*/
create unique index v1_pinned_topics_unq on v1_pinned_topics (
user_id ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_pinned_topics_user_id_idx                          */
/*==============================================================*/
create index v1_pinned_topics_user_id_idx on v1_pinned_topics (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_pinned_topics_topic_id_idx                         */
/*==============================================================*/
create index v1_pinned_topics_topic_id_idx on v1_pinned_topics (
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_poll_options                                       */
/*==============================================================*/
create table v1_poll_options (
   id                   int                  identity,
   name                 nvarchar(700)        not null,
   topic_id             int                  not null,
   constraint v1_poll_options_pk primary key (id)
)
go

/*==============================================================*/
/* Index: v1_poll_options_unq                                   */
/*==============================================================*/
create unique index v1_poll_options_unq on v1_poll_options (
name ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_poll_options_topic_id_idx                          */
/*==============================================================*/
create index v1_poll_options_topic_id_idx on v1_poll_options (
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_poll_user_answers                                  */
/*==============================================================*/
create table v1_poll_user_answers (
   tm                   datetime             null,
   option_id            int                  not null,
   user_id              int                  not null
)
go

/*==============================================================*/
/* Index: v1_poll_user_answers_unq                              */
/*==============================================================*/
create unique index v1_poll_user_answers_unq on v1_poll_user_answers (
option_id ASC,
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_poll_user_answers_option_id_idx                    */
/*==============================================================*/
create index v1_poll_user_answers_option_id_idx on v1_poll_user_answers (
option_id ASC
)
go

/*==============================================================*/
/* Index: v1_poll_user_answers_user_id_idx                      */
/*==============================================================*/
create index v1_poll_user_answers_user_id_idx on v1_poll_user_answers (
user_id ASC
)
go

/*==============================================================*/
/* Table: v1_post                                               */
/*==============================================================*/
create table v1_post (
   id                   int                  identity,
   user_id              int                  null,
   author               nvarchar(255)        not null,
   creation_date        datetime             not null,
   pinned               tinyint              not null default 0,
   deleted              tinyint              not null default 0,
   text_content         nvarchar(max)        null,
   html_content         nvarchar(max)        null,
   searchable_content   nvarchar(max)        null,
   has_picture          tinyint              not null default 0,
   has_audio            tinyint              not null default 0,
   has_video            tinyint              not null default 0,
   has_link             tinyint              not null default 0,
   has_code             tinyint              not null default 0,
   has_attachment       tinyint              not null default 0,
   has_attachment_ref   tinyint              not null default 0,
   read_marker          varchar(255)         null,
   ip                   varchar(250)         null,
   last_updated_by      nvarchar(255)        null,
   last_updated         datetime             null,
   self_edited          tinyint              not null default 0,
   last_warned_by       nvarchar(255)        null,
   last_warning         nvarchar(max)        null,
   bb_parser_version    int                  not null default 1,
   topic_id             int                  not null,
   user_marker          varchar(255)         null,
   user_agent           nvarchar(700)        null,
   is_comment           tinyint              not null default 0,
   is_adult             tinyint              not null default 0,
   is_system            tinyint              not null default 0,
   ref                  int                  null,
   constraint v1_post_pk primary key nonclustered (id)
)
go

/*==============================================================*/
/* Index: v1_post_is_deleted_idx                                */
/*==============================================================*/
create index v1_post_is_deleted_idx on v1_post (
deleted ASC
)
go

/*==============================================================*/
/* Index: v1_post_user_id_idx                                   */
/*==============================================================*/
create index v1_post_user_id_idx on v1_post (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_topic_id_idx                                  */
/*==============================================================*/
create index v1_post_topic_id_idx on v1_post (
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_has_attachment_idx                            */
/*==============================================================*/
create index v1_post_has_attachment_idx on v1_post (
has_attachment ASC
)
go

/*==============================================================*/
/* Index: v1_post_has_attachment_ref_idx                        */
/*==============================================================*/
create index v1_post_has_attachment_ref_idx on v1_post (
has_attachment_ref ASC
)
go

/*==============================================================*/
/* Index: v1_post_ip_idx                                        */
/*==============================================================*/
create index v1_post_ip_idx on v1_post (
ip ASC
)
go

/*==============================================================*/
/* Index: v1_post_rm_idx                                        */
/*==============================================================*/
create index v1_post_rm_idx on v1_post (
read_marker ASC
)
go

/*==============================================================*/
/* Index: v1_post_author_idx                                    */
/*==============================================================*/
create index v1_post_author_idx on v1_post (
author ASC
)
go

/*==============================================================*/
/* Index: v1_post_creation_date_idx                             */
/*==============================================================*/
create index v1_post_creation_date_idx on v1_post (
creation_date ASC
)
go

/*==============================================================*/
/* Index: v1_post_is_pinned_idx                                 */
/*==============================================================*/
create index v1_post_is_pinned_idx on v1_post (
pinned ASC
)
go

/*==============================================================*/
/* Index: v1_post_user_marker_idx                               */
/*==============================================================*/
create index v1_post_user_marker_idx on v1_post (
user_marker ASC
)
go

/*==============================================================*/
/* Index: v1_post_has_video_idx                                 */
/*==============================================================*/
create index v1_post_has_video_idx on v1_post (
has_video ASC
)
go

/*==============================================================*/
/* Index: v1_post_has_audio_idx                                 */
/*==============================================================*/
create index v1_post_has_audio_idx on v1_post (
has_audio ASC
)
go

/*==============================================================*/
/* Index: v1_post_has_link_idx                                  */
/*==============================================================*/
create index v1_post_has_link_idx on v1_post (
has_link ASC
)
go

/*==============================================================*/
/* Index: v1_post_has_code_idx                                  */
/*==============================================================*/
create index v1_post_has_code_idx on v1_post (
has_code ASC
)
go

/*==============================================================*/
/* Index: v1_post_is_comment_idx                                */
/*==============================================================*/
create index v1_post_is_comment_idx on v1_post (
is_comment ASC
)
go

/*==============================================================*/
/* Index: v1_post_is_adult_idx                                  */
/*==============================================================*/
create index v1_post_is_adult_idx on v1_post (
is_adult ASC
)
go

/*==============================================================*/
/* Index: v1_post_has_picture_idx                               */
/*==============================================================*/
create index v1_post_has_picture_idx on v1_post (
has_picture ASC
)
go

/*==============================================================*/
/* Index: v1_post_ref_idx                                       */
/*==============================================================*/
create index v1_post_ref_idx on v1_post (
ref ASC
)
go

/*==============================================================*/
/* Table: v1_post_hierarchy                                     */
/*==============================================================*/
create table v1_post_hierarchy (
   parent_post_id       int                  not null,
   reply_post_id        int                  not null
)
go

/*==============================================================*/
/* Index: v1_post_hierarchy_unq                                 */
/*==============================================================*/
create unique index v1_post_hierarchy_unq on v1_post_hierarchy (
parent_post_id ASC,
reply_post_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_hierarchy_ppost_id                            */
/*==============================================================*/
create index v1_post_hierarchy_ppost_id on v1_post_hierarchy (
parent_post_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_hierarchy_rpost_id                            */
/*==============================================================*/
create index v1_post_hierarchy_rpost_id on v1_post_hierarchy (
reply_post_id ASC
)
go

/*==============================================================*/
/* Table: v1_post_history                                       */
/*==============================================================*/
create table v1_post_history (
   id                   int                  identity,
   dt                   datetime             not null,
   author               nvarchar(255)        null,
   self_edited          tinyint              not null default 0,
   text_content         nvarchar(max)        null,
   html_content         nvarchar(max)        null,
   post_id              int                  not null,
   constraint v1_post_history_pk primary key (id)
)
go

/*==============================================================*/
/* Index: v1_post_history_post_id_idx                           */
/*==============================================================*/
create index v1_post_history_post_id_idx on v1_post_history (
post_id ASC
)
go

/*==============================================================*/
/* Table: v1_post_rating                                        */
/*==============================================================*/
create table v1_post_rating (
   id                   int                  identity,
   rating               int                  not null,
   dt                   datetime             null,
   post_id              int                  not null,
   user_id              int                  not null,
   rater_ignored        tinyint              not null default 0,
   constraint v1_post_rating_pk primary key (id)
)
go

/*==============================================================*/
/* Index: v1_post_rating_unq                                    */
/*==============================================================*/
create unique index v1_post_rating_unq on v1_post_rating (
post_id ASC,
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_rating_post_idx                               */
/*==============================================================*/
create index v1_post_rating_post_idx on v1_post_rating (
post_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_rating_user_idx                               */
/*==============================================================*/
create index v1_post_rating_user_idx on v1_post_rating (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_rating_raiting_idx                            */
/*==============================================================*/
create index v1_post_rating_raiting_idx on v1_post_rating (
rating ASC
)
go

/*==============================================================*/
/* Table: v1_post_statistics                                    */
/*==============================================================*/
create table v1_post_statistics (
   post_id              int                  not null,
   like_count           bigint               not null default 0,
   dislike_count        bigint               not null default 0
)
go

/*==============================================================*/
/* Index: v1_post_statistics_unq                                */
/*==============================================================*/
create unique index v1_post_statistics_unq on v1_post_statistics (
post_id ASC
)
go

/*==============================================================*/
/* Table: v1_post_subscription                                  */
/*==============================================================*/
create table v1_post_subscription (
   post_id              int                  not null,
   user_id              int                  not null
)
go

/*==============================================================*/
/* Index: v1_post_subscription_unq                              */
/*==============================================================*/
create unique index v1_post_subscription_unq on v1_post_subscription (
post_id ASC,
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_subscription_post_id_idx                      */
/*==============================================================*/
create index v1_post_subscription_post_id_idx on v1_post_subscription (
post_id ASC
)
go

/*==============================================================*/
/* Index: v1_post_subscription_user_id_idx                      */
/*==============================================================*/
create index v1_post_subscription_user_id_idx on v1_post_subscription (
user_id ASC
)
go

/*==============================================================*/
/* Table: v1_preferred_forum                                    */
/*==============================================================*/
create table v1_preferred_forum (
   user_id              int                  not null,
   forum_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_preferred_forum_unq                                */
/*==============================================================*/
create unique index v1_preferred_forum_unq on v1_preferred_forum (
user_id ASC,
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_preferred_forum_user_id_idx                        */
/*==============================================================*/
create index v1_preferred_forum_user_id_idx on v1_preferred_forum (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_preferred_forum_forum_id_idx                       */
/*==============================================================*/
create index v1_preferred_forum_forum_id_idx on v1_preferred_forum (
forum_id ASC
)
go

/*==============================================================*/
/* Table: v1_private_topics                                     */
/*==============================================================*/
create table v1_private_topics (
   last_visit_date      datetime             null,
   participant_id       int                  not null,
   topic_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_private_topics_unq                                 */
/*==============================================================*/
create unique index v1_private_topics_unq on v1_private_topics (
participant_id ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_private_topics_participant_id_idx                  */
/*==============================================================*/
create index v1_private_topics_participant_id_idx on v1_private_topics (
participant_id ASC
)
go

/*==============================================================*/
/* Index: v1_private_topics_topic_id_idx                        */
/*==============================================================*/
create index v1_private_topics_topic_id_idx on v1_private_topics (
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_protected_guests                                   */
/*==============================================================*/
create table v1_protected_guests (
   guest_name           nvarchar(255)        not null,
   guest_name_hash      nvarchar(255)        null
)
go

/*==============================================================*/
/* Index: v1_protected_guests_unq                               */
/*==============================================================*/
create unique index v1_protected_guests_unq on v1_protected_guests (
guest_name ASC
)
go

/*==============================================================*/
/* Index: v1_protected_guests_hash_idx                          */
/*==============================================================*/
create index v1_protected_guests_hash_idx on v1_protected_guests (
guest_name_hash ASC
)
go

/*==============================================================*/
/* Table: v1_read_marker_activity                               */
/*==============================================================*/
create table v1_read_marker_activity (
   read_marker          varchar(255)         not null,
   last_activity        datetime             null,
   first_activity       datetime             null,
   ip                   varchar(250)         null,
   author               nvarchar(255)        null,
   user_agent           nvarchar(500)        null,
   hits                 int                  not null default 0,
   current_name_start   datetime             null,
   current_name_hits    int                  not null default 0
)
go

/*==============================================================*/
/* Index: v1_read_marker_activity_unq                           */
/*==============================================================*/
create unique index v1_read_marker_activity_unq on v1_read_marker_activity (
read_marker ASC
)
go

/*==============================================================*/
/* Table: v1_reserved_names                                     */
/*==============================================================*/
create table v1_reserved_names (
   user_name            nvarchar(255)        not null,
   user_name_hash       nvarchar(255)        null
)
go

/*==============================================================*/
/* Index: v1_reserved_names_unq                                 */
/*==============================================================*/
create unique index v1_reserved_names_unq on v1_reserved_names (
user_name ASC
)
go

/*==============================================================*/
/* Table: v1_settings                                           */
/*==============================================================*/
create table v1_settings (
   moderator_log        varchar(100)         null,
   default_sender       nvarchar(255)        null,
   receiver             nvarchar(255)        null,
   max_att_size         int                  null,
   max_att_size_audiovideo int                  null,
   max_messages_minute  int                  null,
   max_messages_hour    int                  null,
   max_messages_day     int                  null,
   min_search_interval  int                  null,
   whois_server         nvarchar(500)        null,
   hide_online_status   tinyint              not null default 0,
   approval_required    tinyint              not null default 0,
   delayed_reg_mailing  tinyint              not null default 0,
   max_rates_hour       int                  null,
   max_topics_day       int                  null,
   rates_active         tinyint              not null default 0,
   dislikes_active      tinyint              not null default 0,
   dislikes_anonym      tinyint              not null default 0,
   skin                 varchar(255)         null,
   max_poll_options     int                  null,
   max_user_name_symbols int                  null,
   max_topic_name_symbols int                  null,
   max_message_length   int                  null,
   max_pinned_topics    int                  null,
   max_private_members  int                  null,
   block_tor_ips        tinyint              not null default 0,
   celebration_active   tinyint              not null default 0,
   mourning_active      tinyint              not null default 0,
   snow_effect          tinyint              not null default 0,
   hide_users_from_robots tinyint              not null default 0,
   archive_mode         tinyint              not null default 0,
   hash_ip_addresses    tinyint              not null default 0
)
go

/*==============================================================*/
/* Table: v1_topic                                              */
/*==============================================================*/
create table v1_topic (
   id                   int                  identity,
   name                 nvarchar(800)        not null,
   author               nvarchar(255)        not null,
   creation_date        datetime             not null,
   deleted              tinyint              not null default 0,
   closed               tinyint              not null default 0,
   pinned               tinyint              not null default 0,
   read_marker          varchar(255)         null,
   has_pinned_post      tinyint              not null default 0,
   merged               int                  null,
   is_poll              tinyint              not null default 0,
   poll_comment         nvarchar(max)        null,
   poll_results_delayed tinyint              not null default 0,
   no_guests            tinyint              not null default 0,
   forum_id             int                  not null,
   user_id              int                  null,
   user_marker          varchar(255)         null,
   is_private           tinyint              not null default 0,
   publish_delay        tinyint              not null default 0,
   profiled_topic       tinyint              not null default 0,
   request_moderation   tinyint              not null default 0,
   ref                  int                  null,
   constraint v1_topic_pk primary key nonclustered (id)
)
go

/*==============================================================*/
/* Index: v1_topic_name_idx                                     */
/*==============================================================*/
create index v1_topic_name_idx on v1_topic (
name ASC
)
go

/*==============================================================*/
/* Index: v1_topic_user_id_idx                                  */
/*==============================================================*/
create index v1_topic_user_id_idx on v1_topic (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_forum_id_idx                                 */
/*==============================================================*/
create index v1_topic_forum_id_idx on v1_topic (
forum_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_is_deleted_idx                               */
/*==============================================================*/
create index v1_topic_is_deleted_idx on v1_topic (
deleted ASC
)
go

/*==============================================================*/
/* Index: v1_topic_rm_idx                                       */
/*==============================================================*/
create index v1_topic_rm_idx on v1_topic (
read_marker ASC
)
go

/*==============================================================*/
/* Index: v1_topic_is_pinned_idx                                */
/*==============================================================*/
create index v1_topic_is_pinned_idx on v1_topic (
pinned ASC
)
go

/*==============================================================*/
/* Index: v1_topic_is_private_idx                               */
/*==============================================================*/
create index v1_topic_is_private_idx on v1_topic (
is_private ASC
)
go

/*==============================================================*/
/* Index: v1_topic_publish_delay_idx                            */
/*==============================================================*/
create index v1_topic_publish_delay_idx on v1_topic (
publish_delay ASC
)
go

/*==============================================================*/
/* Index: v1_topic_author_idx                                   */
/*==============================================================*/
create index v1_topic_author_idx on v1_topic (
author ASC
)
go

/*==============================================================*/
/* Index: v1_topic_creation_date_idx                            */
/*==============================================================*/
create index v1_topic_creation_date_idx on v1_topic (
creation_date ASC
)
go

/*==============================================================*/
/* Index: v1_topic_ref_idx                                      */
/*==============================================================*/
create index v1_topic_ref_idx on v1_topic (
ref ASC
)
go

/*==============================================================*/
/* Table: v1_topic_blocked                                      */
/*==============================================================*/
create table v1_topic_blocked (
   user_id              int                  not null,
   topic_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_topic_blocked_unq                                  */
/*==============================================================*/
create unique index v1_topic_blocked_unq on v1_topic_blocked (
user_id ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_blocked_user_id_idx                          */
/*==============================================================*/
create index v1_topic_blocked_user_id_idx on v1_topic_blocked (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_blocked_topic_id_idx                         */
/*==============================================================*/
create index v1_topic_blocked_topic_id_idx on v1_topic_blocked (
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_topic_moderator                                    */
/*==============================================================*/
create table v1_topic_moderator (
   user_id              int                  not null,
   topic_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_topic_moderator_unq                                */
/*==============================================================*/
create index v1_topic_moderator_unq on v1_topic_moderator (
user_id ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_moderator_user_id_idx                        */
/*==============================================================*/
create index v1_topic_moderator_user_id_idx on v1_topic_moderator (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_moderator_topic_id_id_idx                    */
/*==============================================================*/
create index v1_topic_moderator_topic_id_id_idx on v1_topic_moderator (
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_topic_participants                                 */
/*==============================================================*/
create table v1_topic_participants (
   user_id              int                  not null,
   topic_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_topic_participants_unq                             */
/*==============================================================*/
create unique index v1_topic_participants_unq on v1_topic_participants (
user_id ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_topic_read_markers                                 */
/*==============================================================*/
create table v1_topic_read_markers (
   topic_id             int                  null,
   read_marker          varchar(255)         null,
   last_read_date       datetime             null,
   ip                   varchar(250)         null
)
go

/*==============================================================*/
/* Index: v1_topic_read_markers_tprm_idx                        */
/*==============================================================*/
create unique index v1_topic_read_markers_tprm_idx on v1_topic_read_markers (
topic_id ASC,
read_marker ASC
)
go

/*==============================================================*/
/* Index: v1_topic_read_markers_rm_idx                          */
/*==============================================================*/
create index v1_topic_read_markers_rm_idx on v1_topic_read_markers (
read_marker ASC
)
go

/*==============================================================*/
/* Index: v1_topic_read_markers_lrdt_idx                        */
/*==============================================================*/
create index v1_topic_read_markers_lrdt_idx on v1_topic_read_markers (
last_read_date ASC
)
go

/*==============================================================*/
/* Table: v1_topic_statistics                                   */
/*==============================================================*/
create table v1_topic_statistics (
   topic_id             int                  not null,
   post_count           int                  not null default 0,
   post_count_total     int                  not null default 0,
   hits_count           int                  not null default 0,
   bot_hits_count       int                  not null default 0,
   last_message_date    datetime             null,
   last_message_id      int                  null
)
go

/*==============================================================*/
/* Index: v1_topic_statistics_unq                               */
/*==============================================================*/
create unique index v1_topic_statistics_unq on v1_topic_statistics (
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_statistics_lmdate_idx                        */
/*==============================================================*/
create index v1_topic_statistics_lmdate_idx on v1_topic_statistics (
last_message_date ASC
)
go

/*==============================================================*/
/* Index: v1_topic_statistics_lmid_idx                          */
/*==============================================================*/
create index v1_topic_statistics_lmid_idx on v1_topic_statistics (
last_message_id ASC
)
go

/*==============================================================*/
/* Table: v1_topic_subscription                                 */
/*==============================================================*/
create table v1_topic_subscription (
   user_id              int                  not null,
   topic_id             int                  not null
)
go

/*==============================================================*/
/* Index: v1_topic_subscription_unq                             */
/*==============================================================*/
create unique index v1_topic_subscription_unq on v1_topic_subscription (
user_id ASC,
topic_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_subscription_user_id_idx                     */
/*==============================================================*/
create index v1_topic_subscription_user_id_idx on v1_topic_subscription (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_subscription_topic_id_idx                    */
/*==============================================================*/
create index v1_topic_subscription_topic_id_idx on v1_topic_subscription (
topic_id ASC
)
go

/*==============================================================*/
/* Table: v1_topic_view_history                                 */
/*==============================================================*/
create table v1_topic_view_history (
   user_id              int                  null,
   guest_name           nvarchar(255)        null,
   topic_id             int                  not null,
   dt                   datetime             not null,
   ip                   varchar(250)         null
)
go

/*==============================================================*/
/* Index: v1_topic_view_history_unq                             */
/*==============================================================*/
create unique index v1_topic_view_history_unq on v1_topic_view_history (
user_id ASC,
topic_id ASC,
guest_name ASC
)
go

/*==============================================================*/
/* Index: v1_topic_view_history_user_idx                        */
/*==============================================================*/
create index v1_topic_view_history_user_idx on v1_topic_view_history (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_topic_view_history_dt_idx                          */
/*==============================================================*/
create index v1_topic_view_history_dt_idx on v1_topic_view_history (
dt ASC
)
go

/*==============================================================*/
/* Index: v1_topic_view_history_guest_name_idx                  */
/*==============================================================*/
create index v1_topic_view_history_guest_name_idx on v1_topic_view_history (
guest_name ASC
)
go

/*==============================================================*/
/* Table: v1_tor_ips                                            */
/*==============================================================*/
create table v1_tor_ips (
   ip                   varchar(250)         not null,
   hashed_ip            varchar(250)         not null,
   block_level          int                  not null default 0,
   refresh_date         datetime             null
)
go

/*==============================================================*/
/* Index: v1_tor_ips_unq                                        */
/*==============================================================*/
create unique index v1_tor_ips_unq on v1_tor_ips (
ip ASC
)
go

/*==============================================================*/
/* Table: v1_user                                               */
/*==============================================================*/
create table v1_user (
   id                   int                  identity,
   login                nvarchar(255)        not null,
   password_hash        varchar(255)         not null,
   user_name            nvarchar(255)        not null,
   user_name_hash       nvarchar(255)        null,
   email                nvarchar(255)        not null,
   email_hash           nvarchar(255)        null,
   hide_email           tinyint              not null default 1,
   registration_date    datetime             not null,
   last_visit_date      datetime             null,
   last_post_date       datetime             null,
   is_admin             tinyint              not null default 0,
   message              nvarchar(500)        null,
   info                 nvarchar(max)        null,
   homepage             nvarchar(500)        null,
   signature            nvarchar(max)        null,
   api_active           tinyint              not null default 0,
   api_token            varchar(100)         null,
   pwd_reset_hash       varchar(100)         null,
   pwd_reset_expire     datetime             null,
   activation_hash      varchar(100)         null,
   activation_expire    datetime             null,
   activated            tinyint              not null default 0,
   died                 tinyint              not null default 0,
   autologin_hash       varchar(255)         null,
   blocked              tinyint              not null default 0,
   self_blocked         tinyint              not null default 0,
   block_expires        datetime             null,
   block_reason         nvarchar(max)        null,
   hide_user_avatars    tinyint              not null default 0,
   hide_user_info       tinyint              not null default 0,
   hide_pictures        tinyint              not null default 0,
   hide_ignored         tinyint              not null default 0,
   donot_hide_adult_pictures tinyint              not null default 0,
   location             nvarchar(255)        null,
   hidden               tinyint              not null default 0,
   send_notifications   tinyint              not null default 0,
   donot_notify_on_rates tinyint              not null default 0,
   no_private_messages  tinyint              not null default 0,
   turnoff_events       tinyint              not null default 0,
   turnoff_personal_appeals tinyint              not null default 0,
   approved             tinyint              not null default 0,
   read_marker          varchar(255)         null,
   ip                   varchar(250)         null,
   last_ip              varchar(250)         null,
   no_video_expand      tinyint              not null default 0,
   ignore_guests_blacklist tinyint              not null default 0,
   ignore_guests_whitelist tinyint              not null default 0,
   ignore_new_guests    tinyint              not null default 0,
   logout               tinyint              not null default 0,
   last_logout_date     datetime             null,
   last_events_view_date datetime             null,
   rating_blocked       tinyint              not null default 0,
   time_zone            varchar(255)         null,
   privileged           tinyint              not null default 0,
   privileged_topic_moderator tinyint              not null default 0,
   skin                 varchar(255)         null,
   skin_properties      nvarchar(max)        null,
   notify_citation      tinyint              not null default 0,
   notify_about_new_users tinyint              not null default 0,
   interface_language   varchar(50)          null,
   global_ban_allowed   tinyint              not null default 0,
   show_ip              tinyint              not null default 0,
   notify_on_words      tinyint              not null default 0,
   words_to_notify      nvarchar(max)        null,
   last_host            nvarchar(255)        null,
   custom_css           nvarchar(max)        null,
   custom_smiles        nvarchar(max)        null,
   ref                  int                  null,
   email_changed        tinyint              not null default 0,
   constraint v1_user_pk primary key nonclustered (id)
)
go

/*==============================================================*/
/* Index: v1_user_login_unq                                     */
/*==============================================================*/
create unique index v1_user_login_unq on v1_user (
login ASC
)
go

/*==============================================================*/
/* Index: v1_user_nickname_unq                                  */
/*==============================================================*/
create unique index v1_user_nickname_unq on v1_user (
user_name ASC
)
go

/*==============================================================*/
/* Index: v1_user_nickname_hash_idx                             */
/*==============================================================*/
create index v1_user_nickname_hash_idx on v1_user (
user_name_hash ASC
)
go

/*==============================================================*/
/* Index: v1_user_email_unq                                     */
/*==============================================================*/
create unique index v1_user_email_unq on v1_user (
email_hash ASC
)
go

/*==============================================================*/
/* Index: v1_user_autologin_hash_idx                            */
/*==============================================================*/
create index v1_user_autologin_hash_idx on v1_user (
autologin_hash ASC
)
go

/*==============================================================*/
/* Index: v1_user_pwd_reset_hash_idx                            */
/*==============================================================*/
create index v1_user_pwd_reset_hash_idx on v1_user (
pwd_reset_hash ASC
)
go

/*==============================================================*/
/* Index: v1_user_block_expires_idx                             */
/*==============================================================*/
create index v1_user_block_expires_idx on v1_user (
block_expires ASC
)
go

/*==============================================================*/
/* Index: v1_user_read_marker_unq                               */
/*==============================================================*/
create unique index v1_user_read_marker_unq on v1_user (
read_marker ASC
)
go

/*==============================================================*/
/* Table: v1_user_comment                                       */
/*==============================================================*/
create table v1_user_comment (
   user_id              int                  not null,
   commented_user_id    int                  not null,
   comment              nvarchar(max)        null
)
go

/*==============================================================*/
/* Index: v1_user_comment_unq                                   */
/*==============================================================*/
create unique index v1_user_comment_unq on v1_user_comment (
user_id ASC,
commented_user_id ASC
)
go

/*==============================================================*/
/* Table: v1_user_login_tries                                   */
/*==============================================================*/
create table v1_user_login_tries (
   login                nvarchar(250)        not null,
   ip                   varchar(250)         not null,
   dt                   datetime             not null
)
go

/*==============================================================*/
/* Index: v1_user_login_tries_ip_idx                            */
/*==============================================================*/
create index v1_user_login_tries_ip_idx on v1_user_login_tries (
ip ASC
)
go

/*==============================================================*/
/* Table: v1_user_statistics                                    */
/*==============================================================*/
create table v1_user_statistics (
   user_id              int                  not null,
   post_count           bigint               not null default 0,
   like_count           bigint               not null default 0,
   dislike_count        bigint               not null default 0,
   topic_count          bigint               not null default 0,
   time_online          bigint               not null default 0
)
go

/*==============================================================*/
/* Index: v1_user_statistics_unq                                */
/*==============================================================*/
create unique index v1_user_statistics_unq on v1_user_statistics (
user_id ASC
)
go

/*==============================================================*/
/* Table: v1_user_subscription                                  */
/*==============================================================*/
create table v1_user_subscription (
   user_id              int                  not null,
   subscribed_user_id   int                  null,
   subscribed_user_name nvarchar(255)        null,
   tm                   datetime             not null,
   last_view            datetime             null
)
go

/*==============================================================*/
/* Index: v1_user_subscription_unq                              */
/*==============================================================*/
create unique index v1_user_subscription_unq on v1_user_subscription (
user_id ASC,
subscribed_user_id ASC,
subscribed_user_name ASC
)
go

/*==============================================================*/
/* Index: v1_user_subscription_usr_idx                          */
/*==============================================================*/
create index v1_user_subscription_usr_idx on v1_user_subscription (
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_user_subscription_usrname_idx                      */
/*==============================================================*/
create index v1_user_subscription_usrname_idx on v1_user_subscription (
subscribed_user_name ASC
)
go

/*==============================================================*/
/* Index: v1_user_subscription_usrid_idx                        */
/*==============================================================*/
create index v1_user_subscription_usrid_idx on v1_user_subscription (
subscribed_user_id ASC
)
go

/*==============================================================*/
/* Table: v1_user_tag_post                                      */
/*==============================================================*/
create table v1_user_tag_post (
   tag_id               int                  not null,
   post_id              int                  not null
)
go

/*==============================================================*/
/* Index: v1_user_tag_post_unq                                  */
/*==============================================================*/
create unique index v1_user_tag_post_unq on v1_user_tag_post (
tag_id ASC,
post_id ASC
)
go

/*==============================================================*/
/* Index: v1_user_tag_post_tag_id_idx                           */
/*==============================================================*/
create index v1_user_tag_post_tag_id_idx on v1_user_tag_post (
tag_id ASC
)
go

/*==============================================================*/
/* Index: v1_user_tag_post_post_id_idx                          */
/*==============================================================*/
create index v1_user_tag_post_post_id_idx on v1_user_tag_post (
post_id ASC
)
go

/*==============================================================*/
/* Table: v1_user_tags                                          */
/*==============================================================*/
create table v1_user_tags (
   id                   int                  identity,
   name                 nvarchar(255)        null,
   user_id              int                  not null,
   constraint v1_user_tags_pk primary key nonclustered (id)
)
go

/*==============================================================*/
/* Index: v1_user_tags_unq                                      */
/*==============================================================*/
create unique index v1_user_tags_unq on v1_user_tags (
name ASC,
user_id ASC
)
go

/*==============================================================*/
/* Index: v1_user_tags_user_id_idx                              */
/*==============================================================*/
create index v1_user_tags_user_id_idx on v1_user_tags (
user_id ASC
)
go


create procedure v1_deep_collect_replies
  @oid integer
as
begin
   set nocount on

   delete from #tmp_children
   insert into #tmp_children (id) values (@oid)

   while(1 = 1)
   begin
     insert into #tmp_children (id)
     select reply_post_id from v1_post_hierarchy
     where parent_post_id in (select id from #tmp_children)
     and reply_post_id not in (select id from #tmp_children)
     
     if(@@rowcount = 0) break
   end
   
end
go

