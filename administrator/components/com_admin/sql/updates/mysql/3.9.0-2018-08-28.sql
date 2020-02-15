ALTER TABLE `#__session` MODIFY `session_id` varbinary(192) NOT NULL;
ALTER TABLE `#__session` MODIFY `guest` tinyint(3) unsigned DEFAULT 1;
ALTER TABLE `#__session` MODIFY `time` int(11) NOT NULL DEFAULT 0;
