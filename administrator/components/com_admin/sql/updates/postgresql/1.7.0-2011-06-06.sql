CREATE TABLE "#__associations" (
  -- A reference to the associated item.
  "id" character varying(50) NOT NULL,
  -- The context of the associated item.
  "context" character varying(50) NOT NULL,
  -- The key for the association computed from an md5 on associated ids.
  "key" character(32) NOT NULL,
  CONSTRAINT "idx_context_id" PRIMARY KEY ("context", "id")
);
CREATE INDEX "#__associations_idx_key" on #__associations ("key");

COMMENT ON COLUMN "#__associations"."id" IS 'A reference to the associated item.';
COMMENT ON COLUMN "#__associations"."context" IS 'The context of the associated item.';
COMMENT ON COLUMN "#__associations"."key" IS 'The key for the association computed from an md5 on associated ids.';
