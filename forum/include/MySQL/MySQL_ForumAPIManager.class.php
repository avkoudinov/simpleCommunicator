<?php

class MySQL_ForumAPIManager extends ForumAPIManager
{
    //-----------------------------------------------------------------
    function get_query_topic_list($prfx, $where, $limit)
    {
        return "select {$prfx}_topic.id, {$prfx}_topic.name, {$prfx}_topic.creation_date,
               {$prfx}_topic_statistics.last_message_date,
               {$prfx}_topic_statistics.post_count,
               {$prfx}_topic_statistics.post_count_total,
               {$prfx}_topic_statistics.hits_count,
               {$prfx}_topic_statistics.bot_hits_count,
               {$prfx}_topic.profiled_topic,
               {$prfx}_topic.deleted, {$prfx}_topic.closed, {$prfx}_topic.pinned, {$prfx}_topic.publish_delay, has_pinned_post,
               {$prfx}_forum.deleted forum_deleted, disable_ignore,
               {$prfx}_topic.user_id, {$prfx}_topic.author, {$prfx}_user.user_name, {$prfx}_topic.read_marker,
               {$prfx}_user.last_visit_date, {$prfx}_user.logout,
               forum_id, {$prfx}_forum.name forum_name, is_poll
               from {$prfx}_topic
               inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
               inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
               left join {$prfx}_user on ({$prfx}_topic.user_id = {$prfx}_user.id)
               $where
               order by {$prfx}_topic_statistics.last_message_date desc, {$prfx}_topic.id desc
               limit $limit";
    }
    
    //-----------------------------------------------------------------
    function get_query_post_list($prfx, $where, $uid, $limit)
    {
        return "select
            {$prfx}_post.id, {$prfx}_post.creation_date, text_content, html_content,
            {$prfx}_post.deleted, {$prfx}_post.pinned, {$prfx}_post.is_comment, {$prfx}_post.is_adult,
            {$prfx}_post.topic_id, {$prfx}_topic.forum_id, {$prfx}_topic.name topic_name, {$prfx}_forum.name forum_name, disable_ignore,
            {$prfx}_topic.creation_date topic_creation_date, {$prfx}_topic.author topic_author, {$prfx}_topic.read_marker topic_author_read_marker, is_private,
            profiled_topic, stringent_rules,
            {$prfx}_topic.user_id topic_author_id, {$prfx}_topic.publish_delay,
            {$prfx}_post.user_id, {$prfx}_post.author, user_name, no_private_messages, {$prfx}_post.ip,
            {$prfx}_post.read_marker, {$prfx}_post.user_marker, {$prfx}_post.user_agent,
            {$prfx}_post_rating.post_id, {$prfx}_post_rating.dt rating_dt,
            has_attachment,
            last_updated, last_updated_by, self_edited, allow_edit, user_posting_as_guest, last_warned_by, last_warning
            from {$prfx}_post
            inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
            left join {$prfx}_user on ({$prfx}_post.user_id = {$prfx}_user.id)
            left join {$prfx}_post_rating on ({$prfx}_post_rating.post_id = {$prfx}_post.id and {$prfx}_post_rating.user_id = $uid)
            left join {$prfx}_post_statistics on ({$prfx}_post.id = {$prfx}_post_statistics.post_id)
            $where 
            order by {$prfx}_post.creation_date desc, {$prfx}_post.id desc
            limit $limit";
    } // get_query_post_list
} // MySQL_ForumAPIManager