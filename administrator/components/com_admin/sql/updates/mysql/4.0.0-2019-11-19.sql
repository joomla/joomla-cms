DELETE FROM `#__menu` WHERE `link` = 'option=com_messages' AND `menutype` = 'main';
DELETE FROM `#__menu` WHERE `link` = 'option=com_messages&view=messages' AND `menutype` = 'main';
DELETE FROM `#__menu` WHERE `link` = 'option=com_messages&&task=message.add' AND `menutype` = 'main';
