INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`) VALUES
    (0, 'plg_editors-xtd_guidedtour', 'plugin', 'compat', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 0, 0);

--
-- Table structure for table `#__guidedtour_user_steps`
--

CREATE TABLE IF NOT EXISTS `#__guidedtour_user_steps` (
    `tour_id` int NOT NULL DEFAULT 0,
    `step_id` int NOT NULL DEFAULT 0,
    `user_id` int NOT NULL DEFAULT 0,
    `viewed` datetime NOT NULL,
    KEY `idx_tour` (`tour_id`),
    KEY `idx_step` (`step_id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_viewed` (`viewed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__guidedtours` ADD COLUMN `params` text NOT NULL /** CAN FAIL **/;

INSERT INTO `#__content_types` (`type_id`, `type_title`, `type_alias`, `table`, `rules`, `field_mappings`, `router`, `content_history_options`)
VALUES
    (
        14,
        'Guided Tour',
        'com_guidedtours.tour',
        '{"special":{"dbtable":"#__guidedtours","key":"id","type":"TourTable","prefix":"Joomla\\\\Component\\\\Guidedtours\\\\Administrator\\\\Table\\\\","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"Joomla\\\\Component\\\\Guidedtours\\\\Administrator\\\\Table\\\\","config":"array()"}}',
        '',
        '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description","core_access":"access", "core_params":"params", "core_language":"language", "core_ordering":"ordering", "note":"note"}}',
        '',
        '{"formFile":"administrator\\/components\\/com_guidedtours\\/forms\\/tour.xml", "hideFields":["params","language"], "ignoreChanges":[], "convertToInt":[], "displayLookup":[]}'
    );
