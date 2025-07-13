UPDATE `#__assets` SET `title` = 'PUBLISH' WHERE `name` LIKE 'com_content.transition.%' AND `title` = 'Publish';
UPDATE `#__assets` SET `title` = 'UNPUBLISH' WHERE `name` LIKE 'com_content.transition.%' AND `title` = 'Unpublish';
UPDATE `#__assets` SET `title` = 'TRASH' WHERE `name` LIKE 'com_content.transition.%' AND `title` = 'Trash';
UPDATE `#__assets` SET `title` = 'ARCHIVE' WHERE `name` LIKE 'com_content.transition.%' AND `title` = 'Archive';
UPDATE `#__assets` SET `title` = 'FEATURE' WHERE `name` LIKE 'com_content.transition.%' AND `title` = 'Feature';
UPDATE `#__assets` SET `title` = 'UNFEATURE' WHERE `name` LIKE 'com_content.transition.%' AND `title` = 'Unfeature';
UPDATE `#__assets` SET `title` = 'PUBLISH_AND_FEATURE' WHERE `name` LIKE 'com_content.transition.%' AND `title` = 'Publish & Feature';
