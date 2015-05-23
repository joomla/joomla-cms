ALTER TABLE "#__redirect_links" ADD COLUMN "header" INTEGER DEFAULT 301 NOT NULL;
ALTER TABLE "#__redirect_links" ALTER COLUMN "new_url" DROP NOT NULL;
