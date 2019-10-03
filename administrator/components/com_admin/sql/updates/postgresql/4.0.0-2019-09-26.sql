ALTER TABLE "#__privacy_requests" ALTER COLUMN "requested_at" DROP DEFAULT;
ALTER TABLE "#__privacy_requests" ALTER COLUMN "confirm_token_created_at" DROP DEFAULT;

ALTER TABLE "#__privacy_consents" ALTER COLUMN "created" DROP DEFAULT;

UPDATE "#__privacy_requests" SET "confirm_token_created_at" = "requested_at" WHERE "confirm_token_created_at" = '0000-00-00 00:00:00';

