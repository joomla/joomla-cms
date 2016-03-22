--
-- Reset utf8/utf8mb4 conversion status to force conversion after update
-- because the conversion has been changed (handling of forgotten columns
-- and indexes added)
--

UPDATE `#__utf8_conversion` SET `converted` = 0;
