CREATE TABLE `#__session` (
  `session_id` TEXT NOT NULL,
  `time` INTEGER NOT NULL,
  `data` BLOB NOT NULL,
  CONSTRAINT `idx_session` PRIMARY KEY (`session_id`)
);
