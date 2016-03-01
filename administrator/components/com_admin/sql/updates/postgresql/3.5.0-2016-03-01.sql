ALTER TABLE "#__redirect_links" DROP CONSTRAINT "#__redirect_links_idx_link_old"
ALTER TABLE "#__redirect_links" ALTER COLUMN "old_url" TYPE varchar(2048);
ALTER TABLE "#__redirect_links" ALTER COLUMN "new_url" TYPE varchar(2048);
ALTER TABLE "#__redirect_links" ALTER COLUMN "referer" TYPE varchar(2048);
ALTER TABLE "#__redirect_links" ADD CONSTRAINT "#__redirect_links_idx_old_url" UNIQUE ("old_url");
