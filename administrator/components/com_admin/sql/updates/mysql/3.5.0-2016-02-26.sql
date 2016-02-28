--
-- Create a table for UTF-8 Multibyte (utf8mb4) conversion for MySQL in
-- order to check if the conversion has been performed and if not show a
-- message about database problem in the database schema view. 
--
-- Note: This table is created with charset utf8 and collation utf8_unicode_ci
-- so it will not cause an exception on upgrading a pre-3.5.0 with a database
-- not supporting utf8mb4, but all create table statements in future have to
-- use charset utf8mb4 and collation utf8mb4_unicode_ci.
--

CREATE TABLE IF NOT EXISTS `#__utf8_conversion` (
  `converted` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_unicode_ci;
