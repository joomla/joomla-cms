--
-- Step 3 of the UTF-8 Multibyte (utf8mb4) conversion for MySQL
--
-- Convert the tables for action logs and the privacy suite which have been
-- forgotten to be added to the utf8mb4 conversion before.
--
-- This file here will be processed with reporting exceptions, in opposite
-- to the file for step 1.
--

--
-- Step 3.1: Convert action logs and privacy suite tables to utf8mb4 character set with
-- utf8mb4_unicode_ci collation
-- Note: The database driver for mysql will change utf8mb4 to utf8 if utf8mb4 is not supported
--

ALTER TABLE `#__action_logs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__action_logs_extensions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__action_logs_users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__action_log_config` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__privacy_consents` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__privacy_requests` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Step 3.2: Set default character set and collation for previously converted tables
--

ALTER TABLE `#__action_logs` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__action_logs_extensions` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__action_logs_users` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__action_log_config` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__privacy_consents` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `#__privacy_requests` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
