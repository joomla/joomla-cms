--
-- Remove uniqueness of user_keys.series
--

ALTER TABLE "#__user_keys"
DROP INDEX "series";

ALTER TABLE "#__user_keys"
ADD INDEX "series" ("series");
