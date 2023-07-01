--
-- Table structure for table `#__tuf_metadata`
--

CREATE TABLE IF NOT EXISTS `#__tuf_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `extension_id` int DEFAULT 0,
  `root` text DEFAULT NULL,
  `target` text DEFAULT NULL,
  `snapshot` text DEFAULT NULL,
  `timestamp` text DEFAULT NULL,
  `mirrors` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci COMMENT='Secure TUF Updates';

-- --------------------------------------------------------
INSERT INTO `#__tuf_metadata` (`extension_id`, `root`)
SELECT `extension_id`, '{"keytype": "ed25519", "scheme": "ed25519", "keyid": "02c3130c26fb3fe13fda279d578f3bc251f2ca3a42e5878de063e0ee345533c9", "keyid_hash_algorithms": ["sha256", "sha512"], "keyval": {"public": "f813a2882b305389cac36a9b8ebee7576ba7a7de671d2617074b03c12fb003aa", "private": "b7cb4fab28bae035a6fc5d46736e6f2d10ea4ef943e6aace8c637c1fd141ac72"}}' FROM `#__extensions` WHERE `type`='file' AND `element`='joomla';
