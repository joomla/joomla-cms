--
-- This file contains the part of the UTF-8 Multibyte (UTF8mb4) conversion for MySQL
-- for optional extensions which might be still installed or not on an updated installation.
--
-- In opposite to file UTF8mb4-conversion.sql, any table handled by this file here doesn't
-- need to exist.
--

--
-- Step 1: Convert all tables to UTF8mb4 character set with UTF8mb4_unicode_ci collation.
--

ALTER TABLE `#__core_log_searches` CONVERT TO CHARACTER SET UTF8mb4 COLLATE UTF8mb4_unicode_ci;

--
-- Step 2: Set collation to UTF8mb4_bin for formerly UTF8_bin collated columns.
--

--
-- Step 3: Set default character set and collation for all tables
--

ALTER TABLE `#__core_log_searches` DEFAULT CHARACTER SET UTF8mb4 COLLATE UTF8mb4_unicode_ci;
