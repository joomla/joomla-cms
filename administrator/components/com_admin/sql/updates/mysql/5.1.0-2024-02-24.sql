--
-- Table structure for table `#__tuf_metadata`
--

CREATE TABLE IF NOT EXISTS `#__tuf_metadata` (
  `id` int NOT NULL AUTO_INCREMENT,
  `update_site_id` int DEFAULT 0,
  `root` text DEFAULT NULL,
  `targets` text DEFAULT NULL,
  `snapshot` text DEFAULT NULL,
  `timestamp` text DEFAULT NULL,
  `mirrors` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci COMMENT='Secure TUF Updates';

-- --------------------------------------------------------
-- The following INSERT statement has been modified to avoid an SQL error
-- when there is more than 1 update site for the Joomla core.
-- See https://github.com/joomla/joomla-cms/pull/43169 for details.
--
INSERT INTO `#__tuf_metadata` (`update_site_id`, `root`)
SELECT ue.`update_site_id`,
       '{"signed":{"_type":"root","spec_version":"1.0","version":2,"expires":"2025-03-02T11:22:17Z","keys":{"07eb082f367c034a95878687f6648aa76d93652b6ee73e58817053d89af6c44f":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"9b2af2d9b9727227735253d795bd27ea8f0e294a5f3603e822dc5052b44802b9"}},"1b1b1dd55b2c1c7258714cf1c1ae06f23e4607b28c762d016a9d81c48ffe5669":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"a18e5ebabc19d5d5984b601a292ece61ba3662ab2d071dc520da5bd4f8948799"}},"2dcaf3d0e552f150792f7c636d45429246dcfa34ac35b46a44f5c87cd17d457e":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"cb0a7a131961a20edea051d6dc2b091fb650bd399bd8514adb67b3c60db9f8f9"}},"31dd7c7290d664c9b88c0dead2697175293ea7df81b7f24153a37370fd3901c3":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"589d029a68b470deff1ca16dbf3eea6b5b3fcba0ae7bb52c468abc7fb058b2a2"}},"9e41a9d62d94c6a1c8a304f62c5bd72d84a9f286f27e8327cedeacb09e5156cc":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"6043c8bacc76ac5c9750f45454dd865c6ca1fc57d69e14cc192cfd420f6a66a9"}}},"roles":{"root":{"keyids":["1b1b1dd55b2c1c7258714cf1c1ae06f23e4607b28c762d016a9d81c48ffe5669","2dcaf3d0e552f150792f7c636d45429246dcfa34ac35b46a44f5c87cd17d457e"],"threshold":1},"snapshot":{"keyids":["07eb082f367c034a95878687f6648aa76d93652b6ee73e58817053d89af6c44f","2dcaf3d0e552f150792f7c636d45429246dcfa34ac35b46a44f5c87cd17d457e"],"threshold":1},"targets":{"keyids":["31dd7c7290d664c9b88c0dead2697175293ea7df81b7f24153a37370fd3901c3"],"threshold":1},"timestamp":{"keyids":["9e41a9d62d94c6a1c8a304f62c5bd72d84a9f286f27e8327cedeacb09e5156cc"],"threshold":1}},"consistent_snapshot":true},"signatures":[{"keyid":"2dcaf3d0e552f150792f7c636d45429246dcfa34ac35b46a44f5c87cd17d457e","sig":"2a225a560ec0837b721d4c5e379fedbd3c7c9079a94e6b31e47e0184c8b95421b6036b4286c5d90f29ab4c468d79a712fdb65e96511394ceb3aa8e2b3983a501"},{"keyid":"1b1b1dd55b2c1c7258714cf1c1ae06f23e4607b28c762d016a9d81c48ffe5669","sig":"8ce0b2a7bdc1e6dcba12081f440510df0a593c072dcf591631c2dd0f456844a7da63be8e8ac31ffbddf42641fde84dc733a336031d182c2163b4c1eaf2117005"}]}'
  FROM `#__update_sites_extensions` AS ue JOIN `#__extensions` AS e ON (e.`extension_id` = ue.`extension_id`)
 WHERE e.`type`='file' AND e.`element`='joomla';

-- --------------------------------------------------------
-- The following UPDATE statement has been modified to avoid an SQL error
-- when there is more than 1 update site for the Joomla core.
-- See https://github.com/joomla/joomla-cms/pull/42988 for details.
--
UPDATE `#__update_sites`
   SET `type` = 'tuf', `location` = 'https://update.joomla.org/cms/'
 WHERE `update_site_id` IN (SELECT ue.`update_site_id` FROM `#__update_sites_extensions` AS ue JOIN `#__extensions` AS e ON (e.`extension_id` = ue.`extension_id`) WHERE e.`type`='file' AND e.`element`='joomla');
