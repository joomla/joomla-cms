-- Replace language image UNIQUE index for a normal INDEX.
-- Note: This needs to be done in just one query because of the database schema checker.
ALTER TABLE `#__languages` DROP INDEX `idx_image`, ADD INDEX `idx_image` (`image`);
