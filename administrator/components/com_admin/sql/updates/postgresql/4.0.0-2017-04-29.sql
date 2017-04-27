ALTER TABLE ""#__extensions" ADD "autoload"" VARCHAR(255) NULL DEFAULT NULL AFTER "params";

-- Update com_content with autoloader file
UPDATE `#__extensions` SET `autoload` = 'autoload.php' WHERE `name` = 'com_content';
