--
-- Increasing size of the URL field in com_newsfeeds
--

ALTER TABLE `#__newsfeeds` MODIFY `link` VARCHAR(2048) NOT NULL;
