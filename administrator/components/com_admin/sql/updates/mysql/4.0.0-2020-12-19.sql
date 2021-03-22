UPDATE `#__banners` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__banners` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__categories` SET `created_time` = '1980-01-01 00:00:00' WHERE `created_time` = '0000-00-00 00:00:00';
UPDATE `#__categories` SET `modified_time` = `created_time` WHERE `modified_time` = '0000-00-00 00:00:00';

UPDATE `#__contact_details` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__contact_details` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__content` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__content` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__newsfeeds` SET `created` = '1980-01-01 00:00:00' WHERE `created` = '0000-00-00 00:00:00';
UPDATE `#__newsfeeds` SET `modified` = `created` WHERE `modified` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_created_time` = '1980-01-01 00:00:00'
 WHERE `core_type_alias`
    IN ('com_banners.banner'
       ,'com_banners.category'
       ,'com_contact.category'
       ,'com_contact.contact'
       ,'com_content.article'
       ,'com_content.category'
       ,'com_newsfeeds.category'
       ,'com_newsfeeds.newsfeed'
       ,'com_users.category')
   AND `core_created_time` = '0000-00-00 00:00:00';

UPDATE `#__ucm_content` SET `core_modified_time` = `core_created_time`
 WHERE `core_type_alias`
    IN ('com_banners.banner'
       ,'com_banners.category'
       ,'com_contact.category'
       ,'com_contact.contact'
       ,'com_content.article'
       ,'com_content.category'
       ,'com_newsfeeds.category'
       ,'com_newsfeeds.newsfeed'
       ,'com_users.category')
   AND `core_modified_time` = '0000-00-00 00:00:00';

UPDATE `#__redirect_links` SET `created_date` = '1980-01-01 00:00:00' WHERE `created_date` = '0000-00-00 00:00:00';
UPDATE `#__redirect_links` SET `modified_date` = `created_date` WHERE `modified_date` = '0000-00-00 00:00:00';

UPDATE `#__users` SET `registerDate` = '1980-01-01 00:00:00' WHERE `registerDate` = '0000-00-00 00:00:00';
