<?php if(!empty($treaders) && (empty($settings["hide_online_status"]) && empty($_SESSION["skin_properties"][$skin]["no_online_users"])) || !empty($topic_data["is_private"])): ?>

<div class="header2 topic_readers">
<?php echo($treaders); ?>
</div>

<?php endif; ?>

<?php if(!empty($tignorers)): ?>
<div class="header2 topic_ignorers">
<?php echo($tignorers); ?>
</div>
<?php endif; ?>

<?php if(!empty($tblocked) && empty($_SESSION["skin_properties"][$skin]["no_online_users"])): ?>
<div class="header2 topic_blocked_users">
<?php echo($tblocked); ?>
</div>
<?php endif; ?>

<?php if(!empty($freaders) && empty($settings["hide_online_status"]) && empty($_SESSION["skin_properties"][$skin]["no_online_users"]) && empty($topic_data["is_private"])): ?>

<div class="header2 forum_readers">
<?php echo($freaders); ?>
</div>

<?php endif; ?>

<?php
@include "online_users_inc.php";
?>
