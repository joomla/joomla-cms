--
-- Table structure for table "#__tuf_metadata"
--

CREATE TABLE IF NOT EXISTS "#__tuf_metadata" (
"id" serial NOT NULL,
"extension_id" bigint DEFAULT 0 NOT NULL,
"root" text DEFAULT NULL,
"targets" text DEFAULT NULL,
"snapshot" text DEFAULT NULL,
"timestamp" text DEFAULT NULL,
"mirrors" text DEFAULT NULL,
PRIMARY KEY ("id")
);

COMMENT ON TABLE "#__tuf_metadata" IS 'Secure TUF Updates';

INSERT INTO `#__tuf_metadata` (`update_site_id`, `root`)
VALUES ((SELECT ue.`update_site_id` FROM `#__update_sites_extensions` AS ue JOIN `#__extensions` AS e ON (e.`extension_id` = ue.`extension_id`) WHERE e.`type`='file' AND e.`element`='joomla'), '{"signed":{"_type":"root","spec_version":"1.0","version":1,"expires":"2028-12-06T15:31:52Z","keys":{"1689c5951cfc8a8cb4e3535c6ddc3f8d5c66e2effd4b7aae3506995f145da2a0":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"71c24873013b6f21aca791f45dcd9ddb5842a97bf72ac73c211742c2659a97ff"}},"696a7598c714e545bb8a3a4248d82bf4c66486d142e226c1e06601a14f4d939a":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"9fac963aac4e14f948a7c2d6b3fa2232f6cb5a08bf6a8b6100bc6e68b0683c1c"}},"70c4fb4ffe87b8d75559092c75bc038d587790bf2ecb9d8d6c6c0fae6705c750":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"d08225342af7a8075bf210bd62154567140a8e14d824743e58b8e7e64ee8ad0b"}},"92933ea840e57ad3db67c748d1a309c4a7d8be3f70d8bbbd3cff9c4cca3bcf7b":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"8d70ac7574e64f209bff3d7c1d8b8ab6e34cf4419dd09f0d222354dceee986d7"}},"f9854d7c61e9413f4d83678be7d50310cc9e062027746d8936ba4736e75224e9":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"b7a3d08989b5885d78e93425daacf3a71b0e190759e1a8633aa41bdb3ec3cd97"}}},"roles":{"root":{"keyids":["70c4fb4ffe87b8d75559092c75bc038d587790bf2ecb9d8d6c6c0fae6705c750"],"threshold":1},"snapshot":{"keyids":["f9854d7c61e9413f4d83678be7d50310cc9e062027746d8936ba4736e75224e9"],"threshold":1},"targets":{"keyids":["696a7598c714e545bb8a3a4248d82bf4c66486d142e226c1e06601a14f4d939a"],"threshold":1},"timestamp":{"keyids":["1689c5951cfc8a8cb4e3535c6ddc3f8d5c66e2effd4b7aae3506995f145da2a0","92933ea840e57ad3db67c748d1a309c4a7d8be3f70d8bbbd3cff9c4cca3bcf7b"],"threshold":1}},"consistent_snapshot":true},"signatures":[{"keyid":"70c4fb4ffe87b8d75559092c75bc038d587790bf2ecb9d8d6c6c0fae6705c750","sig":"52f8de5d8c0ac8c532a4e3c274b3e22cd2dca57a9f5d4094ccc1ded9966fb7064acc589ad564ba7ba04f7dfb42d8ccb803811b73551c60df4f9996c116967e00"}]}');
