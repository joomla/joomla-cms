-- Replace language image UNIQUE index for a normal INDEX.
ALTER TABLE `#__languages` DROP INDEX `idx_image`;
ALTER TABLE `#__languages` ADD INDEX `idx_image` (`image`);
