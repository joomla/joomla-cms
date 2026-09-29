--
-- Remove uniqueness of user_keys.series
--

ALTER TABLE "#__user_keys"
DROP CONSTRAINT "#__user_keys_series";
