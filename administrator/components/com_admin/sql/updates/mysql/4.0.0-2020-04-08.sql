--
-- Change the column type before adding the foreign keys. We're adding the unsigned attribute to users and changing
-- the user_id column in user group mapping from 10 to 11 length.
--
ALTER TABLE `#__users` MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__user_usergroup_map` MODIFY `user_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__user_usergroup_map` ADD CONSTRAINT `fk_user_group_user_id` FOREIGN KEY (`user_id`) REFERENCES `#__users` (`id`) ON DELETE CASCADE;
ALTER TABLE `#__user_usergroup_map` ADD CONSTRAINT `fk_user_group_group_id` FOREIGN KEY (`group_id`) REFERENCES `#__usergroups` (`id`) ON DELETE CASCADE;
