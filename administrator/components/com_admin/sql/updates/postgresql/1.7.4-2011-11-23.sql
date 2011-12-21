ALTER TABLE "#__newsfeeds" ALTER COLUMN "alias" TYPE character varying(255),
ALTER COLUMN "position" SET DEFAULT '',
ALTER COLUMN "position" SET NOT NULL;

ALTER TABLE "#__content" ALTER COLUMN "alias" TYPE character varying(255),
ALTER COLUMN "position" SET DEFAULT '',
ALTER COLUMN "position" SET NOT NULL;

COMMENT ON COLUMN "#__content"."alias" IS '';


ALTER TABLE "#__content" ALTER COLUMN "title_alias" TYPE character varying(255),
ALTER COLUMN "position" SET DEFAULT '',
ALTER COLUMN "position" SET NOT NULL;

COMMENT ON COLUMN "#__content"."title_alias" IS 'Deprecated in Joomla! 3.0';