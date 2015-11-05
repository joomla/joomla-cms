DELETE FROM `#__menu` WHERE `title` = 'com_messages_read' AND `client_id` = 1;
UPDATE `#__menu` SET `lft` = `lft` - 2 WHERE `lft` > 20;
UPDATE `#__menu` SET `rgt` = `rgt` - 2 WHERE `rgt` > 21;
