-- Normalize redirect_links table default values.
ALTER TABLE `#__redirect_links` MODIFY `comment` varchar(255) NOT NULL DEFAULT '';
