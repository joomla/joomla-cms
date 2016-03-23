--
-- Make #__user_keys.user_id fit to #__users.username
--

ALTER TABLE `#__user_keys` MODIFY `user_id` varchar(150) NOT NULL;
