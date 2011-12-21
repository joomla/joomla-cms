ALTER TABLE "#__content" ALTER COLUMN "alias" TYPE character varying(255),
ALTER COLUMN "position" SET DEFAULT '',
ALTER COLUMN "position" SET NOT NULL;

COMMENT ON COLUMN "#__content"."alias" IS 'Deprecated in Joomla! 3.0';