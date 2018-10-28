DROP INDEX [idx_checkout] ON [#__privacy_requests];
ALTER TABLE [#__privacy_requests] DROP COLUMN [checked_out];
ALTER TABLE [#__privacy_requests] DROP COLUMN [checked_out_time];
