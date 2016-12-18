-- Replace language image UNIQUE index for a normal INDEX.
-- Note: This needs to be done in just one query because of the database schema checker.
ALTER TABLE "#__languages" DROP CONSTRAINT "#__idx_image", ADD INDEX "#__idx_image" ("image");
