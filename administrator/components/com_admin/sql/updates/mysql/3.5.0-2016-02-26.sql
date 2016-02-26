--
-- Create a table for UTF-8 Multibyte (utf8mb4) conversion for MySQL in
-- order to check if the conversion has been performed and if not show a
-- message about database problem in the database schema view. 
--
-- Note: This table is created with charset utf8 and collation utf8_general_ci
-- so it will not cause an exception on upgrading a pre-3.5.0 with a database
-- not supporting utf8mb4, but all create table statements in future have to
-- use charset utf8mb4 and collation utf8mb4_general_ci.
--

CREATE TABLE IF NOT EXISTS `#__mysql_utf8mb4_test` (
  `converted` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=no; 1=yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_general_ci;

INSERT INTO `#__mysql_utf8mb4_test` (`converted`) VALUES (0);
