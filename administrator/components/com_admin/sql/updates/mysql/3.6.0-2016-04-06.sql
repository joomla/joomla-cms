ALTER TABLE `#__redirect_links` MODIFY `new_url` VARCHAR(2048);
UPDATE `#__languages` SET `access` = 1 WHERE `title` = 'English (UK)' AND `access` = 0;
