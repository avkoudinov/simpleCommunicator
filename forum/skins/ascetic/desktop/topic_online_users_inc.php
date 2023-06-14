<?php if(empty($settings["hide_online_status"]) || !empty($topic_data["is_private"])): ?>

<div class="header3 topic_readers">
<?php echo($treaders); ?>
</div>

<?php endif; ?>

<?php if(!empty($tignorers)): ?>

<div class="header3 topic_ignorers">
<?php echo($tignorers); ?>
</div>

<?php endif; ?>

<?php if(empty($settings["hide_online_status"]) && empty($topic_data["is_private"])): ?>

<div class="header3 forum_readers">
<?php echo($freaders); ?>
</div>

<?php endif; ?>

<?php
@include "online_users_inc.php";
?>
