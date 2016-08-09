ALTER TABLE `#__nullDate_conversion` CHANGE `converted` `converted` datetime NOT NULL DEFAULT $T$;
UPDATE `#__nullDate_conversion` SET `converted` = $T$;
