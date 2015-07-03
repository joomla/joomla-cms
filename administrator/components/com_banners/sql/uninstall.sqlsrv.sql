DELETE FROM [#__content_types] WHERE [type_alias] IN ('com_banners.banner', 'com_banners.category', 'com_banners.client');

DROP TABLE IF EXISTS `#__banners`;

DROP TABLE IF EXISTS `#__banner_clients`;

DROP TABLE IF EXISTS `#__banner_tracks`;

