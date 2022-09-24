--
-- Increase the size of the htmlbody field in the #__mail_templates table
--
ALTER TABLE `#__mail_templates` MODIFY `htmlbody` mediumtext NOT NULL COLLATE 'utf8mb4_unicode_ci';
