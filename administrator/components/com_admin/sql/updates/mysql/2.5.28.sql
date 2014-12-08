ALTER TABLE `#__user_profiles` CHANGE `profile_value` `profile_value` TEXT NOT NULL;

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(900, 'plg_quickicon_eosnotify', 'plugin', 'eosnotify', 'quickicon', 0, 1, 1, 1, '', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);
