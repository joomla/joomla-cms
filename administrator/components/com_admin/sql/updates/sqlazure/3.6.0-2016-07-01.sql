ALTER TABLE [#__banners] ADD COLUMN [track_frequency] [nvarchar](6) NOT NULL DEFAULT 'hourly' AFTER [track_impressions];
ALTER TABLE [#__banner_clients] ADD COLUMN [track_frequency] [nvarchar](6) NOT NULL DEFAULT 'hourly';
