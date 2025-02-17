<?php
//-------------------------------------------------------------------
// class MySQL_ForumManager
//-------------------------------------------------------------------
class MySQL_ForumManager extends ForumManager
{
    //-----------------------------------------------------------------
    function get_new_where_appendix($prfx, $rm)
    {
        if (empty($rm)) {
            return " and {$prfx}_post.creation_date > ifnull({$prfx}_topic_read_markers.last_read_date, {$prfx}_forum_read_markers.first_read_date)
                    ";
        }
        
        return " and {$prfx}_post.read_marker <> '$rm'
                 and {$prfx}_post.creation_date > ifnull({$prfx}_topic_read_markers.last_read_date, {$prfx}_forum_read_markers.first_read_date)
                ";
    } // get_new_where_appendix
    
    //-----------------------------------------------------------------
    function get_query_min_topic_post($prfx, $first_post_topic_id, $first_post_id)
    {
        return "select id from {$prfx}_post
                where topic_id = $first_post_topic_id and deleted <> 1
                and id > $first_post_id
                order by id limit 1";
    } // get_query_min_topic_post
    
    //-----------------------------------------------------------------
    function get_query_max_topic_post($prfx, $first_post_topic_id, $first_post_id)
    {
        return "select id from {$prfx}_post
                where topic_id = $first_post_topic_id and deleted <> 1
                and id < $first_post_id
                order by id desc limit 1";    
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
                where (all_cnt.cnt - ifnull(deleted_cnt.cnt, 0)) = 0";
    } // get_query_empty_topic
    
    //-----------------------------------------------------------------
    function get_query_previous_valid_topic_post($prfx, $where)
    {
        return "select {$prfx}_post.id
                from {$prfx}_post
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                $where
                order by {$prfx}_post.id desc
                limit 1
               ";
    } // get_query_previous_valid_topic_post

    //-----------------------------------------------------------------
    function get_query_next_valid_topic_post($prfx, $where)
    {
        return "select {$prfx}_post.id
                from {$prfx}_post
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                $where
                order by {$prfx}_post.id
                limit 1
               ";
    } // get_query_next_valid_topic_post

    //-----------------------------------------------------------------
    function get_query_topic_first_post($prfx, $where, $order_by)
    {
        return "select {$prfx}_post.id, {$prfx}_post.deleted, {$prfx}_post.pinned
               from {$prfx}_post
               inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
               inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
               $where
               $order_by
               limit 1
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
        
        return "select id, user_name
                from {$prfx}_user
                $where
                $master_appendix
                order by user_name
                limit 100";
    } // get_query_user
    
    //-----------------------------------------------------------------
    function get_query_last_guest_activity($prfx, $where)
    {
        return "select dt, ip
                 from {$prfx}_forum_hits
                 $where
                 order by dt desc
                 limit 1";
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
        
        return "select id, name topic_name
            from
            {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            where deleted <> 1 and merged is NULL and ($where) and
            (forum_id in ($in_list) or exists (select 1 from {$prfx}_private_topics where {$prfx}_private_topics.topic_id = {$prfx}_topic.id and participant_id = $uid))
            order by last_message_id desc
            limit 100";
    } // get_query_topic_search
    
    //-----------------------------------------------------------------
    function get_query_existing_topic_search(&$dbw, $prfx, $search, $forum)
    {
        $search = $dbw->escape($search);
        $fid = $dbw->escape($forum);

        $where = " forum_id = $fid and name like '$search%'";
        
        return "select id, name topic_name
            from
            {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            where deleted <> 1 and merged is NULL and is_private < 1 and ($where)
            order by last_message_id desc
            limit 100";
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
        
        return "select id, name topic_name
            from
            {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            where deleted <> 1 and merged is NULL and ($where) and
            forum_id in ($in_list)
            order by last_message_id desc
            limit 100";
    } // get_query_moderated_topic_search
    
    //-----------------------------------------------------------------
    function get_query_blocked_user_list_order_clause($prfx)
    {
        return "blocked desc, ifnull(block_expires, date_add(current_timestamp, interval 1 year)), registration_date desc";
    }
    
    //-----------------------------------------------------------------
    function get_query_user_list($prfx, $start_date, $where, $order_by, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"];
        
        return "select
           id, user_name, activated, approved, privileged, hidden,
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
             
           $where

           order by $order_by
           limit $begin, $pagination_info[rows_per_page]";
    } // get_query_user_list
    
    //-----------------------------------------------------------------
    function get_query_read_marker_list($prfx, $where, $order_by, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"];
        
        return "select
           {$prfx}_read_marker_activity.read_marker, last_activity, first_activity, {$prfx}_read_marker_activity.ip,
           current_name_start, current_name_hits, author, user_agent, hits, last_visit_date, logout,
           {$prfx}_user.id
           from {$prfx}_read_marker_activity
           left join {$prfx}_user on ({$prfx}_read_marker_activity.author = {$prfx}_user.user_name)

           $where
           order by $order_by
           limit $begin, $pagination_info[rows_per_page]";
    } // get_query_read_marker_list
    
    //-----------------------------------------------------------------
    function get_query_user_agent_list($prfx, $where)
    {
        return "select
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
                  order by dt desc
                  limit 1000";
    } // get_query_user_agent_list
    
    //-----------------------------------------------------------------
    function get_query_forum_topics($prfx, $uid, $user_pinned_topic_appendix, $where, &$pagination_info)
    {
        if ($pagination_info["page"] == 1) {
            $begin = 0;
            $limit = $pagination_info["rows_per_page"] - $pagination_info["pinned_count"];
        } else {
            $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"] - $pagination_info["pinned_count"];
            $limit = $pagination_info["rows_per_page"];
        }
        
        $index_hint = "";
        if ($pagination_info["total_count"] > 1000) {
            $index_hint = "use index ({$prfx}_topic_statistics_lmdate_idx)";
        }
        
        return "
          select
          
           {$prfx}_topic.id, {$prfx}_topic.name, {$prfx}_topic.creation_date,
           {$prfx}_topic.last_message_date, 
           {$prfx}_topic.post_count, 
           {$prfx}_topic.post_count_total,
           {$prfx}_topic.hits_count,
           {$prfx}_topic.bot_hits_count,
           {$prfx}_topic.profiled_topic,
           {$prfx}_topic.deleted, {$prfx}_topic.closed, {$prfx}_topic.pinned, {$prfx}_topic.publish_delay, has_pinned_post,
           forum_deleted, disable_ignore,
           {$prfx}_topic.user_id, {$prfx}_topic.author, {$prfx}_topic.read_marker, {$prfx}_user.user_name,
               {$prfx}_user.last_visit_date, {$prfx}_user.logout,
           forum_id, forum_name, is_poll, {$prfx}_topic.no_guests
           
           from
          
          (select 
           {$prfx}_topic.id, {$prfx}_topic.name, {$prfx}_topic.creation_date,
           {$prfx}_topic_statistics.last_message_date, 
           {$prfx}_topic_statistics.post_count, 
           {$prfx}_topic_statistics.post_count_total,
           {$prfx}_topic_statistics.hits_count,
           {$prfx}_topic_statistics.bot_hits_count,
           {$prfx}_topic.profiled_topic,
           {$prfx}_topic.deleted, {$prfx}_topic.closed, {$prfx}_topic.pinned, {$prfx}_topic.publish_delay, has_pinned_post,
           {$prfx}_forum.deleted forum_deleted, disable_ignore,
           {$prfx}_topic.user_id, {$prfx}_topic.author, {$prfx}_topic.read_marker, 
           forum_id, {$prfx}_forum.name forum_name, is_poll, {$prfx}_topic.no_guests

           from {$prfx}_topic
           inner join {$prfx}_topic_statistics {$index_hint}
           on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)

           inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)

           $where and {$prfx}_topic.pinned + {$prfx}_topic.publish_delay = 0 $user_pinned_topic_appendix
           order by last_message_date desc
           limit $begin, $limit) {$prfx}_topic
           left join {$prfx}_user on ({$prfx}_topic.user_id = {$prfx}_user.id)
           ";
    } // get_query_forum_topics
    
    //-----------------------------------------------------------------
    function get_query_paginated_found_posts($prfx, $current_uid, $where, &$pagination_info, $order_by)
    {
        if ($pagination_info["mode"] == "all") {
            $limit = "";
        } else {
            $begin = ($pagination_info["page"] - 1) * $pagination_info["posts_per_page"];
            $limit = "limit $begin, $pagination_info[posts_per_page]";
        }
        
        return "select
            {$prfx}_post.id, {$prfx}_post.creation_date, text_content, html_content,
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
            $where
            $order_by
            $limit";
    } // get_query_paginated_found_posts

    //-----------------------------------------------------------------
    function get_query_topic_posts($prfx, $current_uid, $where, $limit, $order_by)
    {
        if (!empty($limit)) {
            $limit = "limit $limit";
        } else {
            $limit = "";
        }
        
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
            left join {$prfx}_post_rating on ({$prfx}_post_rating.post_id = {$prfx}_post.id and {$prfx}_post_rating.user_id = $current_uid)
            left join {$prfx}_post_statistics on ({$prfx}_post.id = {$prfx}_post_statistics.post_id)
            $where 
            $order_by
            $limit";
    } // get_query_topic_posts

    //-----------------------------------------------------------------
    function get_query_topic_cnt_update($prfx, $fid)
    {
        return "update {$prfx}_forum_statistics
                left join
                  (select forum_id, 
                   {$prfx}_topic_statistics.last_message_date last_message_date, 
                   {$prfx}_topic_statistics.last_message_id last_message_id
                   from {$prfx}_topic 
                   inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
                   where forum_id = $fid and deleted <> 1 and publish_delay <> 1
                   order by {$prfx}_topic_statistics.last_message_id desc limit 1
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
                set
                {$prfx}_forum_statistics.topic_count = ifnull(t_total_cnt.cnt, 0) - ifnull(t_invisible_cnt.cnt, 0),
                {$prfx}_forum_statistics.topic_count_total = ifnull(t_total_cnt.cnt, 0) - ifnull(t_delayed_cnt.cnt, 0),
                {$prfx}_forum_statistics.last_message_date = ifnull(t_last.last_message_date, {$prfx}_forum_statistics.last_message_date),
                {$prfx}_forum_statistics.last_message_id = ifnull(t_last.last_message_id, {$prfx}_forum_statistics.last_message_id)
                where {$prfx}_forum_statistics.forum_id = $fid";
    } // get_query_topic_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_post_cnt_update($prfx, $tid)
    {
        return "update {$prfx}_topic_statistics
                left join
                  (select topic_id, creation_date last_message_date, id last_message_id
                   from {$prfx}_post where topic_id = $tid and deleted <> 1
                   order by id desc limit 1
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
                set
                {$prfx}_topic_statistics.post_count = ifnull(ptcnt.cnt, 0) - ifnull(pdcnt.cnt, 0),
                {$prfx}_topic_statistics.post_count_total = ifnull(ptcnt.cnt, 0),
                {$prfx}_topic_statistics.last_message_date = ifnull(plast.last_message_date, {$prfx}_topic_statistics.last_message_date),
                {$prfx}_topic_statistics.last_message_id = ifnull(plast.last_message_id, {$prfx}_topic_statistics.last_message_id)
                where {$prfx}_topic_statistics.topic_id = $tid";
    } // get_query_post_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_user_post_cnt_update($prfx, $uid)
    {
        return "update {$prfx}_user_statistics
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
            set
            {$prfx}_user_statistics.post_count = ifnull(user_post_count.cnt, 0),
            {$prfx}_user_statistics.topic_count = ifnull(user_topic_count.cnt, 0)
            where {$prfx}_user_statistics.user_id = $uid";
    } // get_query_user_post_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_user_rate_cnt_update($prfx, $uid)
    {
        return "update {$prfx}_user_statistics
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
            set
            {$prfx}_user_statistics.like_count = ifnull(user_like_count.cnt, 0),
            {$prfx}_user_statistics.dislike_count = ifnull(user_dislike_count.cnt, 0)
            where {$prfx}_user_statistics.user_id = $uid";
    } // get_query_user_rate_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_post_rate_cnt_update($prfx, $pid)
    {
        return "update {$prfx}_post_statistics
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
                set
                   like_count = ifnull(cnt.likes, 0),
                   dislike_count = ifnull(cnt.dislikes, 0)
                where {$prfx}_post_statistics.post_id = $pid";
    } // get_query_post_rate_cnt_update
    
    //-----------------------------------------------------------------
    function get_query_moderator_events($prfx, $where, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"];
        
        return "select
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
             order by {$prfx}_moderator_log.id desc
             limit $begin, $pagination_info[rows_per_page]";
    } // get_query_moderator_events
    
    //-----------------------------------------------------------------
    function get_query_subscribed_messages(&$dbw, $prfx, $uid, $mindate)
    {
        $forum_restriction_appendix = $this->get_forum_restriction_appendix($dbw, $prfx);
        if (!empty($forum_restriction_appendix)) {
            $forum_restriction_appendix = " and " . $forum_restriction_appendix;
        }
        
        $ignore_forum_where_appendix = $this->get_ignore_forum_where_appendix($dbw, $prfx);
        $ignore_topic_where_appendix = $this->get_ignore_topic_where_appendix($dbw, $prfx);
        
        return "select ifnull({$prfx}_user.user_name, {$prfx}_post.author) author, count(*) cnt
                from {$prfx}_post {$prfx}_post use index ({$prfx}_post_creation_date_idx)
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                left join {$prfx}_private_topics on ({$prfx}_topic.id = {$prfx}_private_topics.topic_id and {$prfx}_topic.is_private = 2 and {$prfx}_private_topics.participant_id = $uid)
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                inner join {$prfx}_user_subscription
                on ({$prfx}_user_subscription.user_id = $uid and ({$prfx}_post.user_id = {$prfx}_user_subscription.subscribed_user_id or {$prfx}_post.author = {$prfx}_user_subscription.subscribed_user_name))
                left join {$prfx}_user on ({$prfx}_user_subscription.subscribed_user_id = {$prfx}_user.id)
                where {$prfx}_post.creation_date >= '$mindate' and {$prfx}_post.creation_date > {$prfx}_user_subscription.last_view
                and publish_delay <> 1 and {$prfx}_post.deleted <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_forum.deleted <> 1
                and (is_private < 1 or {$prfx}_private_topics.topic_id is not NULL)
                $forum_restriction_appendix
                $ignore_forum_where_appendix
                $ignore_topic_where_appendix
                group by ifnull({$prfx}_user.user_name, {$prfx}_post.author)";
    } // get_query_subscribed_messages
    
    //-----------------------------------------------------------------
    function get_query_subscribed_topics(&$dbw, $prfx, $uid, $mindate)
    {
        $forum_restriction_appendix = $this->get_forum_restriction_appendix($dbw, $prfx);
        if (!empty($forum_restriction_appendix)) {
            $forum_restriction_appendix = " and " . $forum_restriction_appendix;
        }
        
        $ignore_forum_where_appendix = $this->get_ignore_forum_where_appendix($dbw, $prfx);
        $ignore_topic_where_appendix = $this->get_ignore_topic_where_appendix($dbw, $prfx);
        
        return "select ifnull({$prfx}_user.user_name, {$prfx}_topic.author) author, count(*) cnt
                from {$prfx}_topic use index ({$prfx}_topic_creation_date_idx)
                left join {$prfx}_private_topics on ({$prfx}_topic.id = {$prfx}_private_topics.topic_id and {$prfx}_topic.is_private = 2 and {$prfx}_private_topics.participant_id = $uid)
                inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                inner join {$prfx}_user_subscription
                on ({$prfx}_user_subscription.user_id = $uid and ({$prfx}_topic.user_id = {$prfx}_user_subscription.subscribed_user_id or {$prfx}_topic.author = {$prfx}_user_subscription.subscribed_user_name))
                left join {$prfx}_user on ({$prfx}_user_subscription.subscribed_user_id = {$prfx}_user.id)
                where {$prfx}_topic.creation_date >= '$mindate' and {$prfx}_topic.creation_date > {$prfx}_user_subscription.last_view
                and publish_delay <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_forum.deleted <> 1
                and (is_private < 1 or {$prfx}_private_topics.topic_id is not NULL)
                $forum_restriction_appendix
                $ignore_forum_where_appendix
                $ignore_topic_where_appendix
                group by ifnull({$prfx}_user.user_name, {$prfx}_topic.author)";
    } // get_query_subscribed_topics
    
    //-----------------------------------------------------------------
    function get_query_subscribed_authors($prfx, $uid, $mindate)
    {
        return "select subscribed_user_id, subscribed_user_name, user_name, last_visit_date, {$prfx}_user.last_post_date user_last_post_date, logout, tm, last_view,
                (select max(creation_date) from {$prfx}_post where ({$prfx}_post.user_id = {$prfx}_user_subscription.subscribed_user_id or {$prfx}_post.author = {$prfx}_user_subscription.subscribed_user_name) and creation_date > '$mindate') guest_last_post_date
                from {$prfx}_user_subscription
                left join {$prfx}_user on ({$prfx}_user_subscription.subscribed_user_id = {$prfx}_user.id)
                where {$prfx}_user_subscription.user_id = $uid
                order by ifnull(guest_last_post_date, user_last_post_date) desc, last_view desc";
    } // get_query_subscribed_authors
    
    //-----------------------------------------------------------------
    function get_query_fill_digest_posts($dbw, $prfx, $session_id, $now, $search_hash, $uid, $rm, $fid, $private_fid)
    {
        $new_tracking_period = defined('NEW_TRACKING_PERIOD') ? NEW_TRACKING_PERIOD : 30;
        $mindate = $dbw->format_datetime(time() - $new_tracking_period * 24 * 3600);
        
        $ignore_forum_where_appendix = $this->get_ignore_forum_where_appendix($dbw, $prfx);
        
        $ignore_topic_where_appendix = $this->get_ignore_topic_where_appendix($dbw, $prfx);
        
        $ignore_post_where_appendix = $this->get_ignore_post_where_appendix($dbw, $prfx);
        
        $ignore_comment_where_appendix = $this->get_ignore_comment_where_appendix($dbw, $prfx);

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
                                  from {$prfx}_post use index ({$prfx}_post_creation_date_idx)
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
                                  
                                  and {$prfx}_post.creation_date > ifnull({$prfx}_topic_read_markers.last_read_date, {$prfx}_forum_read_markers.first_read_date)
                            
                                  $ignore_forum_where_appendix
                                  
                                  $ignore_topic_where_appendix
                            
                                  $ignore_post_where_appendix
                                  
                                  $ignore_comment_where_appendix
                              ) srch
                              where nr <= 5 $topic_appendix
                              order by last_message_id desc
                             ";
    } // get_query_fill_digest_posts
    
    //-----------------------------------------------------------------
    function get_query_fill_search_posts($prfx, $session_id, $now, $search_hash, $topic_part_where, $post_part_where, $max_search_results, $order_by, &$hints)
    {
        $post_hint = "";
        if (!empty($hints["post"])) {
            $hints["post"]["primary"] = "primary";
            $hints["post"]["{$prfx}_post_topic_id_idx"] = "{$prfx}_post_topic_id_idx";
            $post_hint = "use index (" . implode(", ", $hints["post"]) . ")";
        }
        
        return "insert into {$prfx}_found_post_cache (post_id, topic_id, session_id, dt, search_hash)
                              select
                              {$prfx}_post.id, {$prfx}_post.topic_id, '$session_id', '$now', '$search_hash'
                              from {$prfx}_post $post_hint
                              left join {$prfx}_post_statistics on ({$prfx}_post.id = {$prfx}_post_statistics.post_id)
                              inner join {$prfx}_topic ignore index ({$prfx}_topic_is_private_idx, {$prfx}_topic_publish_delay_idx, {$prfx}_topic_is_deleted_idx) on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                              inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
                              inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
                              
                              where
                              {$prfx}_topic.publish_delay <> 1
                              $topic_part_where
                              $post_part_where
                              $order_by
                              limit $max_search_results
                             ";
    } // get_query_fill_search_posts
    
    //-----------------------------------------------------------------
    function get_query_fill_search_topics($prfx, $session_id, $now, $search_hash, $topic_where, $delayed_clause, $max_search_results)
    {
        return "insert into {$prfx}_found_topic_cache (topic_id, session_id, dt, search_hash)
              select 
              {$prfx}_topic.id, '$session_id', '$now', '$search_hash'
              from {$prfx}_topic
              inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
              inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
              where
              $delayed_clause
              $topic_where
              order by {$prfx}_topic_statistics.last_message_id desc
              limit $max_search_results
             ";
    } // get_query_fill_search_topics
    
    //-----------------------------------------------------------------
    function get_query_found_topics($prfx, $current_uid, $session_id, $hash, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"];
        
        return "select
            topics.id, name, creation_date,
            last_message_date, post_count, post_count_total,
            hits_count, bot_hits_count, profiled_topic, deleted, closed, pinned, has_pinned_post, {$prfx}_pinned_topics.topic_id user_pinned,
            forum_deleted, disable_ignore,
            topics.user_id, author, topics.read_marker, {$prfx}_user.user_name,
            {$prfx}_user.last_visit_date, {$prfx}_user.logout,
            forum_id, forum_name,
            is_poll, publish_delay, no_guests
           from
           (select
            {$prfx}_topic.id, {$prfx}_topic.name, {$prfx}_topic.creation_date,
            {$prfx}_topic_statistics.last_message_date, post_count, post_count_total,
            hits_count, bot_hits_count, {$prfx}_topic.profiled_topic, {$prfx}_topic.deleted, {$prfx}_topic.closed, {$prfx}_topic.pinned, has_pinned_post,
            {$prfx}_forum.deleted forum_deleted, disable_ignore,
            {$prfx}_topic.user_id, {$prfx}_topic.author, {$prfx}_topic.read_marker,
            forum_id, {$prfx}_forum.name forum_name,
            is_poll, publish_delay, {$prfx}_topic.no_guests
            
            from {$prfx}_topic
            inner join {$prfx}_topic_statistics on ({$prfx}_topic.id = {$prfx}_topic_statistics.topic_id)
            
            inner join {$prfx}_found_topic_cache on ({$prfx}_topic.id = {$prfx}_found_topic_cache.topic_id)
            inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
            where
            session_id = '$session_id' and search_hash = '$hash'
            order by {$prfx}_topic.publish_delay desc, last_message_date desc, {$prfx}_topic.id desc
            limit $begin, $pagination_info[rows_per_page]) topics
            left join {$prfx}_pinned_topics on (topics.id = {$prfx}_pinned_topics.topic_id and {$prfx}_pinned_topics.user_id = $current_uid)
            left join {$prfx}_user on (topics.user_id = {$prfx}_user.id)
            ";
    } // get_query_found_topics
    
    //-----------------------------------------------------------------
    function get_topic_search_clause(&$dbw, $prfx, $search_keys, $with_morphology)
    {
        $where_clause = "";
        
        $search_keys = utf8_trim($search_keys);
        
        $stop_words = array();
        if (file_exists(__DIR__ . "/MySQL_StopWords.txt") &&
            $contents = @file_get_contents(__DIR__ . "/MySQL_StopWords.txt")) {
            if (!empty($contents)) {
                $stop_words = preg_split("/[\r\n]+/", $contents, -1, PREG_SPLIT_NO_EMPTY);
            }
            
            //debug_message(print_r($stop_words, true));
        }
        
        $db_ft_min_word_len = defined('DB_FT_MIN_WORD_LEN') ? DB_FT_MIN_WORD_LEN : 3;
        
        if (!empty($search_keys)) {
            // search by exact phrase, e.g "exact revolution"
            if (preg_match('/^"(.+)"$/iu', $search_keys, $matches)) {
                $search_clause = '"' . str_replace('"', '""', $dbw->escape(trim($matches[1]))) . '"';
                $where_clause = "match ({$prfx}_topic.name) against ('$search_clause' in boolean mode)";
                return $where_clause;
            }
            
            $search_keys = preg_replace("/\s+(или|or|oder)\s+/iu", " | ", $search_keys);
            $search_keys = preg_replace("/\s+(и|and|und)\s+/iu", " & ", $search_keys);
            
            $search_clause = "";
            
            $skey_array = preg_split("/\s*([\|\&])\s*/iu", $search_keys, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE);
            
            $skey_array = array_diff($skey_array, $stop_words);
            
            // if the union is not explicitly specified, AND is implied
            
            if (count($skey_array) == 0) {
                return "";
            } elseif (count($skey_array) == 1) {
                $search_clause = "";
                $skey_array = preg_split("/\s+/iu", $search_keys, -1, PREG_SPLIT_NO_EMPTY);
                
                if (count($skey_array) == 0) {
                    return "";
                }
                
                $skey_array = array_diff($skey_array, $stop_words);
                
                foreach ($skey_array as $skey) {
                    $skey = utf8_trim($skey);
                    
                    $words = array();
                    if ($with_morphology && $this->get_unicore_words($skey, $words)) {
                        $search_clause .= ' +(';
                        foreach ($words as $word) {
                            if (utf8_strlen($word) < $db_ft_min_word_len) {
                                continue;
                            }
                            
                            $search_clause .= ' "' . str_replace('"', '""', $dbw->escape($word)) . '"';
                        }
                        $search_clause .= ')';
                    } else {
                        if (utf8_strlen($skey) < $db_ft_min_word_len) {
                            continue;
                        }
                        
                        $search_clause .= ' +"' . str_replace('"', '""', $dbw->escape($skey)) . '"';
                    }
                }
            } else {
                $first_and = true;
                
                foreach ($skey_array as $skey) {
                    if ($skey == "|" || $skey == "&") {
                        if ($skey == "|") {
                            $search_clause .= " ";
                        }
                        
                        if ($skey == "&") {
                            $search_clause .= " +";
                            
                            if ($first_and) {
                                $first_and = false;
                                
                                $search_clause = "+" . $search_clause;
                            }
                        }
                        
                        continue;
                    }
                    
                    $skey = utf8_trim($skey);
                    
                    $words = array();
                    if ($with_morphology && $this->get_unicore_words($skey, $words)) {
                        $search_clause .= '(';
                        foreach ($words as $word) {
                            if (utf8_strlen($word) < $db_ft_min_word_len) {
                                continue;
                            }
                            
                            $search_clause .= ' "' . str_replace('"', '""', $dbw->escape($word)) . '"';
                        }
                        $search_clause .= ')';
                    } else {
                        if (utf8_strlen($skey) < $db_ft_min_word_len) {
                            continue;
                        }
                        
                        $search_clause .= '"' . str_replace('"', '""', $dbw->escape($skey)) . '"';
                    }
                }
            }
            
            $search_clause = trim(rtrim($search_clause, "+ "), " ");
            
            $where_clause = "match ({$prfx}_topic.name) against ('$search_clause' in boolean mode)";
        } // search_keys
        
        return $where_clause;
    } // get_topic_search_clause
    
    //-----------------------------------------------------------------
    function get_post_search_clause(&$dbw, $prfx, $search_keys, $with_morphology)
    {
        $where_clause = "";
        
        $search_keys = utf8_trim($search_keys);
        
        $stop_words = array();
        if (file_exists(__DIR__ . "/MySQL_StopWords.txt") &&
            $contents = @file_get_contents(__DIR__ . "/MySQL_StopWords.txt")) {
            if (!empty($contents)) {
                $stop_words = preg_split("/[\r\n]+/", $contents, -1, PREG_SPLIT_NO_EMPTY);
            }
            
            //debug_message(print_r($stop_words, true));
        }
        
        $db_ft_min_word_len = defined('DB_FT_MIN_WORD_LEN') ? DB_FT_MIN_WORD_LEN : 3;
        
        if (!empty($search_keys)) {
            // search by exact phrase, e.g "exact revolution"
            if (preg_match('/^"(.+)"$/iu', $search_keys, $matches)) {
                $search_clause = '"' . str_replace('"', '""', $dbw->escape(trim($matches[1]))) . '"';
                $where_clause = "match (searchable_content) against ('$search_clause' in boolean mode)";
                return $where_clause;
            }
            
            $search_keys = preg_replace("/\s+(или|or|oder)\s+/iu", " | ", $search_keys);
            $search_keys = preg_replace("/\s+(и|and|und)\s+/iu", " & ", $search_keys);
            
            $search_clause = "";
            
            $skey_array = preg_split("/\s*([\|\&])\s*/iu", $search_keys, -1, PREG_SPLIT_NO_EMPTY + PREG_SPLIT_DELIM_CAPTURE);
            
            $skey_array = array_diff($skey_array, $stop_words);
            
            // if the union is not explicitly specified, AND is implied
            
            if (count($skey_array) == 0) {
                return "";
            } elseif (count($skey_array) == 1) {
                $search_clause = "";
                $skey_array = preg_split("/\s+/iu", $search_keys, -1, PREG_SPLIT_NO_EMPTY);
                
                if (count($skey_array) == 0) {
                    return "";
                }
                
                $skey_array = array_diff($skey_array, $stop_words);
                
                foreach ($skey_array as $skey) {
                    $skey = utf8_trim($skey);
                    
                    $words = array();
                    if ($with_morphology && $this->get_unicore_words($skey, $words)) {
                        $search_clause .= ' +(';
                        foreach ($words as $word) {
                            if (utf8_strlen($word) < $db_ft_min_word_len) {
                                continue;
                            }
                            
                            $search_clause .= ' "' . str_replace('"', '""', $dbw->escape($word)) . '"';
                        }
                        $search_clause .= ')';
                    } else {
                        if (utf8_strlen($skey) < $db_ft_min_word_len) {
                            continue;
                        }
                        
                        $search_clause .= ' +"' . str_replace('"', '""', $dbw->escape($skey)) . '"';
                    }
                }
            } else {
                $first_and = true;
                
                foreach ($skey_array as $skey) {
                    if ($skey == "|" || $skey == "&") {
                        if ($skey == "|") {
                            $search_clause .= " ";
                        }
                        
                        if ($skey == "&") {
                            $search_clause .= " +";
                            
                            if ($first_and) {
                                $first_and = false;
                                
                                $search_clause = "+" . $search_clause;
                            }
                        }
                        
                        continue;
                    }
                    
                    $skey = utf8_trim($skey);
                    
                    $words = array();
                    if ($with_morphology && $this->get_unicore_words($skey, $words)) {
                        $search_clause .= '(';
                        foreach ($words as $word) {
                            if (utf8_strlen($word) < $db_ft_min_word_len) {
                                continue;
                            }
                            
                            $search_clause .= ' "' . str_replace('"', '""', $dbw->escape($word)) . '"';
                        }
                        $search_clause .= ')';
                    } else {
                        if (utf8_strlen($skey) < $db_ft_min_word_len) {
                            continue;
                        }
                        
                        $search_clause .= '"' . str_replace('"', '""', $dbw->escape($skey)) . '"';
                    }
                }
            }
            
            $search_clause = trim(rtrim($search_clause, "+ "), " ");
            
            $where_clause = "match (searchable_content) against ('$search_clause' in boolean mode)";
        } // search_keys
        
        return $where_clause;
    } // get_post_search_clause

    //-----------------------------------------------------------------
    function get_query_event_list($prfx, $where, &$pagination_info)
    {
        $begin = ($pagination_info["page"] - 1) * $pagination_info["rows_per_page"];
        
        return "select
             {$prfx}_events.id, event_time, 
             event_code, is_new, todo, params, author_name, author_id,
             last_visit_date, logout
             from {$prfx}_events
             left join {$prfx}_user on ({$prfx}_events.author_id = {$prfx}_user.id)
             $where
             order by {$prfx}_events.id desc
             limit $begin, $pagination_info[rows_per_page]
           ";
    } // get_query_event_list
    
    //-----------------------------------------------------------------
    function get_query_last_n_posts($n, $prfx, $forum_id, $author_id, $author_rm)
    {
        $author_appendix = "and {$prfx}_post.read_marker = $author_rm and {$prfx}_post.user_id is NULL";
        if (!empty($author_id)) {
            $author_appendix = "and {$prfx}_post.user_id = $author_id";
        }
        
        return "select {$prfx}_post.id
            from {$prfx}_post
            inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
            where
            {$prfx}_topic.forum_id = $forum_id
            and {$prfx}_post.deleted <> 1 and {$prfx}_topic.is_private < 1
            $author_appendix
            order by {$prfx}_post.creation_date desc, {$prfx}_post.id desc
            limit $n
            ";
    } // get_query_last_n_posts
    
    //-----------------------------------------------------------------
    function get_query_last_n_rates($prfx, $n, $rater_id, $author_id, $moderator_restriction, $rate_type)
    {
        return "select
            {$prfx}_post_rating.id, {$prfx}_post_rating.post_id
            from {$prfx}_post_rating
            inner join {$prfx}_post on ({$prfx}_post.id = {$prfx}_post_rating.post_id)
            inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
            where {$prfx}_post_rating.user_id = $rater_id and rating = $rate_type and {$prfx}_post.user_id = $author_id
            $moderator_restriction
            order by {$prfx}_post_rating.dt desc
            limit $n
            ";
    } // get_query_last_n_rates
    
    //-----------------------------------------------------------------
    function get_query_read_topics($prfx, $uid, $forum_appendix)
    {
        return "select
             {$prfx}_topic.id, forum_id, {$prfx}_topic.name, {$prfx}_forum.name forum_name, max({$prfx}_topic_view_history.dt) dt
             from {$prfx}_topic
             inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
             inner join {$prfx}_topic_view_history on ({$prfx}_topic.id = {$prfx}_topic_view_history.topic_id)
             where
             {$prfx}_topic_view_history.user_id = $uid and
             {$prfx}_forum.deleted <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_topic.publish_delay <> 1 and {$prfx}_topic.is_private < 1
             $forum_appendix
             group by {$prfx}_topic.id, forum_id, {$prfx}_topic.name, {$prfx}_forum.name
             order by max({$prfx}_topic_view_history.dt) desc
             limit 200
             ";
    } // get_query_read_topics
    
    //-----------------------------------------------------------------
    function get_query_guest_last_activity($prfx, $guest)
    {
        if (empty($guest)) {
            $where = "where guest_name is NULL and user_id is NULL";
        } else {
            $where = "where guest_name = '$guest'";
        }
        
        return "select dt, ip from {$prfx}_topic_view_history $where order by dt desc limit 1";
    } // get_query_guest_last_activity
    
    //-----------------------------------------------------------------
    function get_query_guest_read_topics($prfx, $guest, $forum_appendix)
    {
        if (empty($guest) || $guest == "NULL") {
            $where = "{$prfx}_topic_view_history.guest_name is NULL and {$prfx}_topic_view_history.user_id is NULL";
        } else {
            $where = "{$prfx}_topic_view_history.guest_name = $guest";
        }

        return "select
             {$prfx}_topic.id, forum_id, {$prfx}_topic.name, {$prfx}_forum.name forum_name, {$prfx}_topic_view_history.dt
             from {$prfx}_topic
             inner join {$prfx}_forum on ({$prfx}_topic.forum_id = {$prfx}_forum.id)
             inner join {$prfx}_topic_view_history on ({$prfx}_topic.id = {$prfx}_topic_view_history.topic_id)
             where
             $where and
             {$prfx}_forum.deleted <> 1 and {$prfx}_topic.deleted <> 1 and {$prfx}_topic.publish_delay <> 1 and {$prfx}_topic.is_private < 1
             $forum_appendix
             order by {$prfx}_topic_view_history.dt desc
             limit 200
             ";
    } // get_query_guest_read_topics
    
    //-----------------------------------------------------------------
    function get_query_ignored_posts_list($prfx, $where)
    {
        return "select 1
               from 
               {$prfx}_post use index (primary, {$prfx}_post_topic_id_idx, {$prfx}_post_author_idx, {$prfx}_post_rm_idx)
               inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
               $where
               limit 1";
    } // get_query_ignored_posts_list

    //-----------------------------------------------------------------
    function get_query_ignored_comments_list($prfx, $where)
    {
        return "select 1
               from 
               {$prfx}_post use index (primary, {$prfx}_post_topic_id_idx, {$prfx}_post_author_idx, {$prfx}_post_rm_idx)
               inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
               $where
               limit 1";
    } // get_query_ignored_comments_list

    //-----------------------------------------------------------------
    function get_query_post_count($prfx, $where)
    {
        return "select
                user_name, {$prfx}_post.user_id, ifnull({$prfx}_post.user_id, {$prfx}_post.read_marker) uid, {$prfx}_user.registration_date,
                count(*) cnt
                from {$prfx}_post
                inner join {$prfx}_topic on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                left join {$prfx}_user on ({$prfx}_post.user_id = {$prfx}_user.id)
                $where
                group by
                user_name, {$prfx}_post.user_id, ifnull({$prfx}_post.user_id, {$prfx}_post.read_marker), {$prfx}_user.registration_date
                order by count(*) desc
                ";
    } // get_query_post_count
    
    //-----------------------------------------------------------------
    function get_query_load_attachments($prfx, $uid, $current_appendex)
    {
        return "select {$prfx}_attachment.id, {$prfx}_attachment.post_id,
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
                             limit 100";
    } // get_query_load_attachments
    
    //-----------------------------------------------------------------
    function get_query_clear_viewed_topics($prfx)
    {
        return "select users.user_id, min(vth.dt) as min_dt
                from (
                    select user_id, topic_id, max(dt) as dt
                    from {$prfx}_topic_view_history
                    group by user_id, topic_id
                    order by max(dt) desc
                    limit 300
                ) vth
                inner join (
                    select distinct user_id
                    from {$prfx}_topic_view_history
                    where user_id is not NULL
                ) users
                on vth.user_id = users.user_id
                group by users.user_id";
    } // get_query_clear_viewed_topics
    
    //-----------------------------------------------------------------
    function get_query_clear_guest_viewed_topics($prfx)
    {
        return "select guests.guest_name, min(vth.dt) as min_dt
                from (
                    select guest_name, topic_id, max(dt) as dt
                    from {$prfx}_topic_view_history
                    group by guest_name, topic_id
                    order by max(dt) desc
                    limit 300
                ) vth
                join (
                    select distinct guest_name
                    from {$prfx}_topic_view_history
                    where guest_name is not NULL
                ) guests
                on vth.guest_name = guests.guest_name
                group by guests.guest_name";
    } // get_query_clear_viewed_topics

    //-----------------------------------------------------------------
    function get_query_user_hour_hits($prfx, $where, $hour_offset)
    {
        return "select hour(date_add(dt, interval $hour_offset second)) hour, sum(hits_count) hits_count
                from {$prfx}_forum_hits
                $where
                group by hour(date_add(dt, interval $hour_offset second))
                order by hour(date_add(dt, interval $hour_offset second))";
    } // get_query_user_hour_hits
    
    //-----------------------------------------------------------------
    function get_query_user_hour_posts($prfx, $where, $hour_offset)
    {
        return "select hour(date_add(creation_date, interval $hour_offset second)) hour, count(*) post_count
                from {$prfx}_post
                $where
                group by hour(date_add(creation_date, interval $hour_offset second))
                order by hour(date_add(creation_date, interval $hour_offset second))
               ";
    } // get_query_user_hour_posts
    
    //-----------------------------------------------------------------
    function gen_activity_statistics(&$user_activity, &$ip_activity, &$agent_activity, &$total_user_hits_count, &$total_ip_hits_count, &$total_agents_hits_count)
    {
        global $settings;
        
        start_action_time_measure();
        
        $rodbw = system::getRODBWorker();
        if (!$rodbw) {
            return false;
        }
        
        $prfx = $rodbw->escape(system::getDBPrefix());
        
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
                $start_date = xstrtotime("-1 day", $now_rounded);
                break;
        }
        
        $now = $rodbw->format_datetime(time());
        $start_date = $rodbw->format_datetime($start_date);
        
        if (!$rodbw->execute_query("select user_id, guest_name, last_visit_date, logout, count(*) cnt
                             from
                             {$prfx}_forum_hits
                             left join {$prfx}_user on ({$prfx}_forum_hits.user_id = {$prfx}_user.id)
                             where dt >= '$start_date' and bot is NULL
                             group by user_id, guest_name, last_visit_date, logout
                             order by cnt desc
                             limit 20")) {
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
            $is_anonym = 0;
            if (empty($name)) {
                $name = text("Anonyms");
                $is_anonym = 1;
            }
            
            $total_user_hits_count += $rodbw->field_by_name("cnt");
            $user_activity[] = array(
                "id" => $rodbw->field_by_name("user_id"),
                "user_name" => $name,
                "is_anonym" => $is_anonym,
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
        
        if (!$rodbw->execute_query("select {$prfx}_forum_hits.ip, {$prfx}_ip_blocked.ip blocked, count(*) cnt
                             from
                             {$prfx}_forum_hits
                             left join {$prfx}_ip_blocked on ({$prfx}_forum_hits.ip = {$prfx}_ip_blocked.ip and (block_expires is NULL or block_expires > '$now'))
                             where dt >= '$start_date'
                             group by {$prfx}_forum_hits.ip, {$prfx}_ip_blocked.ip
                             order by cnt desc
                             limit 20")) {
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
            $ip_activity[$ip] = array(
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
        
        if (!$rodbw->execute_query("select {$prfx}_forum_hits.user_agent, count(*) cnt
                             from
                             {$prfx}_forum_hits
                             where dt >= '$start_date'
                             group by {$prfx}_forum_hits.user_agent
                             order by cnt desc
                             limit 40")) {
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
        
        measure_action_time("get activity statistics");
        
        return true;
    } // gen_activity_statistics
    
    //-----------------------------------------------------------------
    function gen_load_statistics()
    {
        global $settings;
        
        start_action_time_measure();
        
        $rodbw = system::getRODBWorker();
        if (!$rodbw) {
            return false;
        }
        
        $prfx = $rodbw->escape(system::getDBPrefix());
        
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
                $start_date = xstrtotime("-1 day", $now_rounded);
                break;
        }
        
        $now = $rodbw->format_datetime(time());
        $start_date = $rodbw->format_datetime($start_date);

        unset($_SESSION["load_hits"]);
        unset($_SESSION["exec_hits"]);
        unset($_SESSION["exec_times"]);
        unset($_SESSION["topic_rm_count"]);
        unset($_SESSION["forum_rm_count"]);
        unset($_SESSION["total_topic_rm_count"]);
        unset($_SESSION["total_forum_rm_count"]);
        
        if (!$rodbw->execute_query("select
                             extract(minute from dt) mn,
                             extract(hour from dt) hh,
                             extract(month from dt) mm,
                             extract(day from dt) dd,
                             extract(year from dt) yy,
                             count(*) hits_count,
                             avg(exec_time) exec_time,
                             max(topic_rm_count) topic_rm_count,
                             max(forum_rm_count) forum_rm_count,
                             max(total_topic_rm_count) total_topic_rm_count,
                             max(total_forum_rm_count) total_forum_rm_count
                             from
                             {$prfx}_load_statistics
                             where dt >= '$start_date' 
                             group by extract(year from dt), extract(month from dt), extract(day from dt), extract(hour from dt), extract(minute from dt)
                             order by extract(year from dt), extract(month from dt), extract(day from dt), extract(hour from dt), extract(minute from dt)
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

            $_SESSION["total_topic_rm_count"][$time] = round($rodbw->field_by_name("total_topic_rm_count"));
            $_SESSION["total_forum_rm_count"][$time] = round($rodbw->field_by_name("total_forum_rm_count"));
        }
        
        $rodbw->free_result();
        
        if (!$rodbw->execute_query("select
                             extract(minute from dt) mn,
                             extract(hour from dt) hh,
                             extract(month from dt) mm,
                             extract(day from dt) dd,
                             extract(year from dt) yy,
                             sum(hits_count) hits_count
                             from
                             {$prfx}_forum_hits
                             where dt >= '$start_date'
                             group by extract(year from dt), extract(month from dt), extract(day from dt), extract(hour from dt), extract(minute from dt)
                             order by extract(year from dt), extract(month from dt), extract(day from dt), extract(hour from dt), extract(minute from dt)
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

        if (!empty($_SESSION["total_topic_rm_count"])) {
            ksort($_SESSION["total_topic_rm_count"]);
        }
        
        if (!empty($_SESSION["total_forum_rm_count"])) {
            ksort($_SESSION["total_forum_rm_count"]);
        }

        measure_action_time("get load statistics");
        
        return true;
    } // gen_load_statistics
    
    //-----------------------------------------------------------------
    function create_tmp_id_collector_table($dbw, $prfx)
    {
        $query = "create temporary table if not exists tmp_id_collector(id integer)";
        if (!$dbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
            return "";
        }
        
        if (!$dbw->execute_query("truncate table tmp_id_collector")) {
            MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
            return "";
        }

        return "tmp_id_collector";
    } // create_tmp_id_collector_table

    //-----------------------------------------------------------------
    function get_reply_post_clause($dbw, $prfx, $parent_pid)
    {
        $query = "create temporary table if not exists tmp_children(id integer)";
        if (!$dbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
            return "";
        }
        
        if (!$dbw->execute_procedure("{$prfx}_deep_collect_replies", $parent_pid)) {
            MessageHandler::setError(text("ErrQueryFailed"), $dbw->get_last_error() . "\n\n" . $dbw->get_last_query());
            return "";
        }

        return " and {$prfx}_post.id in (select id from tmp_children)";
    } // get_reply_post_clause
    
    //-----------------------------------------------------------------
    function get_last_replies_clause($srdbw, $prfx, $author_id, $author, $start_date, $end_date)
    {
        $query = "create temporary table if not exists tmp_children(id integer)";
        if (!$srdbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
            return "";
        }
        
        $query = "create temporary table if not exists tmp_children_aux1(id integer)";
        if (!$srdbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
            return "";
        }

        $query = "create temporary table if not exists tmp_children_aux2(id integer)";
        if (!$srdbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
            return "";
        }
        
        $query = "delete from tmp_children";
        if (!$srdbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
            return "";
        }
        
        $query = "delete from tmp_children_aux1";
        if (!$srdbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
            return "";
        }

        $query = "delete from tmp_children_aux2";
        if (!$srdbw->execute_query($query)) {
            MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
            return "";
        }

        $date_appendix = "";
        if (!empty($start_date) && $start_date != "error") {
            $date_appendix .= " and {$prfx}_post.creation_date >= '" . $srdbw->format_datetime($start_date) . "'";
        }
        
        if (!empty($end_date) && $end_date != "error") {
            $date_appendix .= " and {$prfx}_post.creation_date <= '" . $srdbw->format_datetime($end_date) . "'";
        }
      
        if (!empty($author_id)) {
            $query = "insert into tmp_children (id)
                      select reply_post_id from {$prfx}_post_hierarchy
                      inner join {$prfx}_post on ({$prfx}_post_hierarchy.parent_post_id = {$prfx}_post.id)
                      where {$prfx}_post.user_id = $author_id
                      $date_appendix
                      order by reply_post_id desc
                      limit 250
                      ";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }
            
            $query = "insert into tmp_children_aux1 (id) select id from tmp_children";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children_aux2 (id) select id from tmp_children";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children (id)
                      select reply_post_id from {$prfx}_post_hierarchy
                      inner join {$prfx}_post on ({$prfx}_post_hierarchy.reply_post_id = {$prfx}_post.id)
                      where {$prfx}_post.user_id = $author_id and parent_post_id in (select id from tmp_children_aux1)
                      and reply_post_id not in (select id from tmp_children_aux2)
                      $date_appendix
                      ";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "delete from tmp_children_aux1";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "delete from tmp_children_aux2";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children_aux1 (id) select id from tmp_children";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children_aux2 (id) select id from tmp_children";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children (id)
                      select parent_post_id from {$prfx}_post_hierarchy
                      inner join {$prfx}_post on ({$prfx}_post_hierarchy.parent_post_id = {$prfx}_post.id)
                      where {$prfx}_post.user_id = $author_id
                      and parent_post_id not in (select id from tmp_children_aux1)
                      and reply_post_id in (select id from tmp_children_aux2)
                      $date_appendix
                      ";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }
        } else {
            $query = "insert into tmp_children (id)
                      select reply_post_id from {$prfx}_post_hierarchy
                      inner join {$prfx}_post on ({$prfx}_post_hierarchy.parent_post_id = {$prfx}_post.id)
                      where {$prfx}_post.author = $author
                      $date_appendix
                      order by reply_post_id desc
                      limit 250
                      ";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children_aux1 (id) select id from tmp_children";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children_aux2 (id) select id from tmp_children";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children (id)
                      select reply_post_id from {$prfx}_post_hierarchy
                      inner join {$prfx}_post on ({$prfx}_post_hierarchy.reply_post_id = {$prfx}_post.id)
                      where {$prfx}_post.author = $author and parent_post_id in (select id from tmp_children_aux1)
                      and reply_post_id not in (select id from tmp_children_aux2)
                      $date_appendix
                      ";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "delete from tmp_children_aux1";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "delete from tmp_children_aux2";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children_aux1 (id) select id from tmp_children";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children_aux2 (id) select id from tmp_children";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }

            $query = "insert into tmp_children (id)
                      select parent_post_id from {$prfx}_post_hierarchy
                      inner join {$prfx}_post on ({$prfx}_post_hierarchy.parent_post_id = {$prfx}_post.id)
                      where {$prfx}_post.author = $author
                      and parent_post_id not in (select id from tmp_children_aux1)
                      and reply_post_id in (select id from tmp_children_aux2)
                      $date_appendix
                      ";
            if (!$srdbw->execute_query($query)) {
                MessageHandler::setError(text("ErrQueryFailed"), $srdbw->get_last_error() . "\n\n" . $srdbw->get_last_query());
                return "";
            }
        }

        return " and {$prfx}_post.id in (select id from tmp_children)";
    } // get_last_replies_clause
    
    //-----------------------------------------------------------------
    function get_query_rating_info($prfx, $where)
    {
        return "select sum(like_count) likes, sum(dislike_count) dislikes, sum(like_count + dislike_count) rates
                from {$prfx}_post use index ({$prfx}_post_creation_date_idx)
                inner join {$prfx}_topic use index ({$prfx}_topic_is_private_idx) on ({$prfx}_post.topic_id = {$prfx}_topic.id)
                inner join {$prfx}_post_statistics on ({$prfx}_post.id = {$prfx}_post_statistics.post_id)
                $where";
    } // get_query_rating_info

    //-----------------------------------------------------------------
    function get_query_banned_ips($prfx)
    {
       return "select ip_summary.ip, first_attack, last_attack, attacks_count, atype, hits, hit_limit, check_period
               from
                 (select ip, 
                  min(banned_until) first_attack,
                  max(banned_until) last_attack,
                  count(*) attacks_count
                  from {$prfx}_banned_ips
                  group by ip
                  order by max(banned_until) desc
                  limit 200) ip_summary
                inner join
                 (select ip, 
                  atype, hits, hit_limit, check_period, 
                  row_number() over (partition by ip order by banned_until desc) as rnk
                  from {$prfx}_banned_ips
                 ) last_attacks
                on (ip_summary.ip = last_attacks.ip and last_attacks.rnk = 1)
               ";
    } // get_query_banned_ips

    //-----------------------------------------------------------------
    function get_hot_topic_clause($prfx, $start1, $start2)
    {
        $where = "";
        $where .= " and {$prfx}_topic_statistics.last_message_date >= '$start2'" . "\n";
        $where .= " and {$prfx}_topic_statistics.post_count_total >= 100" . "\n";
        $where .= " and (exists (select 1 from {$prfx}_post use index ({$prfx}_post_creation_date_idx, {$prfx}_post_author_idx) where {$prfx}_post.topic_id = {$prfx}_topic.id and {$prfx}_post.creation_date >= '$start1' group by topic_id having count(*) >= 15 and count(distinct {$prfx}_post.author) > 2) or 
                         exists (select 1 from {$prfx}_post use index ({$prfx}_post_creation_date_idx, {$prfx}_post_author_idx) where {$prfx}_post.topic_id = {$prfx}_topic.id and {$prfx}_post.creation_date >= '$start2' group by topic_id having count(*) >= 100 and count(distinct {$prfx}_post.author) > 2)
                        )" . "\n";
        
        return $where;        
    } // get_hot_topic_clause
    
    //-----------------------------------------------------------------
    function get_query_next_post($prfx, $tid, $last_post)
    {
        return "select {$prfx}_post.id
                       from {$prfx}_post
                       where {$prfx}_post.topic_id = $tid and {$prfx}_post.id > $last_post
                       order by {$prfx}_post.id
                       limit 1
                       ";
    } // get_query_next_post

    //-----------------------------------------------------------------
    function get_query_check_comments($prfx, $tid, $first_post, $last_post)
    {
        return "select {$prfx}_post.id, is_comment,
                       ifnull(lead(is_comment) over (order by {$prfx}_post.id), 0) as has_comments
                       from {$prfx}_post
                       where {$prfx}_post.topic_id = $tid and {$prfx}_post.id between $first_post and $last_post
                       ";
    }    
} // class MySQL_ForumManager
?>