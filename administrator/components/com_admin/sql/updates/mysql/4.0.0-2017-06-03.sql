ALTER TABLE `#__extensions` ADD COLUMN `changelogurl` text AFTER `element`;
ALTER TABLE `#__updates` ADD COLUMN `changelogurl` text AFTER `infourl`;
