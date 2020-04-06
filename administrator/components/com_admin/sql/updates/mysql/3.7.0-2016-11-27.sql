-- Normalize modules content field with other db systems. Add default value.
ALTER TABLE `#__modules` MODIFY `content` text;
