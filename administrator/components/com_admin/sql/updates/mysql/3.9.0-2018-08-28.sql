ALTER TABLE `#__session` MODIFY `session_id` varbinary(192) NOT NULL;
ALTER TABLE `#__session` MODIFY `guest` tinyint unsigned DEFAULT 1;
ALTER TABLE `#__session` MODIFY `time` int NOT NULL DEFAULT 0;
