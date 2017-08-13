UPDATE "#__menu" SET "component_id" = (SELECT "extension_id" FROM "#__extensions" WHERE "element" = 'com_joomlaupdate') WHERE "link" = 'index.php?option=com_joomlaupdate';
