--
-- Make #__user_keys.user_id fit to #__users.username
--

ALTER TABLE `#__user_keys` MODIFY `user_id` varchar(150) NOT NULL;

--
-- Reset utf8/utf8mb4 conversion status to force conversion after update
-- because the conversion has been changed (handling of forgotten columns
-- and indexes added)
--

UPDATE `#__utf8_conversion` SET `converted` = 0;
