CREATE TABLE IF NOT EXISTS `#__wf_profiles` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `users` text NOT NULL,
    `types` text NOT NULL,
    `components` text NOT NULL,
    `area` tinyint(3) NOT NULL,
    `device` varchar(255) NOT NULL,
    `rows` text NOT NULL,
    `plugins` text NOT NULL,
    `published` tinyint(3) NOT NULL,
    `ordering` int(11) NOT NULL,
    `checked_out` int(11) NOT NULL,
    `checked_out_time` datetime NOT NULL,
    `params` text NOT NULL,
    PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;