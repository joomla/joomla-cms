ALTER TABLE "#__redirect_links" DROP CONSTRAINT "#__redirect_links_idx_link_old";

--
-- The following statement had to be modified for 3.6.0 by adding the
-- NOT NULL, to be consistent with new install.
-- See also 3.6.0-2016-04-06.sql for updating 3.5.0 or 3.5.1
--
ALTER TABLE "#__redirect_links" ALTER COLUMN "old_url" TYPE character varying(2048) NOT NULL;

ALTER TABLE "#__redirect_links" ALTER COLUMN "new_url" TYPE character varying(2048);

--
-- The following statement had to be modified for 3.6.0 by adding the
-- NOT NULL, to be consistent with new install.
-- See also 3.6.0-2016-04-06.sql for updating 3.5.0 or 3.5.1
--
ALTER TABLE "#__redirect_links" ALTER COLUMN "referer" TYPE character varying(2048) NOT NULL;

CREATE INDEX "#__idx_link_old" ON "#__redirect_links" ("old_url");
