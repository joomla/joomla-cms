ALTER TABLE "#__content" ALTER COLUMN "alias" TYPE character varying(255);
ALTER TABLE "#__content" ALTER COLUMN "alias" SET DEFAULT '';
ALTER TABLE "#__content" ALTER COLUMN "alias" SET NOT NULL;

COMMENT ON COLUMN "#__content"."alias" IS 'Deprecated in Joomla! 3.0';