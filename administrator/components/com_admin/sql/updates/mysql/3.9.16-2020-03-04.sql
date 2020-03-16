ALTER TABLE `#__users` DROP INDEX `username`;
ALTER TABLE `#__users` ADD UNIQUE INDEX `idx_username` (`username`);