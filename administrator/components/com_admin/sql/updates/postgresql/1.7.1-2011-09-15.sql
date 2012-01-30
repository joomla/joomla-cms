ALTER TABLE "#__categories" ALTER COLUMN "description" TYPE text;

ALTER TABLE "#__session" ALTER COLUMN data TYPE text;
ALTER TABLE "#__session" ALTER COLUMN "session_id" TYPE character varying(200);