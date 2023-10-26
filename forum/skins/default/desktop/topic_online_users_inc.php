<?php if((empty($settings["hide_online_status"]) && empty($_SESSION["skin_properties"][$skin]["no_online_users"])) || !empty($topic_data["is_private"])): ?>

<div class="header3 topic_readers">
<?php echo($treaders); ?>
</div>

<?php endif; ?>

<?php if(!empty($tignorers) && empty($_SESSION["skin_properties"][$skin]["no_online_users"])): ?>

<div class="header3 topic_ignorers">
<?php echo($tignorers); ?>
</div>

<?php endif; ?>

<?php if(empty($settings["hide_online_status"]) && empty($_SESSION["skin_properties"][$skin]["no_online_users"]) && empty($topic_data["is_private"])): ?>

<div class="header3 forum_readers">
<?php echo($freaders); ?>
</div>

<?php endif; ?>

<?php
@include "online_users_inc.php";
?>
