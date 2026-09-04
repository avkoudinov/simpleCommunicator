<?php
// These parameters give the possibility to warn
// visitors about regular backups.
// If specified, a warning dialog with the sepcified
// start and end time will be shown once per session.
//
// $backup_days - array of the week days, when the backups 
// are performed.
//
// $backup_start - the start time in hours and minutes (01:00)
//
// $backup_end - the end time in hours and minutes (07:00)

$backup_days = array(1,2,3,4,5,6,7);
// Format: 01:00
$backup_start = "";
// Format: 07:00
$backup_end = "";

// These parameters give the possibility to warn
// visitors about planned maintenance.
// If specified, a warning dialog with the sepcified
// start and end time will be shown once per session.

// Format: 2017-07-30 15:46
////$maintenance_start = "2022-12-03 15:30";
////$maintenance_end = "2022-12-03 17:00";
$maintenance_start = "2026-08-03 15:00";
$maintenance_end = "2026-08-10 09:00";

// Setting this parameter switches the forum into
// maintenance modus with the corresponding text
// that maintenance will be until the specified time.

// Format: 2017-07-30 15:46
$maintenance_until = "";
//$maintenance_until = "2026-02-18 20:00";

// Detailed comment which maintenance jobs are performed
// one for all langauges or dedicated.
////$maintenance_comment = "Технические работы по обновлению ОС и переходу на php 8. Ориентировочное время недоступности форума до 2 часов. Отслеживание: http://status.itwrks.org/ и https://t.me/it_works_org";
////$maintenance_comment = "Технические работы по обновлению форума. Ориентировочное время недоступности форума - 30 минут. Отслеживание: http://status.itwrks.org/ и https://t.me/it_works_org";
////$maintenance_comment = "Технические работы по обновлению OC. Ориентировочное время недоступности форума - 15-60 минут. Отслеживание: https://t.me/it_works_org";
$maintenance_comment = "DC-T: Технические работы";
////$maintenance_comment = "DC-T: Технические работы. Переусадка бэкплейна на сервере";
////$maintenance_comment = "Технические работы по изменению конфигурации гипервизоров";
////$maintenance_comment = "Работы на форуме";
////$maintenance_comment = "Обновление форума: патч";
////$maintenance_comment = "Товарищи! Проводятся технические работы по миграции и завершению настройки сервисов. Вероятна недоступность в любой момент времени и без дополнительных уведомлений. Извините!";
////$maintenance_comment = "Импорт SQL.RU";
////$maintenance_comment = "Восстановление SQL.RU";
////$maintenance_comment = "Оптимизация таблиц";
////$maintenance_comment = "Изменение параметров работы сервера MySQL";
////$maintenance_comment = "Изменения в схеме работы ВМ в среде виртуализации";
////$maintenance_comment = "Плановая перезагрузка сервера СУБД";
////$maintenance_comment = "Восстановление старой версии Дедофорума";
////$maintenance_comment = "Технические работы по обновлению ОС и переходу на php 8.4";
////$maintenance_comment = "Рестарт СУБД";
////$maintenance_comment = "Резервное копирование виртуальных машин";
////$maintenance_comment = "Технические работы по миграции на новые серверы. Полная неработоспособность сервисов с 23:00 2026.01.02. Сроки возвращения в работу точно неизвестны";
$maintenance_comment_lang["ru"] = "";
$maintenance_comment_lang["ua"] = "";
$maintenance_comment_lang["en"] = "";
$maintenance_comment_lang["de"] = "";

// Link to the infromation about the maintenance.
$maintenance_link = "https://t.me/it_works_org";
$maintenance_link_lang["ru"] = "";
$maintenance_link_lang["ua"] = "";
$maintenance_link_lang["en"] = "";
$maintenance_link_lang["de"] = "";

// If a password is set, the admin will be able to 
// view and test the forum in the normal modus,
// whereas for all other users, the maintenance
// modus will be still active. This function is useful 
// for checking the forum when the maintenance is
// complete and before the maintenance modus is turned off.
//
// Example: forums.php?admdebug=password
$adm_debug_password = "";
?>
