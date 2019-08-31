DROP INDEX "#__privacy_requests_idx_checked_out";
ALTER TABLE "#__privacy_requests" DROP COLUMN "checked_out";
ALTER TABLE "#__privacy_requests" DROP COLUMN "checked_out_time";
