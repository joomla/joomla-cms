--
-- Step 3 of the UTF-8 Multibyte (utf8mb4) conversion for MySQL
--
-- Drop indexes which will be added again in step 4, utf8mb4-conversion-04.sql.
--
-- Do not rename this file or any other of the utf8mb4-conversion-*.sql
-- files unless you want to change PHP code, too.
--
-- This file here will be processed ignoring any exceptions caused by indexes
-- to be dropped do not exist.
--
-- The file for step 4 will the be processed with reporting exceptions.
--

ALTER TABLE `#__finder_terms` DROP KEY `idx_term`;
