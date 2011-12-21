ALTER TABLE "#__redirect_links" ALTER COLUMN "old_url" character varying(255);
ALTER TABLE "#__redirect_links" ALTER COLUMN "new_url" character varying(255);
ALTER TABLE "#__categories" ALTER COLUMN "access" bigint;
ALTER TABLE "#__contact_details" ALTER COLUMN "access" bigint;
ALTER TABLE "#__extensions" ALTER COLUMN "access" bigint;
ALTER TABLE "#__menu" ALTER COLUMN "access" bigint;
ALTER TABLE "#__modules" ALTER COLUMN "access" bigint;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "access" bigint;