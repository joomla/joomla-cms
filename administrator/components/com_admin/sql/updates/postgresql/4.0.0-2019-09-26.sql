ALTER TABLE "#__privacy_requests" ALTER COLUMN "requested_at" DROP DEFAULT;

ALTER TABLE "#__privacy_requests" ALTER COLUMN "confirm_token_created_at" DROP NOT NULL;
ALTER TABLE "#__privacy_requests" ALTER COLUMN "confirm_token_created_at" DROP DEFAULT;

ALTER TABLE "#__privacy_consents" ALTER COLUMN "created" DROP DEFAULT;

UPDATE "#__privacy_requests" SET "confirm_token_created_at" = NULL WHERE "confirm_token_created_at" = '1970-01-01 00:00:00';
