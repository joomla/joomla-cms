UPDATE `#__users` SET `id`=5 WHERE `id` = (SELECT `user_id` FROM `#__user_usergroup_map` WHERE `group_id`=8);
UPDATE `#__user_usergroup_map` SET `user_id`=5 WHERE `group_id`=8;
