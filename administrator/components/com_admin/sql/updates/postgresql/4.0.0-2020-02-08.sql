ALTER TABLE "#__banners" ALTER COLUMN "metakey" DROP NOT NULL;
ALTER TABLE "#__banner_clients" ALTER COLUMN "metakey" DROP NOT NULL;
ALTER TABLE "#__contact_details" ALTER COLUMN "metakey" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "metakey" DROP NOT NULL;
ALTER TABLE "#__languages" ALTER COLUMN "metakey" DROP NOT NULL;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "metakey" DROP NOT NULL;
ALTER TABLE "#__tags" ALTER COLUMN "metakey" SET DEFAULT '';
