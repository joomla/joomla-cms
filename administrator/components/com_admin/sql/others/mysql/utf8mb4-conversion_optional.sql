--
-- This file contains the part of the UTF-8 Multibyte (utf8mb4) conversion for MySQL
-- for optional extensions which might be still installed or not on an updated installation.
--
-- In opposite to file utf8mb4-conversion.sql, any table handled by this file here doesn't
-- need to exist.
--

--
-- Step 1: Convert all tables to utf8mb4 character set with utf8mb4_unicode_ci collation.
--

ALTER TABLE `#__core_log_searches` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

--
-- Step 2: Set collation to utf8mb4_bin for formerly utf8_bin collated columns.
--

--
-- Step 3: Set default character set and collation for all tables
--

ALTER TABLE `#__core_log_searches` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
