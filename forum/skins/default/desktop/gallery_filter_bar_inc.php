<div id="gallery_filter_bar" class="gallery_filter_bar">

    <div class="gallery_filter_bar_block">
        <span class="gfb_label"><?php echo_html(text("Forum")); ?>:</span>
        <select name="forum" id="gallery_filter_forum" class="filter_field"
                onchange='Forum.show_sys_progress_indicator(true); reset_gallery_filter_area(); this.parentNode.parentNode.parentNode.scrollTo(0, 0); load_next_gallery_attachments("<?php echo_js(text("AddToFavourites")); ?>", "<?php echo_js(text("RemoveFromFavourites")); ?>");'>
            <option value="">-</option>

            <?php foreach ($forum_list as $fid => $fdata): ?>
                <option value="<?php echo_html($fid); ?>"><?php echo_html($fdata["name"]); ?></option>
            <?php endforeach; ?>

        </select>
    </div>

    <div class="gallery_filter_bar_block">
        <span class="gfb_label"><?php echo_html(text("DateRange")); ?>:</span>
        <div class="wrapper"><input type="text" class="filter_field" autocomplete="off" id="gallery_filter_start_date"
                                    name="start_date"
                                    value=""></div>
        <div class="wrapper"><input type="text" class="filter_field" autocomplete="off" id="gallery_filter_end_date"
                                    name="end_date"
                                    value=""></div>
    </div>

    <div class="gallery_filter_bar_block">
        <input type="button" class="standard_button" value="<?php echo_html(text("Search")); ?>"
               onclick='Forum.show_sys_progress_indicator(true); reset_gallery_filter_area(); this.parentNode.parentNode.parentNode.scrollTo(0, 0); load_next_gallery_attachments("<?php echo_js(text("AddToFavourites")); ?>", "<?php echo_js(text("RemoveFromFavourites")); ?>");'>
        <input type="button" class="standard_button" value="<?php echo_html(text("Reset")); ?>"
               onclick='reset_gallery_filter_dialog(); reset_gallery_filter_area(); Forum.show_sys_progress_indicator(true); this.parentNode.parentNode.parentNode.scrollTo(0, 0); load_next_gallery_attachments("<?php echo_js(text("AddToFavourites")); ?>", "<?php echo_js(text("RemoveFromFavourites")); ?>");'>
    </div>

</div>
