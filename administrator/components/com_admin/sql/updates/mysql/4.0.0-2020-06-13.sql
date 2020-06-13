
CREATE TABLE IF NOT EXISTS `#__jobs` (
  `element` varchar(100) NOT NULL,
  `folder` varchar(100) NOT NULL,
  PRIMARY KEY (`element`, `folder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params", `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(0, 'plg_system_scheduler', 'plugin', 'scheduler', 'system', 0, 1, 1, 0, '', '{"timeout":"3","webcron":"0","webcronkey":"mywebcronactivationkey","lastrun":"0","unit":"60","taskid":"0"}',  0, NULL, 0, 0);
