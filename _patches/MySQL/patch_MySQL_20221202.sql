/*
*/
create table v1_ignored_forums
(
   forum_id             int not null,
   user_id              int not null
);

create unique index v1_ignored_forums_unq on v1_ignored_forums
(
   forum_id,
   user_id
);

create index v1_ignored_forums_user_id_idx on v1_ignored_forums
(
   user_id
);

create index v1_ignored_forums_forum_id_idx on v1_ignored_forums
(
   forum_id
);

/*
*/
drop table v1_preferred_forum;
