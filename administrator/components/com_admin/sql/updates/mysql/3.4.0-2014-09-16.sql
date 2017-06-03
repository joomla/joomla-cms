ALTER TABLE `#__redirect_links` ADD COLUMN `header` smallint(3) NOT NULL DEFAULT 301;
--
-- The following statement has to be disabled because it conflicts with
-- a later change added with Joomla! 3.5.0 for long URLs in this table
--
-- ALTER TABLE `#__redirect_links` MODIFY `new_url` varchar(255);
