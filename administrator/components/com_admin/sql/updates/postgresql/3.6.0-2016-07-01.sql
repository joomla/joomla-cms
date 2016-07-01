ALTER TABLE "#__banners" ADD COLUMN "track_frequency" VARCHAR(6) NOT NULL DEFAULT 'hourly' AFTER "track_impressions";
ALTER TABLE "#__banner_clients" ADD COLUMN "track_frequency" VARCHAR(6) NOT NULL DEFAULT 'hourly';
