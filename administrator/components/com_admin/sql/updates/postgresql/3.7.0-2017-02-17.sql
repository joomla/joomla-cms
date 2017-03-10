-- Normalize contact_details table default values.
ALTER TABLE "#__contact_details" ALTER COLUMN "name" DROP DEFAULT;
ALTER TABLE "#__contact_details" ALTER COLUMN "alias" DROP DEFAULT;
ALTER TABLE "#__contact_details" ALTER COLUMN "sortname1" SET DEFAULT '';
ALTER TABLE "#__contact_details" ALTER COLUMN "sortname2" SET DEFAULT '';
ALTER TABLE "#__contact_details" ALTER COLUMN "sortname3" SET DEFAULT '';
ALTER TABLE "#__contact_details" ALTER COLUMN "language" DROP DEFAULT;
ALTER TABLE "#__contact_details" ALTER COLUMN "xreference" SET DEFAULT '';
