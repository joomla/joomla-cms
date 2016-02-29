--
-- Create a table for UTF-8 Multibyte (utf8mb4) conversion for MySQL in
-- order to check if the conversion has been performed and if not show a
-- message about database problem in the database schema view. 
--

CREATE TABLE IF NOT EXISTS `#__utf8_conversion` (
  `converted` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
