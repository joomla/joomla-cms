ALTER TABLE "#__session" ALTER COLUMN "session_id" DROP DEFAULT;
ALTER TABLE "#__session" ALTER COLUMN "session_id" TYPE bytea USING "session_id"::bytea;
ALTER TABLE "#__session" ALTER COLUMN "session_id" SET NOT NULL;
ALTER TABLE "#__session" ALTER COLUMN "time" DROP DEFAULT,
                         ALTER COLUMN "time" TYPE integer USING "time"::integer;
ALTER TABLE "#__session" ALTER COLUMN "time" SET DEFAULT 0;
ALTER TABLE "#__session" ALTER COLUMN "time" SET NOT NULL;
