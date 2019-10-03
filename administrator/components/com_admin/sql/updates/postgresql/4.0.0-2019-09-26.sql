ALTER TABLE "#__privacy_requests" ALTER COLUMN "requested_at" DROP DEFAULT;
ALTER TABLE "#__privacy_requests" ALTER COLUMN "confirm_token_created_at" DROP NOT NULL;
ALTER TABLE "#__privacy_requests" ALTER COLUMN "confirm_token_created_at" DROP DEFAULT;

ALTER TABLE "#__privacy_consents" ALTER COLUMN "created" DROP DEFAULT;
