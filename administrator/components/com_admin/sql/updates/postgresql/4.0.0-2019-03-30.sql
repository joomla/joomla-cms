-- The following two statements were modified for 4.1.1 by adding the "/** CAN FAIL **/" installer hint.
-- See https://github.com/joomla/joomla-cms/pull/37156
ALTER TABLE "#__menu" ADD COLUMN "publish_up" timestamp without time zone /** CAN FAIL **/;
ALTER TABLE "#__menu" ADD COLUMN "publish_down" timestamp without time zone /** CAN FAIL **/;

ALTER TABLE "#__menu" ALTER COLUMN "checked_out_time" DROP NOT NULL;
ALTER TABLE "#__menu" ALTER COLUMN "checked_out_time" DROP DEFAULT;

UPDATE "#__menu" SET "checked_out_time" = NULL WHERE "checked_out_time" = '1970-01-01 00:00:00';
