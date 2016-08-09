ALTER TABLE `#__nullDate_conversion` CHANGE `converted` `converted` datetime NOT NULL DEFAULT $T$;
UPDATE `#__nullDate_conversion` SET `converted` = $T$;

ALTER TABLE `#__banners` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__banners` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__banners` CHANGE `publish_up` `publish_up` datetime NOT NULL DEFAULT $T$;
UPDATE `#__banners` SET `publish_up` = $T$ WHERE `publish_up` = $O$;

ALTER TABLE `#__banners` CHANGE `publish_down` `publish_down` datetime NOT NULL DEFAULT $T$;
UPDATE `#__banners` SET `publish_down` = $T$ WHERE `publish_down` = $O$;

ALTER TABLE `#__banners` CHANGE `reset` `reset` datetime NOT NULL DEFAULT $T$;
UPDATE `#__banners` SET `reset` = $T$ WHERE `reset` = $O$;

ALTER TABLE `#__banners` CHANGE `created` `created` datetime NOT NULL DEFAULT $T$;
UPDATE `#__banners` SET `created` = $T$ WHERE `created` = $O$;

ALTER TABLE `#__banners` CHANGE `modified` `modified` datetime NOT NULL DEFAULT $T$;
UPDATE `#__banners` SET `modified` = $T$ WHERE `modified` = $O$;

ALTER TABLE `#__banner_clients` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__banner_clients` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__categories` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__categories` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__categories` CHANGE `created_time` `created_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__categories` SET `created_time` = $T$ WHERE `created_time` = $O$;

ALTER TABLE `#__categories` CHANGE `modified_time` `modified_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__categories` SET `modified_time` = $T$ WHERE `modified_time` = $O$;

ALTER TABLE `#__contact_details` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__contact_details` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__contact_details` CHANGE `created` `created` datetime NOT NULL DEFAULT $T$;
UPDATE `#__contact_details` SET `created` = $T$ WHERE `created` = $O$;

ALTER TABLE `#__contact_details` CHANGE `modified` `modified` datetime NOT NULL DEFAULT $T$;
UPDATE `#__contact_details` SET `modified` = $T$ WHERE `modified` = $O$;

ALTER TABLE `#__contact_details` CHANGE `publish_up` `publish_up` datetime NOT NULL DEFAULT $T$;
UPDATE `#__contact_details` SET `publish_up` = $T$ WHERE `publish_up` = $O$;

ALTER TABLE `#__contact_details` CHANGE `publish_down` `publish_down` datetime NOT NULL DEFAULT $T$;
UPDATE `#__contact_details` SET `publish_down` = $T$ WHERE `publish_down` = $O$;

ALTER TABLE `#__content` CHANGE `created` `created` datetime NOT NULL DEFAULT $T$; 
UPDATE `#__content` SET `created` = $T$ WHERE `created` = $O$;

ALTER TABLE `#__content` CHANGE `modified` `modified` datetime NOT NULL DEFAULT $T$;
UPDATE `#__content` SET `modified` = $T$ WHERE `modified` = $O$;
   
ALTER TABLE `#__content` CHANGE  `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$; 
UPDATE `#__content` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__content` CHANGE  `publish_up` `publish_up` datetime NOT NULL DEFAULT $T$; 
UPDATE `#__content` SET `publish_up` = $T$ WHERE `publish_up` = $O$;

ALTER TABLE `#__content` CHANGE `publish_down` `publish_down` datetime NOT NULL DEFAULT $T$;
UPDATE `#__content` SET `publish_down` = $T$ WHERE `publish_down` = $O$;

ALTER TABLE `#__menu` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__menu` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__extensions` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__extensions` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__finder_filters` CHANGE `created` `created` datetime NOT NULL DEFAULT $T$;
UPDATE `#__finder_filters` SET `created` = $T$ WHERE `created` = $O$;

ALTER TABLE `#__finder_filters` CHANGE `modified` `modified` datetime NOT NULL DEFAULT $T$;
UPDATE `#__finder_filters` SET `modified` = $T$ WHERE `modified` = $O$;

ALTER TABLE `#__finder_filters` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__finder_filters` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__finder_links` CHANGE `indexdate` `indexdate` datetime NOT NULL DEFAULT $T$;
UPDATE `#__finder_links` SET `indexdate` = $T$ WHERE `indexdate` = $O$;

ALTER TABLE `#__finder_links` CHANGE `publish_start_date` `publish_start_date` datetime NOT NULL DEFAULT $T$;
UPDATE `#__finder_links` SET `publish_start_date` = $T$ WHERE `publish_start_date` = $O$;

ALTER TABLE `#__finder_links` CHANGE `publish_end_date` `publish_end_date` datetime NOT NULL DEFAULT $T$;
UPDATE `#__finder_links` SET `publish_end_date` = $T$ WHERE `publish_end_date` = $O$;

ALTER TABLE `#__finder_links` CHANGE `start_date` `start_date` datetime NOT NULL DEFAULT $T$;
UPDATE `#__finder_links` SET `start_date` = $T$ WHERE `start_date` = $O$;

ALTER TABLE `#__finder_links` CHANGE `end_date` `end_date` datetime NOT NULL DEFAULT $T$;
UPDATE `#__finder_links` SET `end_date` = $T$ WHERE `end_date` = $O$;

ALTER TABLE `#__messages` CHANGE `date_time` `date_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__messages` SET `date_time` = $T$ WHERE `date_time` = $O$;

ALTER TABLE `#__modules` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__modules` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__modules` CHANGE `publish_up` `publish_up` datetime NOT NULL DEFAULT $T$; 
UPDATE `#__modules` SET `publish_up` = $T$ WHERE `publish_up` = $O$;

ALTER TABLE `#__modules` CHANGE `publish_down` `publish_down` datetime NOT NULL DEFAULT $T$;
UPDATE `#__modules` SET `publish_down` = $T$ WHERE `publish_down` = $O$;

ALTER TABLE `#__newsfeeds` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__newsfeeds` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__newsfeeds` CHANGE `created` `created` datetime NOT NULL DEFAULT $T$;
UPDATE `#__newsfeeds` SET `created` = $T$ WHERE `created` = $O$;

ALTER TABLE `#__newsfeeds` CHANGE `modified` `modified` datetime NOT NULL DEFAULT $T$;
UPDATE `#__newsfeeds` SET `modified` = $T$ WHERE `modified` = $O$;

ALTER TABLE `#__newsfeeds` CHANGE `publish_up` `publish_up` datetime NOT NULL DEFAULT $T$;
UPDATE `#__newsfeeds` SET `publish_up` = $T$ WHERE `publish_up` = $O$;

ALTER TABLE `#__newsfeeds` CHANGE `publish_down` `publish_down` datetime NOT NULL DEFAULT $T$;
UPDATE `#__newsfeeds` SET `publish_down` = $T$ WHERE `publish_down` = $O$;

ALTER TABLE `#__redirect_links` CHANGE `created_date` `created_date` datetime NOT NULL DEFAULT $T$;  
UPDATE `#__redirect_links` SET `created_date` = $T$ WHERE `created_date` = $O$;

ALTER TABLE `#__redirect_links` CHANGE `modified_date` `modified_date` datetime NOT NULL DEFAULT $T$;
UPDATE `#__redirect_links` SET `modified_date` = $T$ WHERE `modified_date` = $O$;

ALTER TABLE `#__tags` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__tags` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__tags` CHANGE `created_time` `created_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__tags` SET `created_time` = $T$ WHERE `created_time` = $O$;

ALTER TABLE `#__tags` CHANGE `modified_time` `modified_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__tags` SET `modified_time` = $T$ WHERE `modified_time` = $O$;

ALTER TABLE `#__tags` CHANGE `publish_up` `publish_up` datetime NOT NULL DEFAULT $T$;
UPDATE `#__tags` SET `publish_up` = $T$ WHERE `publish_up` = $O$;

ALTER TABLE `#__tags` CHANGE `publish_down` `publish_down` datetime NOT NULL DEFAULT $T$;
UPDATE `#__tags` SET `publish_down` = $T$ WHERE `publish_down` = $O$;

ALTER TABLE `#__ucm_content` CHANGE `core_created_time` `core_created_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__ucm_content` SET `core_created_time` = $T$ WHERE `core_created_time` = $O$;

ALTER TABLE `#__ucm_content` CHANGE `core_modified_time` `core_modified_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__ucm_content` SET `core_modified_time` = $T$ WHERE `core_modified_time` = $O$;

ALTER TABLE `#__ucm_history` CHANGE `save_date` `save_date` datetime NOT NULL DEFAULT $T$;
UPDATE `#__ucm_history` SET `save_date` = $T$ WHERE `save_date` = $O$;

ALTER TABLE `#__users` CHANGE `registerDate` `registerDate` datetime NOT NULL DEFAULT $T$;
UPDATE `#__users` SET `registerDate` = $T$ WHERE `registerDate` = $O$;

ALTER TABLE `#__users` CHANGE `lastvisitDate` `lastvisitDate` datetime NOT NULL DEFAULT $T$;
UPDATE `#__users` SET `lastvisitDate` = $T$ WHERE `lastvisitDate` = $O$;

ALTER TABLE `#__users` CHANGE `lastResetTime` `lastResetTime` datetime NOT NULL DEFAULT $T$;
UPDATE `#__users` SET `lastResetTime` = $T$ WHERE `lastResetTime` = $O$;

ALTER TABLE `#__user_notes` CHANGE `checked_out_time` `checked_out_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__user_notes` SET `checked_out_time` = $T$ WHERE `checked_out_time` = $O$;

ALTER TABLE `#__user_notes` CHANGE `created_time` `created_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__user_notes` SET `created_time` = $T$ WHERE `created_time` = $O$;

ALTER TABLE `#__user_notes` CHANGE `modified_time` `modified_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__user_notes` SET `modified_time` = $T$ WHERE `modified_time` = $O$;

ALTER TABLE `#__user_notes` CHANGE `review_time` `review_time` datetime NOT NULL DEFAULT $T$;
UPDATE `#__user_notes` SET `review_time` = $T$ WHERE `review_time` = $O$;

ALTER TABLE `#__user_notes` CHANGE `publish_up` `publish_up` datetime NOT NULL DEFAULT $T$;
UPDATE `#__user_notes` SET `publish_up` = $T$ WHERE `publish_up` = $O$;

ALTER TABLE `#__user_notes` CHANGE `publish_down` `publish_down` datetime NOT NULL DEFAULT $T$;
UPDATE `#__user_notes` SET `publish_down` = $T$ WHERE `publish_down` = $O$;