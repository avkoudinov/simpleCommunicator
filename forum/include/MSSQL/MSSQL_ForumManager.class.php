<?php
//-------------------------------------------------------------------
// class MSSQL_ForumManager
//-------------------------------------------------------------------
class MSSQL_ForumManager extends ForumManager
{
    //-----------------------------------------------------------------
    function get_new_where_appendix($prfx, $rm)
    {
        if (empty($rm)) {
            return " and {$prfx}_post.creation_date > ifnull({$prfx}_topic_read_markers.last_read_date, {$prfx}_forum_read_markers.first_read_date)
                    ";
        }

        return " and {$prfx}_post.read_marker <> '$rm'
                 and {$prfx}_post.creation_date > isnull({$prfx}_topic_read_markers.last_read_date, {$prfx}_forum_read_markers.first_read_date)
                ";
    } // get_new_where_appendix
    
    //-----------------------------------------------------------------
    function get_query_min_topic_post($prfx, $first_post_topic_id, $first_post_id)
    {
        return "select top 1 id from {$prfx}_post
                where topic_id = $first_post_topic_id and deleted <> 1
                and id > $first_post_id
                order by id";
    } // get_query_min_topic_post
    
    //-----------------------------------------------------------------
    function get_query_max_topic_post($prfx, $first_post_topic_id, $first_post_id)
    {
        return "select top 1 id from {$prfx}_post
                where topic_id = $first_post_topic_id and deleted <> 1
                and id < $first_post_id
                order by id desc";    
    } // get_query_max_topic_post
    
    //-----------------------------------------------------------------
    function get_query_empty_topic($prfx, $topic_id)
    {
        return "select all_cnt.id from
                (select {$prfx}_topic.id, count({$prfx}_post.topic_id) cnt
                                             from
                                             {$prfx}_topic
                                             left join {$prfx}_post on ({$prfx}_topic.id = {$prfx}_post.topic_id)
                                             where {$prfx}_topic.deleted <> 1 and
                                             {$prfx}_topic.id = $topic_id 
                                             group by {$prfx}_topic.id) all_cnt
                left join                             
                (select {$prfx}_topic.id, count({$prfx}_post.topic_id) cnt
                                             from
                                             {$prfx}_topic
                                             left join {$prfx}_post on ({$prfx}_topic.id = {$prfx}_post.topic_id)
                                             where {$prfx}_topic.deleted <> 1 and
                                             {$prfx}_topic.id = $topic_id and
                                             {$prfx}_post.deleted = 1
                                             group by {$prfx}_topic.id) deleted_cnt
                on (deleted_cnt.id = all_cnt.id)
                where (all_cnt.cnt - isnull(deleted_cnt.cnt, 0)) = 0";
    } // get_query_empty_topic
    
    //-----------------------------------------------------------------
    function get_query_previous_valid_topic_post($prfx, $where)
    {
        return "select top 1 {$prfx}_post.id
                from {$prfx}_post
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                $where
                order by {$prfx}_post.id desc
               ";
    } // get_query_previous_valid_topic_post

    //-----------------------------------------------------------------
    function get_query_next_valid_topic_post($prfx, $where)
    {
        return "select top 1 {$prfx}_post.id
                from {$prfx}_post
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                $where
                order by {$prfx}_post.id
               ";
    } // get_query_next_valid_topic_post
    
    //-----------------------------------------------------------------
    function get_query_topic_first_post($prfx, $where, $order_by)
    {
        return "select top 1 {$prfx}_post.id, {$prfx}_post.deleted, {$prfx}_post.pinned
               from {$prfx}_post
               inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
               inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
               $where
               $order_by
              ";
    } // get_query_topic_first_post
    
    //-----------------------------------------------------------------
    function get_query_user($prfx, $where, $master_admin_name)
    {
        $master_appendix = "";
        if(!empty($master_admin_name)) {
            $master_appendix = "
                union
                select id, user_name
                from
                (select 0 id, '$master_admin_name' user_name from {$prfx}_dual) master_admin
                $where
            ";
        }

        return "select top 100 id, user_name
            from
            {$prfx}_user
            $where
            $master_appendix
            order by user_name";
    } // get_query_user
    
    //-----------------------------------------------------------------
    function get_query_last_guest_activity($prfx, $where)
    {
        return "select top 1 dt, ip
                 from {$prfx}_forum_hits
                 $where
                 order by dt desc";
    } // get_query_last_guest_activity
    
    //-----------------------------------------------------------------
    function get_query_tor_ip_list($prfx, $where)
    {
        return "select
                 {$prfx}_tor_ips.ip,
                 {$prfx}_tor_ips.block_level,
                 min({$prfx}_post.creation_date) first_message,
                 max({$prfx}_post.creation_date) last_message,
                 count({$prfx}_post.ip) cnt
                 from {$prfx}_tor_ips
                 inner join {$prfx}_post on ({$prfx}_tor_ips.ip = {$prfx}_post.ip)
                 $where
                 group by {$prfx}_tor_ips.ip, {$prfx}_tor_ips.block_level
                 order by last_message desc
                 ";
    } // get_query_tor_ip_list
    
    //-----------------------------------------------------------------
    function get_query_topic_search(&$dbw, $prfx, $uid, $search)
    {
        $where = "";
        
        $tid = ltrim($search, "#");
        
        if (is_numeric($tid)) {
            $where .= " id = $tid or";
        }
        
        $topic_search_key_clause = $this->get_topic_search_clause($dbw, $prfx, $search, true);
        $search = $dbw->quotes_or_null("%" . $search . "%");
        $where .= " $topic_search_key_clause or name like $search";
        
        $forum_list = array();
        if (!$this->get_forum_list($forum_list)) {
            return false;
        }
        
        $in_list = "";
        $forum_name = "-";
        foreach ($forum_list as $fid => $dummy) {
            if (!$this->has_access_to_forum($fid, $forum_name, false)) {
                continue;
            }
            
            if ($this->need_forum_password("", $fid)) {
                continue;
            }
            
            $in_list .= $fid . ",";
        }
        
        $in_list = trim($in_list, ",");
        if (empty($in_list)) {
            $in_list = "NULL";
        }
    
        return "select top 100 id, name topic_name
            from
            {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            where deleted <> 1 and merged is NULL and ($where) and
            (forum_id in ($in_list) or exists (select 1 from {$prfx}_private_topics where {$prfx}_private_topics.topic_id = {$prfx}_topic.id and participant_id = $uid))
            order by last_message_id desc";
    } // get_query_topic_search
    
    //-----------------------------------------------------------------
    function get_query_existing_topic_search(&$dbw, $prfx, $search, $forum)
    {
        $search = $dbw->escape($search);
        $fid = $dbw->escape($forum);
        
        $where = " forum_id = $fid and name like '$search%'";
        
        return "select top 100 id, name topic_name
            from
            {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            where deleted <> 1 and merged is NULL and is_private < 1 and ($where)
            order by last_message_id desc";
    } // get_query_existing_topic_search

    //-----------------------------------------------------------------
    function get_query_moderated_topic_search(&$dbw, $prfx, $search, $source_fid, $merge_modus)
    {
        $where = "";
        
        $tid = ltrim($search, "#");
        
        if (is_numeric($tid)) {
            $where .= " id = $tid or";
        }
        
        $topic_search_key_clause = $this->get_topic_search_clause($dbw, $prfx, $search, true);
        $search = $dbw->escape($search);
        $where .= " $topic_search_key_clause or name like '%$search%'";
        
        $forum_list = array();
        if (!$this->get_forum_list($forum_list)) {
            return false;
        }
        
        $in_list = "";
        $forum_name = "-";
        foreach ($forum_list as $fid => $dummy) {
            if (!$this->has_access_to_forum($fid, $forum_name, false)) {
                continue;
            }
            
            if ($this->need_forum_password("", $fid)) {
                continue;
            }
            
            if (!empty($merge_modus) && !$this->is_admin() && !$this->is_forum_moderator($fid)) {
                continue;
            }
            
            $in_list .= $fid . ",";
        }
        
        $in_list = trim($in_list, ",");
        if (empty($in_list)) {
            $in_list = "NULL";
        }
        
        return "select top 100 id, name topic_name
            from
            {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            where deleted <> 1 and merged is NULL and ($where) and
            forum_id in ($in_list)
            order by last_message_id desc";
    } // get_query_moderated_topic_search
    
    //-----------------------------------------------------------------
    function get_query_blocked_user_list_order_clause($prfx)
    {
        return "blocked desc, isnull(block_expires, dateadd (year, 1, current_timestamp)), registration_date desc";
    }
    
    //-----------------------------------------------------------------
    function get_query_user_list($prfx, $start_date, $where, $order_by, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"] + 1;
        $end = $begin + $pagination_info["rows_per_page"] - 1;
        
        return "select
             id, user_name, activated, approved, hidden,
             blocked, self_blocked, block_expires,
             registration_date, last_visit_date, logout,

             post_count,
             like_count,
             dislike_count,
             like_count_weighed,
             dislike_count_weighed,
             topic_count,
             time_online,
             week_post_count,
             week_hits_count,
             week_time_online
           from
           (select
           row_number() over(order by $order_by) nr,
           id, user_name, activated, approved, hidden,
           blocked, self_blocked, block_expires,
           registration_date, last_visit_date, logout,
           
           {$prfx}_user_statistics.post_count,
           {$prfx}_user_statistics.like_count,
           {$prfx}_user_statistics.dislike_count,

           100.0 * {$prfx}_user_statistics.like_count / case when {$prfx}_user_statistics.post_count > 1000 then {$prfx}_user_statistics.post_count else 1000 end like_count_weighed,
           100.0 * {$prfx}_user_statistics.dislike_count / case when {$prfx}_user_statistics.post_count > 1000 then {$prfx}_user_statistics.post_count else 1000 end dislike_count_weighed,

           {$prfx}_user_statistics.topic_count,
           {$prfx}_user_statistics.time_online,
           week_statistics.week_post_count,
           week_statistics.week_hits_count,
           week_statistics.week_time_online

           from {$prfx}_user

           left join {$prfx}_user_statistics
           on ({$prfx}_user.id = {$prfx}_user_statistics.user_id)

           left join
             (select user_id, sum(hits_count) week_hits_count, sum(post_count) week_post_count, sum(time_online) week_time_online
                                  from  {$prfx}_daily_statistics
                                  where dt > '$start_date'
                                  group by user_id) week_statistics
             on ({$prfx}_user.id = week_statistics.user_id)

           $where) users
           where nr between $begin and $end
           order by nr
           ";
    } // get_query_user_list
    
    //-----------------------------------------------------------------
    function get_query_read_marker_list($prfx, $where, $order_by, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"] + 1;
        $end = $begin + $pagination_info["rows_per_page"] - 1;
        
        return "select
             read_marker, last_activity, first_activity, ip, current_name_start, current_name_hits, author, user_agent, hits, last_visit_date, logout, id
           from
           (select
           row_number() over(order by $order_by) nr,
           {$prfx}_read_marker_activity.read_marker, last_activity, first_activity, {$prfx}_read_marker_activity.ip,
           current_name_start, current_name_hits, author, user_agent, hits, last_visit_date, logout,
           {$prfx}_user.id

           from {$prfx}_read_marker_activity
           left join {$prfx}_user on ({$prfx}_read_marker_activity.author = {$prfx}_user.user_name)

           $where) users
           where nr between $begin and $end
           order by nr
           ";
    } // get_query_read_marker_list
    
    //-----------------------------------------------------------------
    function get_query_user_agent_list($prfx, $where)
    {
        return "select top 1000
                   dt,
                   user_id,
                   guest_name,
                   user_agent,
                   uri,
                   {$prfx}_forum_hits.ip,
                   last_visit_date, logout
                  from {$prfx}_forum_hits
                  left join {$prfx}_user on ({$prfx}_forum_hits.user_id = {$prfx}_user.id)
                  $where
                  order by dt desc";
    } // get_query_user_agent_list
    
    //-----------------------------------------------------------------
    function get_query_forum_topics($prfx, $uid, $user_pinned_topic_appendix, $where, &$pagination_info)
    {
        if ($pagination_info["page"] == 1) {
            $begin = 1;
            $end = $begin + $pagination_info["rows_per_page"] - 1 - $pagination_info["pinned_count"];
        } else {
            $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"] + 1 - $pagination_info["pinned_count"];
            $end = $begin + $pagination_info["rows_per_page"] - 1;
        }
        
        return "select
            id, name, creation_date,
            last_message_date, 
            post_count, 
            post_count_total,
            hits_count,
            bot_hits_count,
            profiled_topic,
            deleted, closed, pinned, publish_delay, has_pinned_post,
            forum_deleted, disable_ignore,
            user_id, author, read_marker, user_name,
            last_visit_date, logout,
            forum_id, forum_name, is_poll, no_guests
            from
            (select row_number() over (order by {$prfx}_topic_statistics.last_message_id desc) nr,
            {$prfx}_topic.id, {$prfx}_topic.name, {$prfx}_topic.creation_date,
            {$prfx}_topic_statistics.last_message_date, 
            {$prfx}_topic_statistics.post_count, 
            {$prfx}_topic_statistics.post_count_total,
            {$prfx}_topic_statistics.hits_count,
            {$prfx}_topic_statistics.bot_hits_count,
            {$prfx}_topic.profiled_topic,
            {$prfx}_topic.deleted, {$prfx}_topic.closed, {$prfx}_topic.pinned, {$prfx}_topic.publish_delay, has_pinned_post,
            {$prfx}_forum.deleted forum_deleted, disable_ignore,
            {$prfx}_topic.user_id, {$prfx}_topic.author, {$prfx}_topic.read_marker, {$prfx}_user.user_name,
            {$prfx}_user.last_visit_date, {$prfx}_user.logout,
            forum_id, {$prfx}_forum.name forum_name, is_poll, {$prfx}_topic.no_guests
            from {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
            left join {$prfx}_user on ({$prfx}_topic.user_id = {$prfx}_user.id)
            $where and ({$prfx}_topic.pinned + {$prfx}_topic.publish_delay = 0 $user_pinned_topic_appendix)
            ) topics
            where nr between $begin and $end
           ";
    } // get_query_forum_topics
    
    //-----------------------------------------------------------------
    function get_query_paginated_found_posts($prfx, $current_uid, $where, &$pagination_info, $order_by)
    {
        if ($pagination_info["mode"] == "all") {
            $limit = "";
        } else {
            $begin = ($pagination_info["page"] - 1) * $pagination_info["posts_per_page"] + 1;
            $end = $begin + $pagination_info["posts_per_page"] - 1;
            $limit = "where nr between $begin and $end";
        }
        
        return "select id, creation_date, html_content,
            deleted, pinned, is_comment, is_adult,
            user_id, author, user_name, no_private_messages, ip, 
            read_marker, user_marker, user_agent,
            forum_id, forum_name, disable_ignore,
            topic_id, topic_author_id,
            topic_name, topic_creation_date, topic_author, topic_author_read_marker,
            is_private, profiled_topic, stringent_rules, publish_delay,
            has_attachment,
            post_id, rating_dt,
            last_updated, last_updated_by, self_edited, allow_edit, user_posting_as_guest, last_warned_by, last_warning
            from
           (select
            row_number() over ($order_by) nr,
            {$prfx}_post.id, {$prfx}_post.creation_date, html_content,
            {$prfx}_post.deleted, {$prfx}_post.pinned, {$prfx}_post.is_comment, {$prfx}_post.is_adult,
            {$prfx}_post.user_id, {$prfx}_post.author, user_name, no_private_messages, {$prfx}_post.ip, 
            {$prfx}_post.read_marker, {$prfx}_post.user_marker, {$prfx}_post.user_agent,
            {$prfx}_topic.forum_id,
            {$prfx}_post.topic_id, {$prfx}_topic.user_id topic_author_id,
            is_private, profiled_topic, stringent_rules, publish_delay,
            {$prfx}_topic.name topic_name, {$prfx}_forum.name forum_name, disable_ignore,
            {$prfx}_topic.creation_date topic_creation_date, {$prfx}_topic.author topic_author, {$prfx}_topic.read_marker topic_author_read_marker,
            has_attachment,
            {$prfx}_post_rating.post_id, {$prfx}_post_rating.dt rating_dt,
            last_updated, last_updated_by, self_edited, allow_edit, user_posting_as_guest, last_warned_by, last_warning
            from {$prfx}_post
            inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
            left join {$prfx}_user on ({$prfx}_post.user_id = {$prfx}_user.id)
            left join {$prfx}_post_rating on ({$prfx}_post_rating.post_id = {$prfx}_post.id and {$prfx}_post_rating.user_id = $current_uid)
            left join {$prfx}_post_statistics on ({$prfx}_post.id = {$prfx}_post_statistics.post_id)
            $where) posts
            $limit
            order by nr
           ";
    } // get_query_paginated_found_posts

    //-----------------------------------------------------------------
    function get_query_topic_posts($prfx, $current_uid, $where, $limit, $order_by)
    {
        if (!empty($limit)) {
            $limit = "top $limit";
        } else {
            $limit = "";
        }
    
        return "select $limit
            {$prfx}_post.id, {$prfx}_post.creation_date, html_content,
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
            left join {$prfx}_post_rating on ({$prfx}_post_rating.post_id = {$prfx}_post.id and {$prfx}_post_rating.user_id = $current_uid)
            left join {$prfx}_post_statistics on ({$prfx}_post.id = {$prfx}_post_statistics.post_id)
            $where
            $order_by
            ";
    } // get_query_topic_posts

    //-----------------------------------------------------------------
    function get_query_topic_cnt_update($prfx, $fid)
    {
        return "update
              {$prfx}_forum_statistics
            set
              {$prfx}_forum_statistics.topic_count = isnull(t_total_cnt.cnt, 0) - isnull(t_invisible_cnt.cnt, 0),
              {$prfx}_forum_statistics.topic_count_total = isnull(t_total_cnt.cnt, 0) - isnull(t_delayed_cnt.cnt, 0),
              {$prfx}_forum_statistics.last_message_date = isnull(t_last.last_message_date, {$prfx}_forum_statistics.last_message_date),
              {$prfx}_forum_statistics.last_message_id = isnull(t_last.last_message_id, {$prfx}_forum_statistics.last_message_id)
            from
              {$prfx}_forum_statistics
              left join
                (select top 1 forum_id, 
                 {$prfx}_topic_statistics.last_message_date last_message_date, 
                 {$prfx}_topic_statistics.last_message_id last_message_id
                 from {$prfx}_topic 
                 inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
                 where forum_id = $fid and deleted <> 1 and publish_delay <> 1
                 order by {$prfx}_topic_statistics.last_message_id desc
                ) t_last
              on ({$prfx}_forum_statistics.forum_id = t_last.forum_id)
              left join
                (select forum_id, count(*) cnt
                 from {$prfx}_topic where forum_id = $fid
                 group by forum_id
                ) t_total_cnt
              on ({$prfx}_forum_statistics.forum_id = t_total_cnt.forum_id)
              left join
                (select forum_id, count(*) cnt
                 from {$prfx}_topic where (deleted = 1 or publish_delay = 1) and forum_id = $fid
                 group by forum_id
                ) t_invisible_cnt
              on ({$prfx}_forum_statistics.forum_id = t_invisible_cnt.forum_id)
              left join
                (select forum_id, count(*) cnt
                 from {$prfx}_topic where publish_delay = 1 and forum_id = $fid
                 group by forum_id
                ) t_delayed_cnt
              on ({$prfx}_forum_statistics.forum_id = t_delayed_cnt.forum_id)
            where {$prfx}_forum_statistics.forum_id = $fid";
    } // get_query_topic_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_post_cnt_update($prfx, $tid)
    {
        return "update
              {$prfx}_topic_statistics
            set
              {$prfx}_topic_statistics.post_count = isnull(ptcnt.cnt, 0) - isnull(pdcnt.cnt, 0),
              {$prfx}_topic_statistics.post_count_total = isnull(ptcnt.cnt, 0),
              {$prfx}_topic_statistics.last_message_date = isnull(plast.last_message_date, {$prfx}_topic_statistics.last_message_date),
              {$prfx}_topic_statistics.last_message_id = isnull(plast.last_message_id, {$prfx}_topic_statistics.last_message_id)
            from
              {$prfx}_topic_statistics
            left join
              (select top 1 topic_id, creation_date last_message_date, id last_message_id
               from {$prfx}_post where topic_id = $tid and deleted <> 1
               order by id desc 
              ) plast
            on ({$prfx}_topic_statistics.topic_id = plast.topic_id)
            left join
              (select topic_id, count(*) cnt
               from {$prfx}_post where topic_id = $tid
               group by topic_id
              ) ptcnt
            on ({$prfx}_topic_statistics.topic_id = ptcnt.topic_id)
            left join
              (select topic_id, count(*) cnt
               from {$prfx}_post where topic_id = $tid
               and deleted = 1
               group by topic_id
              ) pdcnt
            on ({$prfx}_topic_statistics.topic_id = pdcnt.topic_id)
            where {$prfx}_topic_statistics.topic_id = $tid";
    } // get_query_post_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_user_post_cnt_update($prfx, $uid)
    {
        return "update
              {$prfx}_user_statistics
            set
              {$prfx}_user_statistics.post_count = isnull(user_post_count.cnt, 0),
              {$prfx}_user_statistics.topic_count = isnull(user_topic_count.cnt, 0)
            from
              {$prfx}_user_statistics
            left join
            (select user_id, count(*) cnt
             from {$prfx}_post  
             where topic_id not in (select id from {$prfx}_topic where is_private > 0) and 
             {$prfx}_post.user_id = $uid
             group by user_id) user_post_count
            on ({$prfx}_user_statistics.user_id = user_post_count.user_id)
            left join
            (select user_id, count(*) cnt
             from {$prfx}_topic 
             where is_private < 1 and 
             {$prfx}_topic.user_id = $uid
             group by user_id) user_topic_count
            on ({$prfx}_user_statistics.user_id = user_topic_count.user_id)
            where {$prfx}_user_statistics.user_id = $uid";
    } // get_query_user_post_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_user_rate_cnt_update($prfx, $uid)
    {
        return "update
              {$prfx}_user_statistics
            set
              {$prfx}_user_statistics.like_count = isnull(user_like_count.cnt, 0),
              {$prfx}_user_statistics.dislike_count = isnull(user_dislike_count.cnt, 0)
            from
              {$prfx}_user_statistics
            left join
              (select {$prfx}_post.user_id, count(*) cnt
               from {$prfx}_post 
               inner join {$prfx}_post_rating on ({$prfx}_post.id = {$prfx}_post_rating.post_id and rating = 1 and rater_ignored <> 1)
               where {$prfx}_post.user_id = $uid
               group by {$prfx}_post.user_id
              ) user_like_count
            on ({$prfx}_user_statistics.user_id = user_like_count.user_id)
            left join
              (select {$prfx}_post.user_id, count(*) cnt
               from {$prfx}_post 
               inner join {$prfx}_post_rating on ({$prfx}_post.id = {$prfx}_post_rating.post_id and rating = -1 and rater_ignored <> 1)
               where {$prfx}_post.user_id = $uid
               group by {$prfx}_post.user_id
              ) user_dislike_count
            on ({$prfx}_user_statistics.user_id = user_dislike_count.user_id)
            where {$prfx}_user_statistics.user_id = $uid";
    } // get_query_user_rate_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_post_rate_cnt_update($prfx, $pid)
    {
        return "update
              {$prfx}_post_statistics
            set
              {$prfx}_post_statistics.like_count = isnull(cnt.likes, 0),
              {$prfx}_post_statistics.dislike_count = isnull(cnt.dislikes, 0)
            from
              {$prfx}_post_statistics
            left join (
                          select {$prfx}_post.id post_id,
                          sum(case when rating = 1 then 1 else 0 end) likes,
                          sum(case when rating = -1 then 1 else 0 end) dislikes
                          from {$prfx}_post
                          inner join {$prfx}_post_rating on ({$prfx}_post.id = {$prfx}_post_rating.post_id)
                          where {$prfx}_post.id = $pid
                          group by {$prfx}_post.id
                       ) cnt
            on ({$prfx}_post_statistics.post_id = cnt.post_id)
            where {$prfx}_post_statistics.post_id = $pid";
    } // get_query_user_rate_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_moderator_events($prfx, $where, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"] + 1;
        $end = $begin + $pagination_info["rows_per_page"] - 1;
        
        return "select
            id, event_time, moderator_name, moderator_id, action, action_expires, author_name, author_id,
            ip, post_id, topic_name, topic_id, forum_name, forum_id, comment,
            last_visit_date, logout, ip_blocked, block_expires,
            author_last_visit_date, author_logout
            from
           (select
            row_number() over (order by {$prfx}_moderator_log.id desc) nr,
             {$prfx}_moderator_log.id, event_time, moderator_name, moderator_id, action, action_expires, author_name, author_id,
             {$prfx}_moderator_log.ip, post_id, topic_name, topic_id, forum_name, forum_id, comment,
             {$prfx}_user.last_visit_date, {$prfx}_user.logout, {$prfx}_ip_blocked.ip ip_blocked, {$prfx}_ip_blocked.block_expires,
             author.last_visit_date author_last_visit_date, author.logout author_logout
             from {$prfx}_moderator_log
             left join {$prfx}_forum on ({$prfx}_moderator_log.forum_id = {$prfx}_forum.id)
             left join {$prfx}_user on ({$prfx}_moderator_log.moderator_id = {$prfx}_user.id)
             left join {$prfx}_user author on ({$prfx}_moderator_log.author_id = author.id)
             left join {$prfx}_ip_blocked on ({$prfx}_ip_blocked.ip = {$prfx}_moderator_log.ip)
             $where
           ) topics
           where nr between $begin and $end
           order by nr";
    } // get_query_moderator_events
    
    //-----------------------------------------------------------------
    function get_query_subscribed_messages(&$dbw, $prfx, $uid, $mindate)
    {
        $forum_restriction_appendix = $this->get_forum_restriction_appendix($dbw, $prfx);
        if (!empty($forum_restriction_appendix)) {
            $forum_restriction_appendix = " and " . $forum_restriction_appendix;
        }
        
        $ignore_forum_where_appendix = $this->get_ignore_forum_where_appendix($dbw, $prfx);
        $ignore_topic_where_appendix = $this->get_ignore_topic_where_appendix($dbw, $prfx, 1);
        
        return "select isnull({$prfx}_user.user_name, {$prfx}_post.author) author, count(*) cnt
                from {$prfx}_post
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                inner join {$prfx}_user_subscription
                on ({$prfx}_user_subscription.user_id = $uid and ({$prfx}_post.user_id = {$prfx}_user_subscription.subscribed_user_id or {$prfx}_post.author = {$prfx}_user_subscription.subscribed_user_name))
                left join {$prfx}_user on ({$prfx}_user_subscription.subscribed_user_id = {$prfx}_user.id)
                where {$prfx}_post.creation_date >= '$mindate' and {$prfx}_post.creation_date > {$prfx}_user_subscription.last_view
                and publish_delay <> 1 and {$prfx}_post.deleted <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_forum.deleted <> 1
                and ({$prfx}_topic.is_private < 1 or ({$prfx}_topic.is_private = 2 and {$prfx}_topic.id in (select {$prfx}_private_topics.topic_id from {$prfx}_private_topics where {$prfx}_private_topics.participant_id = $uid)))
                $forum_restriction_appendix
                $ignore_forum_where_appendix
                $ignore_topic_where_appendix
                group by isnull({$prfx}_user.user_name, {$prfx}_post.author)";
    } // get_query_subscribed_messages
    
    //-----------------------------------------------------------------
    function get_query_subscribed_topics(&$dbw, $prfx, $uid, $mindate)
    {
        $forum_restriction_appendix = $this->get_forum_restriction_appendix($dbw, $prfx);
        if (!empty($forum_restriction_appendix)) {
            $forum_restriction_appendix = " and " . $forum_restriction_appendix;
        }
        
        $ignore_forum_where_appendix = $this->get_ignore_forum_where_appendix($dbw, $prfx);
        $ignore_topic_where_appendix = $this->get_ignore_topic_where_appendix($dbw, $prfx, 1);
        
        return "select isnull({$prfx}_user.user_name, {$prfx}_topic.author) author, count(*) cnt
                from {$prfx}_topic
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                inner join {$prfx}_user_subscription
                  on ({$prfx}_user_subscription.user_id = $uid and ({$prfx}_topic.user_id = {$prfx}_user_subscription.subscribed_user_id or {$prfx}_topic.author = {$prfx}_user_subscription.subscribed_user_name))
                left join {$prfx}_user on ({$prfx}_user_subscription.subscribed_user_id = {$prfx}_user.id)
                where {$prfx}_topic.creation_date >= '$mindate' and {$prfx}_topic.creation_date > {$prfx}_user_subscription.last_view
                and publish_delay <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_forum.deleted <> 1
                and ({$prfx}_topic.is_private < 1 or ({$prfx}_topic.is_private = 2 and {$prfx}_topic.id in (select {$prfx}_private_topics.topic_id from {$prfx}_private_topics where {$prfx}_private_topics.participant_id = $uid)))
                $forum_restriction_appendix
                $ignore_forum_where_appendix
                $ignore_topic_where_appendix
                group by isnull({$prfx}_user.user_name, {$prfx}_topic.author)";
    } // get_query_subscribed_topics
    
    //-----------------------------------------------------------------
    function get_query_subscribed_authors($prfx, $uid, $mindate)
    {
        return "select subscribed_user_id, subscribed_user_name, user_name, last_visit_date, {$prfx}_user.last_post_date user_last_post_date, logout, tm, last_view,
                (select max(creation_date) from {$prfx}_post where ({$prfx}_post.user_id = {$prfx}_user_subscription.subscribed_user_id or {$prfx}_post.author = {$prfx}_user_subscription.subscribed_user_name) and creation_date > '$mindate') guest_last_post_date
                from {$prfx}_user_subscription
                left join {$prfx}_user on ({$prfx}_user_subscription.subscribed_user_id = {$prfx}_user.id)
                where {$prfx}_user_subscription.user_id = $uid
                order by isnull((select max(creation_date) from {$prfx}_post where ({$prfx}_post.user_id = {$prfx}_user_subscription.subscribed_user_id or {$prfx}_post.author = {$prfx}_user_subscription.subscribed_user_name) and creation_date > '$mindate'), {$prfx}_user.last_post_date) desc, last_view desc";
    } // get_query_subscribed_authors
    
    //-----------------------------------------------------------------
    function get_query_fill_digest_posts($dbw, $prfx, $session_id, $now, $search_hash, $uid, $rm, $fid, $private_fid)
    {
        $new_tracking_period = defined('NEW_TRACKING_PERIOD') ? NEW_TRACKING_PERIOD : 30;
        $mindate = $dbw->format_datetime(time() - $new_tracking_period * 24 * 3600);
        
        $ignore_forum_where_appendix = $this->get_ignore_forum_where_appendix($dbw, $prfx);
        
        $ignore_topic_where_appendix = $this->get_ignore_topic_where_appendix($dbw, $prfx, 1);
        
        $ignore_post_where_appendix = $this->get_ignore_post_where_appendix($dbw, $prfx, 1);
        
        $forum_restriction_appendix = $this->get_forum_restriction_appendix($dbw, $prfx);
        if (!empty($forum_restriction_appendix)) {
            $forum_restriction_appendix = " and " . $forum_restriction_appendix;
        }
        
        $private_appendix = " and {$prfx}_topic.is_private < 1";
        
        $topic_appendix = "";
        if ($fid == -1 || $fid == "favourites") {
            if (empty($_SESSION["favourite_topics"])) {
                $favourite_topics_in_list = "-1";
            } else {
                $favourite_topics_in_list = $dbw->escape(implode(",", $_SESSION["favourite_topics"]));
            }
            
            $topic_appendix .= " and topic_id in ($favourite_topics_in_list)";
        } elseif ($fid == -2 || $fid == "my_topics") {
            $topic_appendix .= " and user_id = $uid";
        } elseif ($fid == -3 || $fid == "my_part_topics") {
            $topic_appendix .= " and (user_id is NULL or user_id <> $uid) and topic_id in (select topic_id from {$prfx}_topic_participants where user_id = $uid)";
        } elseif ($fid == "private" || $fid == $private_fid) {
            $private_appendix = " and {$prfx}_topic.id in (select {$prfx}_private_topics.topic_id from {$prfx}_private_topics where {$prfx}_private_topics.participant_id = $uid)";
        } elseif (!empty($fid) && is_numeric($fid)) {
            $topic_appendix .= " and forum_id = $fid";
        }
        
        // We do not use get_new_where_appendix because this expludes own posts.
        // But we want to see our posts in the chain of communication in the digest.

        return "insert into {$prfx}_found_post_cache (post_id, topic_id, session_id, dt, search_hash)
                              select id, topic_id, session_id, dt, search_hash
                              from
                              (
                                  select {$prfx}_post.id, {$prfx}_post.topic_id, '$session_id' session_id, '$now' dt, '$search_hash' search_hash,
                                  {$prfx}_topic.user_id, {$prfx}_topic.forum_id, {$prfx}_topic_statistics.last_message_id,
                                  row_number() over (partition by {$prfx}_post.topic_id order by {$prfx}_post.id) nr
                                  from {$prfx}_post
                                  inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                                  inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
                                  inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
    
                                  left join {$prfx}_topic_read_markers on ({$prfx}_topic.id = {$prfx}_topic_read_markers.topic_id and {$prfx}_topic_read_markers.read_marker = '$rm')
                                  left join {$prfx}_forum_read_markers on ({$prfx}_topic.forum_id = {$prfx}_forum_read_markers.forum_id and {$prfx}_forum_read_markers.read_marker = '$rm')
                            
                                  where
                            
                                  {$prfx}_post.deleted <> 1 and {$prfx}_forum.deleted <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_topic.publish_delay <> 1
                            
                                  $forum_restriction_appendix
                            
                                  $private_appendix
                                  
                                  and {$prfx}_post.creation_date > '$mindate'
                                  and {$prfx}_post.creation_date > isnull({$prfx}_topic_read_markers.last_read_date, {$prfx}_forum_read_markers.first_read_date)
                            
                                  $ignore_forum_where_appendix

                                  $ignore_topic_where_appendix
                            
                                  $ignore_post_where_appendix
                              ) srch
                              where nr <= 5 $topic_appendix
                              order by last_message_id desc
                             ";
    } // get_query_fill_digest_posts
    
    //-----------------------------------------------------------------
    function get_query_fill_search_posts($prfx, $session_id, $now, $search_hash, $topic_part_where, $post_part_where, $max_search_results, $order_by, &$hints)
    {
        return "insert into {$prfx}_found_post_cache (post_id, topic_id, session_id, dt, search_hash)
                        select
                        {$prfx}_post.id, {$prfx}_post.topic_id, '$session_id', '$now', '$search_hash'
                        from {$prfx}_post
                        left join {$prfx}_post_statistics on ({$prfx}_post.id = {$prfx}_post_statistics.post_id)
                        inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                        inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
                        inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                        
                        where
                        {$prfx}_topic.publish_delay <> 1
                        $topic_part_where
                        $post_part_where
                        $order_by
                        ";
    } // get_query_fill_search_posts
    
    //-----------------------------------------------------------------
    function get_query_fill_search_topics($prfx, $session_id, $now, $search_hash, $topic_where, $delayed_clause, $max_search_results)
    {
        return "insert into {$prfx}_found_topic_cache (topic_id, session_id, dt, search_hash)
              select 
              top $max_search_results
              {$prfx}_topic.id, '$session_id', '$now', '$search_hash'
              from {$prfx}_topic
              inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
              inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
              where
              $delayed_clause
              $topic_where
              order by {$prfx}_topic.creation_date desc
             ";
    } // get_query_fill_search_topics
    
    //-----------------------------------------------------------------
    function get_query_found_topics($prfx, $current_uid, $session_id, $hash, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"] + 1;
        $end = $begin + $pagination_info["rows_per_page"] - 1;
        
        return "select
            topics.id, name, creation_date,
            last_message_date, post_count, post_count_total,
            hits_count, bot_hits_count, profiled_topic, deleted, forum_deleted, closed, pinned, has_pinned_post, {$prfx}_pinned_topics.topic_id user_pinned,
            topics.user_id, author, topics.read_marker, {$prfx}_user.user_name,
            {$prfx}_user.last_visit_date, {$prfx}_user.logout,
            forum_id, forum_name,
            is_poll, publish_delay, no_guests
            from
           (select
            row_number() over (order by {$prfx}_topic.publish_delay desc, {$prfx}_topic_statistics.last_message_date desc, {$prfx}_topic.id desc) nr,
            {$prfx}_topic.id, {$prfx}_topic.name, {$prfx}_topic.creation_date,
            {$prfx}_topic_statistics.last_message_date, post_count, post_count_total,
            hits_count, bot_hits_count, {$prfx}_topic.profiled_topic, {$prfx}_topic.deleted, {$prfx}_topic.closed, {$prfx}_topic.pinned, has_pinned_post,
            {$prfx}_forum.deleted forum_deleted,
            {$prfx}_topic.user_id, {$prfx}_topic.author, {$prfx}_topic.read_marker,
            forum_id, {$prfx}_forum.name forum_name,
            is_poll, publish_delay, {$prfx}_topic.no_guests
            from {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            inner join {$prfx}_found_topic_cache on ({$prfx}_topic.id = {$prfx}_found_topic_cache.topic_id)
            inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
            where
            session_id = '$session_id' and search_hash = '$hash'
           ) topics
           left join {$prfx}_pinned_topics on (topics.id = {$prfx}_pinned_topics.topic_id and {$prfx}_pinned_topics.user_id = $current_uid)
           left join {$prfx}_user on (topics.user_id = {$prfx}_user.id)
           where nr between $begin and $end
           order by nr";
    } // get_query_found_topics
    
    //-----------------------------------------------------------------
    function get_topic_search_clause(&$dbw, $prfx, $search_keys, $with_morphology)
    {
        $where_clause = "";
        
        $search_keys = utf8_trim($search_keys);
        
        if (!empty($search_keys)) {
            // search by exact phrase, e.g "exact revolution"
            if (preg_match('/^"(.+)"$/iu', $search_keys, $matches)) {
                $search_clause = '"' . str_replace('"', '""', $dbw->escape(trim($matches[1]))) . '"';
                $where_clause .= "contains({$prfx}_topic.name, '" . $search_clause . "')";
                return $where_clause;
            }
            
            $search_keys = preg_replace("/\s+(или|or|oder)\s+/iu", " | ", $search_keys);
            $search_keys = preg_replace("/\s+(и|and|und)\s+/iu", " & ", $search_keys);
            
            $search_clause = "";
            
            $skey_array = preg_split("/\s*([\|\&])\s*/iu", $search_keys, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE);
            
            // if the union is not explicitly specifled, AND is implied
            
            if (count($skey_array) == 1) {
                $search_clause = "";
                $skey_array = preg_split("/\s+/iu", $search_keys, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($skey_array as $skey) {
                    $skey = utf8_trim($skey);
                    
                    if (!empty($search_clause)) {
                        $search_clause .= " & ";
                    }
                    
                    $words = array();
                    if ($with_morphology && $this->get_unicore_words($skey, $words)) {
                        $search_clause .= '(';
                        $first = true;
                        
                        foreach ($words as $word) {
                            if ($first) {
                                $first = false;
                            } else {
                                $search_clause .= ' | ';
                            }
                            
                            $search_clause .= '"' . str_replace('"', '""', $dbw->escape($word)) . '"';
                        }
                        $search_clause .= ')';
                    } else {
                        $search_clause .= '"' . str_replace('"', '""', $dbw->escape($skey)) . '"';
                    }
                }
            } else {
                foreach ($skey_array as $skey) {
                    $skey = utf8_trim($skey);
                    
                    if ($skey == "|" || $skey == "&") {
                        $search_clause .= " $skey ";
                        continue;
                    }
                    
                    $words = array();
                    if ($with_morphology && $this->get_unicore_words($skey, $words)) {
                        $search_clause .= '(';
                        foreach ($words as $word) {
                            $search_clause .= ' | "' . str_replace('"', '""', $dbw->escape($word)) . '"';
                        }
                        $search_clause .= ')';
                    } else {
                        $search_clause .= '"' . str_replace('"', '""', $dbw->escape($skey)) . '"';
                    }
                }
            }
            
            $search_clause = trim($search_clause, "|& ");
            
            $where_clause .= "contains({$prfx}_topic.name, N'" . $search_clause . "')";
        } // search_keys
        
        return $where_clause;
    } // get_topic_search_clause
    
    //-----------------------------------------------------------------
    function get_post_search_clause(&$dbw, $prfx, $search_keys, $with_morphology)
    {
        $where_clause = "";
        
        $search_keys = utf8_trim($search_keys);
        
        if (!empty($search_keys)) {
            // search by exact phrase, e.g "exact revolution"
            if (preg_match('/^"(.+)"$/iu', $search_keys, $matches)) {
                $search_clause = '"' . str_replace('"', '""', $dbw->escape(trim($matches[1]))) . '"';
                $where_clause .= "contains(searchable_content, '" . $search_clause . "')";
                return $where_clause;
            }
            
            $search_keys = preg_replace("/\s+(или|or|oder)\s+/iu", " | ", $search_keys);
            $search_keys = preg_replace("/\s+(и|and|und)\s+/iu", " & ", $search_keys);
            
            $search_clause = "";
            
            $skey_array = preg_split("/\s*([\|\&])\s*/iu", $search_keys, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE);
            
            // if the union is not explicitly specifled, AND is implied
            
            if (count($skey_array) == 1) {
                $search_clause = "";
                $skey_array = preg_split("/\s+/iu", $search_keys, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($skey_array as $skey) {
                    $skey = utf8_trim($skey);
                    
                    if (!empty($search_clause)) {
                        $search_clause .= " & ";
                    }
                    
                    $words = array();
                    if ($with_morphology && $this->get_unicore_words($skey, $words)) {
                        $search_clause .= '(';
                        $first = true;
                        
                        foreach ($words as $word) {
                            if ($first) {
                                $first = false;
                            } else {
                                $search_clause .= ' | ';
                            }
                            
                            $search_clause .= '"' . str_replace('"', '""', $dbw->escape($word)) . '"';
                        }
                        $search_clause .= ')';
                    } else {
                        $search_clause .= '"' . str_replace('"', '""', $dbw->escape($skey)) . '"';
                    }
                }
            } else {
                foreach ($skey_array as $skey) {
                    $skey = utf8_trim($skey);
                    
                    if ($skey == "|" || $skey == "&") {
                        $search_clause .= " $skey ";
                        continue;
                    }
                    
                    $words = array();
                    if ($with_morphology && $this->get_unicore_words($skey, $words)) {
                        $search_clause .= '(';
                        foreach ($words as $word) {
                            $search_clause .= ' | "' . str_replace('"', '""', $dbw->escape($word)) . '"';
                        }
                        $search_clause .= ')';
                    } else {
                        $search_clause .= '"' . str_replace('"', '""', $dbw->escape($skey)) . '"';
                    }
                }
            }
            
            $search_clause = trim($search_clause, "|& ");
            
            $where_clause .= "contains(searchable_content, N'" . $search_clause . "')";
        } // search_keys
        
        return $where_clause;
    } // get_post_search_clause

    //-----------------------------------------------------------------
    function get_query_event_list($prfx, $where, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"] + 1;
        $end = $begin + $pagination_info["rows_per_page"] - 1;
        
        return "select id, event_time, event_code, is_new, todo, params, author_name, author_id,
             last_visit_date, logout from
            (select 
             row_number() over(order by {$prfx}_events.id desc) nr,
             {$prfx}_events.id, convert(varchar(20), event_time, 120) event_time,
             event_code, is_new, todo, params, author_name, author_id,
             last_visit_date, logout
             from {$prfx}_events
             left join {$prfx}_user on ({$prfx}_events.author_id = {$prfx}_user.id)
             $where) events
             where nr between $begin and $end
             order by nr
           ";
    } // get_query_event_list
    
    //-----------------------------------------------------------------
    function get_query_last_n_posts($n, $prfx, $forum_id, $author_id, $author_rm)
    {
        return "select
            top $n
            {$prfx}_post.id
            from {$prfx}_post
            inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
            where
            {$prfx}_topic.forum_id = $forum_id
            and {$prfx}_post.deleted <> 1 and {$prfx}_topic.is_private < 1
            and ({$prfx}_post.user_id = $author_id or ({$prfx}_post.read_marker = $author_rm and {$prfx}_post.user_id is NULL))
            order by {$prfx}_post.creation_date desc, {$prfx}_post.id desc
            ";
    } // get_query_last_n_posts
    
    //-----------------------------------------------------------------
    function get_query_last_N_rates($prfx, $n, $rater_id, $author_id, $moderator_restriction, $rate_type)
    {
        return "select top $n
            {$prfx}_post_rating.id, {$prfx}_post_rating.post_id
            from {$prfx}_post_rating
            inner join {$prfx}_post on ({$prfx}_post.id = {$prfx}_post_rating.post_id)
            inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
            where {$prfx}_post_rating.user_id = $rater_id and rating = $rate_type and {$prfx}_post.user_id = $author_id
            $moderator_restriction
            order by {$prfx}_post_rating.dt desc
            ";
    } // get_query_last_N_rates
    
    //-----------------------------------------------------------------
    function get_query_read_topics($prfx, $uid, $forum_appendix)
    {
        return "select
             top 200
             {$prfx}_topic.id, forum_id, {$prfx}_topic.name, {$prfx}_forum.name forum_name, {$prfx}_topic_view_history.dt
             from {$prfx}_topic
             inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
             inner join {$prfx}_topic_view_history on ({$prfx}_topic.id = {$prfx}_topic_view_history.topic_id)
             where
             {$prfx}_topic_view_history.user_id = $uid and
             {$prfx}_forum.deleted <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_topic.publish_delay <> 1 and {$prfx}_topic.is_private < 1
             $forum_appendix
             order by {$prfx}_topic_view_history.dt desc
             ";
    } // get_query_read_topics
    
    //-----------------------------------------------------------------
    function get_query_guest_last_activity($prfx, $guest)
    {
        return "select top 1 dt, ip from {$prfx}_topic_view_history where guest_name = '$guest' order by dt desc";
    } // get_query_guest_last_activity

    //-----------------------------------------------------------------
    function get_query_guest_read_topics($prfx, $guest, $forum_appendix)
    {
        return "select
             top 200
             {$prfx}_topic.id, forum_id, {$prfx}_topic.name, {$prfx}_forum.name forum_name, {$prfx}_topic_view_history.dt
             from {$prfx}_topic
             inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
             inner join {$prfx}_topic_view_history on ({$prfx}_topic.id = {$prfx}_topic_view_history.topic_id)
             where
             {$prfx}_topic_view_history.guest_name = $guest and
             {$prfx}_forum.deleted <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_topic.publish_delay <> 1 and {$prfx}_topic.is_private < 1
             $forum_appendix
             order by {$prfx}_topic_view_history.dt desc
             ";
    } // get_query_guest_read_topics
    
   //-----------------------------------------------------------------
    function get_query_ignored_posts_list($prfx, $where)
    {
        return "select top 1 1
               from 
               {$prfx}_post
               inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
               $where";
    } // get_query_ignored_posts_list
    
    //-----------------------------------------------------------------
    function get_query_post_count($prfx, $where)
    {
        return "select
                user_name, {$prfx}_post.user_id, isnull(convert(varchar(max), {$prfx}_post.user_id), {$prfx}_post.read_marker) uid, {$prfx}_user.registration_date,
                count(*) cnt
                from {$prfx}_post
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                left join {$prfx}_user on ({$prfx}_post.user_id = {$prfx}_user.id)
                $where
                group by
                user_name, {$prfx}_post.user_id, isnull(convert(varchar(max), {$prfx}_post.user_id), {$prfx}_post.read_marker), {$prfx}_user.registration_date
                order by count(*) desc
                ";
    } // get_query_post_count
    
    //-----------------------------------------------------------------
    function get_query_load_attachments($prfx, $uid, $current_appendex)
    {
        return "select top 100 {$prfx}_attachment.id, {$prfx}_attachment.post_id,
                                  {$prfx}_attachment.last_post_id, nr, {$prfx}_attachment.favourite,
                                  {$prfx}_attachment.name file_name,
                                  {$prfx}_post.topic_id, {$prfx}_topic.forum_id, {$prfx}_forum.name,
                                  year(last_usage.creation_date) year,
                                  month(last_usage.creation_date) month
                             from {$prfx}_attachment
                             inner join {$prfx}_post on ({$prfx}_attachment.post_id = {$prfx}_post.id)
                             inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                             inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                             left join {$prfx}_post last_usage on ({$prfx}_attachment.last_post_id = last_usage.id)
                             where
                             {$prfx}_attachment.id in
                                 (select max({$prfx}_attachment.id)
                                  from {$prfx}_attachment
                                  inner join {$prfx}_post on ({$prfx}_attachment.post_id = {$prfx}_post.id)
                                  inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                                  inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                                  where
                                  {$prfx}_attachment.user_id = $uid and
                                  type in ('image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp') and
                                  {$prfx}_attachment.deleted <> 1 and {$prfx}_post.deleted <> 1 and
                                  {$prfx}_topic.deleted <> 1 and {$prfx}_forum.deleted <> 1 and {$prfx}_topic.is_private < 1
                                  $current_appendex
                                  group by {$prfx}_topic.forum_id, {$prfx}_attachment.name)
                             
                             order by {$prfx}_attachment.favourite desc, {$prfx}_attachment.last_post_id desc, {$prfx}_attachment.id desc
                             ";
    } // get_query_load_attachments
    
    //-----------------------------------------------------------------
    function get_query_clear_viewed_topics($prfx)
    {
        return "select user_id,
                
                (select min(dt) from
                (select top 300 max(dt) dt from {$prfx}_topic_view_history
                where user_id = users.user_id
                group by topic_id
                order by max(dt) desc) topics) min_dt
                
                from
                (select distinct user_id from {$prfx}_topic_view_history where user_id is not NULL) users";
    } // get_query_clear_viewed_topics
    
    //-----------------------------------------------------------------
    function get_query_clear_guest_viewed_topics($prfx)
    {
        return "select guest_name,
                
                (select min(dt) from
                (select top 300 max(dt) dt from {$prfx}_topic_view_history
                where guest_name = guests.guest_name
                group by topic_id
                order by max(dt) desc) topics) min_dt
                
                from
                (select distinct guest_name from {$prfx}_topic_view_history where guest_name is not NULL) guests";
    } // get_query_clear_guest_viewed_topics

    //-----------------------------------------------------------------
    function get_query_user_hour_hits($prfx, $where, $hour_offset)
    {
        return "select datepart(hour, dateadd(second, $hour_offset, dt)) hour, sum(hits_count) hits_count
                from {$prfx}_forum_hits
                $where
                group by datepart(hour, dateadd(second, $hour_offset, dt))
                order by datepart(hour, dateadd(second, $hour_offset, dt))";
    } // get_query_user_hour_hits
    
    //-----------------------------------------------------------------
    function get_query_user_hour_posts($prfx, $where, $hour_offset)
    {
        return "select datepart(hour, dateadd(second, $hour_offset, creation_date)) hour, count(*) post_count
                from {$prfx}_post
                $where
                group by datepart(hour, dateadd(second, $hour_offset, creation_date))
                order by datepart(hour, dateadd(second, $hour_offset, creation_date))
               ";
    } // get_query_user_hour_posts
    
    //-----------------------------------------------------------------
    function gen_load_statistics(&$user_activity, &$ip_activity, &$agent_activity, &$total_user_hits_count, &$total_ip_hits_count, &$total_agents_hits_count)
    {
        global $settings;
        
        start_action_time_measure();
        
        $rodbw = System::getRODBWorker();
        if (!$rodbw) {
            return false;
        }
        
        $prfx = $rodbw->escape(System::getDBPrefix());
        
        $now_rounded = mktime(date("H"), date("i"), 0, date("n"), date("j"), date("Y"));
        
        switch (val_or_empty($_SESSION["load_activity_period"])) {
            case "last_10_minutes":
                $start_date = xstrtotime("-10 minutes", $now_rounded);
                break;
            case "last_hour":
                $start_date = xstrtotime("-1 hour", $now_rounded);
                break;
            case "last_day":
                $start_date = xstrtotime("-1 day", $now_rounded);
                break;
            case "last_week":
                $start_date = xstrtotime("-7 days", $now_rounded);
                break;
            default:
                $start_date = xstrtotime("-10 minutes", $now_rounded);
                break;
        }
        
        $now = $rodbw->format_datetime(time());
        $start_date = $rodbw->format_datetime($start_date);
        
        if (!$rodbw->execute_query("select top 20 user_id, guest_name, last_visit_date, logout, count(*) cnt
                             from
                             {$prfx}_forum_hits
                             left join {$prfx}_user on ({$prfx}_forum_hits.user_id = {$prfx}_user.id)
                             where dt >= '$start_date'
                             group by user_id, guest_name, last_visit_date, logout
                             order by cnt desc")) {
            MessageHandler::setError(text("ErrQueryFailed"),
                $rodbw->get_last_error() . "\n\n" .
                $rodbw->get_last_query()
            );
            return false;
        }
        
        $keep_online_period = defined('KEEP_ONLINE_PERIOD') ? KEEP_ONLINE_PERIOD : 600;

        $total_user_hits_count = 0;
        while ($rodbw->fetch_row()) {
            $name = $rodbw->field_by_name("guest_name");
            if (empty($name)) {
                $name = text("Anonymous");
            }
            
            $total_user_hits_count += $rodbw->field_by_name("cnt");
            $user_activity[] = array(
                "id" => $rodbw->field_by_name("user_id"),
                "user_name" => $name,
                "cnt" => $rodbw->field_by_name("cnt"),
                "online" => (xstrtotime($rodbw->field_by_name("last_visit_date")) > (time() - $keep_online_period) && $rodbw->field_by_name("logout") == 0)
            );
        }
        
        $rodbw->free_result();
        
        // IPs
    
        $ip_rules = array();
        $ips = "";
        $matched_rule = "";
        $this->get_white_list_ips($ips, $ip_rules);
        
        if (!$rodbw->execute_query("select top 20 {$prfx}_forum_hits.ip, {$prfx}_ip_blocked.ip blocked, count(*) cnt
                             from
                             {$prfx}_forum_hits
                             left join {$prfx}_ip_blocked on ({$prfx}_forum_hits.ip = {$prfx}_ip_blocked.ip and (block_expires is NULL or block_expires > '$now'))
                             where dt >= '$start_date'
                             group by {$prfx}_forum_hits.ip, {$prfx}_ip_blocked.ip
                             order by cnt desc")) {
            MessageHandler::setError(text("ErrQueryFailed"),
                $rodbw->get_last_error() . "\n\n" .
                $rodbw->get_last_query()
            );
            return false;
        }
        
        $total_ip_hits_count = 0;
        while ($rodbw->fetch_row()) {
            $ip = $rodbw->field_by_name("ip");

            $total_ip_hits_count += $rodbw->field_by_name("cnt");
            $ip_activity[] = array(
                "ip" => $rodbw->field_by_name("ip"),
                "guest_ip_whitelisted" => $this->is_ip_whitelisted($ip, $ip_rules, $matched_rule) ? 1 : 0,
                "cnt" => $rodbw->field_by_name("cnt"),
                "blocked" => $rodbw->field_by_name("blocked") ? true : false
            );
        }
        
        $rodbw->free_result();
        
        if (!empty($ip_activity)) {
            $in_list = "'" . implode("','", array_keys($ip_activity)) . "'";
            
            if (!$rodbw->execute_query("select ip, block_level from {$prfx}_tor_ips where ip in ($in_list)")) {
                MessageHandler::setError(text("ErrQueryFailed"), $rodbw->get_last_error() . "\n\n" . $rodbw->get_last_query());
                return false;
            }
            
            while ($rodbw->fetch_row()) {
                $ip = $rodbw->field_by_name("ip");
                $ip_activity[$ip]["tor_ip"] = 1;
                
                $ip_activity[$ip]["tor_ip_block_level"] = "tor_allow";
                if (!empty($settings["block_tor_ips"])) {
                    $ip_activity[$ip]["tor_ip_block_level"] = "tor_block_write";
                }
                
                switch ($rodbw->field_by_name("block_level")) {
                    case 1:
                        $ip_activity[$ip]["tor_ip_block_level"] = "tor_block_write";
                        break;
                    
                    case 2:
                        $ip_activity[$ip]["tor_ip_block_level"] = "tor_block_read";
                        break;
                    
                    case 3:
                        $ip_activity[$ip]["tor_ip_block_level"] = "tor_allow";
                        break;
                }
            }
            
            $rodbw->free_result();
        }
        
        // user agents
        
        if (!$rodbw->execute_query("select top 40 {$prfx}_forum_hits.user_agent, count(*) cnt
                             from
                             {$prfx}_forum_hits
                             where dt >= '$start_date'
                             group by {$prfx}_forum_hits.user_agent
                             order by cnt desc")) {
            MessageHandler::setError(text("ErrQueryFailed"),
                $rodbw->get_last_error() . "\n\n" .
                $rodbw->get_last_query()
            );
            return false;
        }
        
        $total_agents_hits_count = 0;
        while ($rodbw->fetch_row()) {
            $total_agents_hits_count += $rodbw->field_by_name("cnt");
            $agent_activity[] = array(
                "agent" => $rodbw->field_by_name("user_agent"),
                "cnt" => $rodbw->field_by_name("cnt")
            );
        }
        
        $rodbw->free_result();
        
        unset($_SESSION["load_hits"]);
        unset($_SESSION["exec_hits"]);
        unset($_SESSION["exec_times"]);
        unset($_SESSION["topic_rm_count"]);
        unset($_SESSION["forum_rm_count"]);
        
        if (!$rodbw->execute_query("select
                             datepart(minute, dt) mn,
                             datepart(hour, dt) hh,
                             datepart(month, dt) mm,
                             datepart(day, dt) dd,
                             datepart(year, dt) yy,
                             count(*) hits_count,
                             avg(exec_time) exec_time,
                             max(topic_rm_count) topic_rm_count,
                             max(forum_rm_count) forum_rm_count
                             from
                             {$prfx}_load_statistics
                             where dt >= '$start_date' 
                             group by datepart(year, dt), datepart(month, dt), datepart(day, dt), datepart(hour, dt), datepart(minute, dt)
                             order by datepart(year, dt), datepart(month, dt), datepart(day, dt), datepart(hour, dt), datepart(minute, dt)
                            ")) {
            MessageHandler::setError(text("ErrQueryFailed"),
                $rodbw->get_last_error() . "\n\n" .
                $rodbw->get_last_query()
            );
            return false;
        }
        
        while ($rodbw->fetch_row()) {
            $time = mktime($rodbw->field_by_name("hh"), $rodbw->field_by_name("mn"), 0, $rodbw->field_by_name("mm"), $rodbw->field_by_name("dd"), $rodbw->field_by_name("yy"));
            if ($time == $now_rounded) {
                continue;
            } // we do not take the last minute
            
            $_SESSION["exec_hits"][$time] = $rodbw->field_by_name("hits_count");
            $_SESSION["exec_times"][$time] = round($rodbw->field_by_name("exec_time"));
            
            $_SESSION["topic_rm_count"][$time] = round($rodbw->field_by_name("topic_rm_count"));
            $_SESSION["forum_rm_count"][$time] = round($rodbw->field_by_name("forum_rm_count"));
        }
        
        $rodbw->free_result();
        
        if (!$rodbw->execute_query("select
                             datepart(minute, dt) mn,
                             datepart(hour, dt) hh,
                             datepart(month, dt) mm,
                             datepart(day, dt) dd,
                             datepart(year, dt) yy,
                             sum(hits_count) hits_count
                             from
                             {$prfx}_forum_hits
                             where dt >= '$start_date'
                             group by datepart(year, dt), datepart(month, dt), datepart(day, dt), datepart(hour, dt), datepart(minute, dt)
                             order by datepart(year, dt), datepart(month, dt), datepart(day, dt), datepart(hour, dt), datepart(minute, dt)
                            ")) {
            MessageHandler::setError(text("ErrQueryFailed"),
                $rodbw->get_last_error() . "\n\n" .
                $rodbw->get_last_query()
            );
            return false;
        }
        
        while ($rodbw->fetch_row()) {
            $time = mktime($rodbw->field_by_name("hh"), $rodbw->field_by_name("mn"), 0, $rodbw->field_by_name("mm"), $rodbw->field_by_name("dd"), $rodbw->field_by_name("yy"));
            if ($time == $now_rounded) {
                continue;
            } // we do not take the last minute
            
            $_SESSION["load_hits"][$time] = $rodbw->field_by_name("hits_count");
        }
        
        $rodbw->free_result();
        
        if (!empty($_SESSION["load_hits"])) {
            ksort($_SESSION["load_hits"]);
        }
        
        if (!empty($_SESSION["exec_hits"])) {
            ksort($_SESSION["exec_hits"]);
        }
        
        if (!empty($_SESSION["exec_times"])) {
            ksort($_SESSION["exec_times"]);
        }
        
        if (!empty($_SESSION["topic_rm_count"])) {
            ksort($_SESSION["topic_rm_count"]);
        }
        
        if (!empty($_SESSION["forum_rm_count"])) {
            ksort($_SESSION["forum_rm_count"]);
        }
        
        measure_action_time("get load statistics");
        
        return true;
    } // gen_load_statistics
    
    //-----------------------------------------------------------------
    function create_tmp_id_collector_table($dbw, $prfx)
    {
        $query = "if object_id('tempdb..#tmp_id_collector') is NULL create table #tmp_id_collector(id integer)";
        if (!$dbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
            return "";
        }
        
        if (!$dbw->execute_query("delete from #tmp_id_collector")) {
            MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
            return "";
        }

        return "#tmp_id_collector";
    } // create_tmp_id_collector_table

    //-----------------------------------------------------------------
    function get_reply_post_clause($dbw, $prfx, $parent_pid)
    {
        $query = "if object_id('tempdb..#tmp_children') is NULL create table #tmp_children(id integer)";
        if (!$dbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
            return "";
        }
        
        if (!$dbw->execute_procedure("{$prfx}_deep_collect_replies", $parent_pid)) {
            MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
            return "";
        }
        
        return " and exists (select 1 from #tmp_children where {$prfx}_post.id = #tmp_children.id)";
    } // get_reply_post_clause
    
    //-----------------------------------------------------------------
    function get_query_rating_info($prfx, $where)
    {
        return "select sum(like_count) likes, sum(dislike_count) dislikes, sum(like_count + dislike_count) rates
                from {$prfx}_post
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                inner join {$prfx}_post_statistics on ({$prfx}_post.id = {$prfx}_post_statistics.post_id)
                $where";
    } // get_query_rating_info

    //-----------------------------------------------------------------
    function get_query_banned_ips($prfx)
    {
        return "select top 200 ip, atype,
               min(banned_until) first_attack,
               max(banned_until) last_attack,
               avg(hits) hits,
               count(*) cnt
               from {$prfx}_banned_ips
               group by ip, atype
               order by max(banned_until) desc
               ";
    } // get_query_banned_ips
    
    //-----------------------------------------------------------------
    function get_hot_topic_clause($prfx, $start1, $start2)
    {
        $where = "";
        $where .= " and {$prfx}_topic_statistics.last_message_date >= '$start2'" . "\n";
        $where .= " and {$prfx}_topic_statistics.post_count_total >= 100" . "\n";
        $where .= " and (exists (select 1 from {$prfx}_post where {$prfx}_post.topic_id = {$prfx}_topic.id and {$prfx}_post.creation_date >= '$start1' group by topic_id having count(*) >= 15 and count(distinct {$prfx}_post.author) > 2) or 
                         exists (select 1 from {$prfx}_post where {$prfx}_post.topic_id = {$prfx}_topic.id and {$prfx}_post.creation_date >= '$start2' group by topic_id having count(*) >= 100 and count(distinct {$prfx}_post.author) > 2)
                        )" . "\n";
        
        return $where;        
    } // get_hot_topic_clause
} // class MSSQL_ForumManager
?>