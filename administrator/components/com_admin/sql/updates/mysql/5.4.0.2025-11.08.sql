INSERT INTO `#__modules` (`title`, `note`, `content`, `ordering`, `position`, `checked_out`, `checked_out_time`, `publish_up`, `publish_down`, `published`, `module`, `access`, `showtitle`, `params`, `client_id`, `language`) VALUES
('Backward Compatibility', '', '', 1, 'status', 0, NULL, NULL, NULL, 1, 'mod_backward', 1, 1, '', 1, '*');

INSERT INTO `#__modules_menu` (`moduleid`, `menuid`) VALUES (LAST_INSERT_ID(), 0);