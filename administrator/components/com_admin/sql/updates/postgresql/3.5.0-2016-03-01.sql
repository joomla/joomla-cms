ALTER TABLE "#__redirect_links" DROP CONSTRAINT "#__redirect_links_idx_link_old"
ALTER TABLE "#__redirect_links" ALTER COLUMN "old_url" TYPE varchar(2048);
ALTER TABLE "#__redirect_links" ALTER COLUMN "new_url" TYPE varchar(2048);
ALTER TABLE "#__redirect_links" ALTER COLUMN "referer" TYPE varchar(2048);
CREATE INDEX "#__idx_link_old" ON "#__redirect_links" ("old_url");
