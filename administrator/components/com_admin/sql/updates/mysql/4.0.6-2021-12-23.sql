UPDATE `#__extensions` SET `checked_out` = NULL WHERE `type` = 'package' AND `element` = 'pkg_search' AND `checked_out` = 0;
