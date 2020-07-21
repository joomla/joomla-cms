CREATE TABLE IF NOT EXISTS `#__pms_finance_data` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `wkn` varchar(50) NOT NULL,
    `symbol` VARCHAR(150) NOT NULL,
    `value` varchar(255) NOT NULL,
    `datetime` int(15) NOT NULL DEFAULT 0,
    `high` varchar(255) NOT NULL,
    `low` varchar(255) NOT NULL,
    `open` varchar(255) NOT NULL,
    `close` varchar(255) NOT NULL,
    `volume` int(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__pms_finance_data`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `datetime` (`datetime`,`wkn`,`symbol`) USING BTREE;
  
ALTER TABLE `#__pms_finance_data`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

CREATE TABLE IF NOT EXISTS `#__pms_finance_stocks` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `wkn` varchar(10) NOT NULL,
    `symbol` varchar(100) NOT NULL DEFAULT 0,
    `praefix` varchar(100) NOT NULL COMMENT 'Präfix des Symbols für Download URL',
    `name` varchar(150) NOT NULL DEFAULT 0,
    `url` varchar(255) NOT NULL,
    `type` varchar(100) NOT NULL DEFAULT 0,
    `lastupdate` int(15) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__pms_finance_stocks`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `wkn` (`wkn`),
    ADD UNIQUE KEY `symbol` (`symbol`);

ALTER TABLE `#__pms_finance_stocks`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

INSERT INTO `#__pms_finance_stocks` (`wkn`, `name`, `url`, `type`, `symbol`, `praefix`, `lastupdate`) VALUES
('846900', 'DAX Performance Index', 'https://www.finanzen.net/index/dax', 'index', 'GDAXI', '%5E', 1594512000),
('969420', 'Dow Jones Index', 'https://www.finanzen.net/index/dow_jones', 'index', 'DJI', '%5E', 1594512000),
('593393', 'iShares Core DAX ETF', 'https://www.finanzen.net//etf/ishares-core-dax-etf-de0005933931', 'etf', 'EXS1.DE', '', 1594512000),
('555200', 'Deutsche Post AG', 'https://www.finanzen.net/aktien/deutsche_post-aktie', 'stock', 'DPW.DE', '', 1594512000);

CREATE TABLE IF NOT EXISTS `#__pms_finance_orders` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `userid` int(10) NOT NULL DEFAULT 0,
    `wkn` varchar(10) NOT NULL,
    `type` varchar(255) NOT NULL,
    `value` varchar(100) NOT NULL DEFAULT 0, 
    `options` text  NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__pms_finance_data_files` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `hash` varchar(50) NOT NULL,
    `path` varchar(150) NOT NULL,
    `filename` varchar(40) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `key` (`path`, `filename`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `#__pms_finance_notification_settings` (
  `id` int(11) NOT NULL,
  `ordertyp` varchar(25) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__pms_finance_data` ADD `200d` VARCHAR(20) NOT NULL AFTER `volume`;
ALTER TABLE `#__pms_finance_notification_settings` ADD UNIQUE( `ordertyp`);