ALTER TABLE "#__menu" ADD COLUMN "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL;
ALTER TABLE "#__menu" ADD COLUMN "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL;
