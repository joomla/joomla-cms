CREATE TABLE IF NOT EXISTS `#__hikashop_address` (
	`address_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`address_user_id` int(10) unsigned NOT NULL DEFAULT '0',
	`address_title` varchar(255) DEFAULT NULL,
	`address_firstname` varchar(255) DEFAULT NULL,
	`address_middle_name` varchar(255) DEFAULT NULL,
	`address_lastname` varchar(255) DEFAULT NULL,
	`address_company` varchar(255) DEFAULT NULL,
	`address_street` varchar(255) DEFAULT NULL,
	`address_street2` varchar(255) NOT NULL DEFAULT '',
	`address_post_code` varchar(255) DEFAULT NULL,
	`address_city` varchar(255) DEFAULT NULL,
	`address_telephone` varchar(255) DEFAULT NULL,
	`address_telephone2` varchar(255) DEFAULT NULL,
	`address_fax` varchar(255) DEFAULT NULL,
	`address_state` varchar(255) DEFAULT NULL,
	`address_country` varchar(255) DEFAULT NULL,
	`address_published` tinyint(4) NOT NULL DEFAULT '1',
	`address_vat` varchar(255) DEFAULT NULL,
	`address_default` tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`address_id`),
	KEY `address_user_id` (`address_user_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_badge` (
	`badge_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`badge_name` varchar(255) NOT NULL DEFAULT '',
	`badge_image` varchar(255) NOT NULL DEFAULT '',
	`badge_start` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_end` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_product_id` varchar(255) NOT NULL DEFAULT '',
	`badge_category_id` varchar(255) NOT NULL DEFAULT '',
	`badge_category_childs` tinyint(4) NOT NULL DEFAULT '0',
	`badge_discount_id` varchar(255) NOT NULL DEFAULT '',
	`badge_ordering` int(10) unsigned NOT NULL DEFAULT '0',
	`badge_size` float(12,2) unsigned NOT NULL,
	`badge_position` varchar(255) NOT NULL DEFAULT 'bottomleft',
	`badge_vertical_distance` int(10) NOT NULL DEFAULT '0',
	`badge_horizontal_distance` int(10) NOT NULL DEFAULT '0',
	`badge_margin` int(10) NOT NULL DEFAULT '0',
	`badge_published` tinyint(4) NOT NULL DEFAULT '0',
	`badge_keep_size` tinyint(4) NOT NULL DEFAULT '0',
	`badge_quantity` varchar(255) NOT NULL DEFAULT '',
	`badge_url` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`badge_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_banner` (
	`banner_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`banner_title` varchar(255) NOT NULL DEFAULT '',
	`banner_url` varchar(255) NOT NULL DEFAULT '',
	`banner_image_url` varchar(255) NOT NULL DEFAULT '',
	`banner_published` tinyint(4) NOT NULL DEFAULT '0',
	`banner_ordering` int(11) NOT NULL DEFAULT '0',
	`banner_comment` text NOT NULL,
	PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_cart` (
	`cart_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`user_id` int(10) unsigned NOT NULL DEFAULT '0',
	`session_id` varchar(255) NOT NULL,
	`cart_modified` int(10) unsigned NOT NULL DEFAULT '0',
	`cart_coupon` varchar(255) NOT NULL DEFAULT '',
	`cart_type` varchar(25) NOT NULL DEFAULT 'cart',
	`cart_current` int(11) NOT NULL DEFAULT '0',
	`cart_share` varchar(255) NOT NULL DEFAULT 'nobody',
	`cart_name` varchar(50) NOT NULL DEFAULT '',
	`cart_params` text NOT NULL DEFAULT '',
	PRIMARY KEY (`cart_id`),
	KEY `user_id` (`user_id`),
	KEY `session_id` (`session_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_cart_product` (
	`cart_product_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`cart_id` int(10) unsigned NOT NULL DEFAULT '0',
	`product_id` int(10) unsigned NOT NULL DEFAULT '0',
	`cart_product_quantity` int(10) unsigned NOT NULL DEFAULT '1',
	`cart_product_parent_id` int(10) unsigned NOT NULL DEFAULT '0',
	`cart_product_modified` int(10) unsigned NOT NULL DEFAULT '0',
	`cart_product_option_parent_id` int(10) unsigned DEFAULT '0',
	`cart_product_wishlist_id` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`cart_product_id`),
	KEY `cart_id` (`cart_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_category` (
	`category_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`category_parent_id` int(10) unsigned NOT NULL DEFAULT '0',
	`category_type` varchar(255) NOT NULL,
	`category_name` varchar(255) NOT NULL,
	`category_description` text NOT NULL,
	`category_published` tinyint(4) NOT NULL DEFAULT '0',
	`category_ordering` int(10) unsigned NOT NULL DEFAULT '0',
	`category_left` int(10) unsigned NOT NULL DEFAULT '0',
	`category_right` int(10) unsigned NOT NULL DEFAULT '0',
	`category_depth` int(10) unsigned NOT NULL DEFAULT '0',
	`category_namekey` varchar(255) NOT NULL,
	`category_created` int(10) unsigned NOT NULL DEFAULT '0',
	`category_modified` int(10) unsigned NOT NULL DEFAULT '0',
	`category_access` varchar(255) NOT NULL DEFAULT 'all',
	`category_menu` int(10) unsigned DEFAULT '0',
	`category_keywords` text NOT NULL,
	`category_meta_description` varchar(255) NOT NULL DEFAULT '',
	`category_layout` varchar(255) NOT NULL DEFAULT '',
	`category_page_title` varchar(255) NOT NULL DEFAULT '',
	`category_alias` varchar(255) NOT NULL DEFAULT '',
	`category_site_id` varchar(255) NULL DEFAULT '',
	`category_canonical` VARCHAR( 255 ) NOT NULL DEFAULT '',
	`category_quantity_layout` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`category_id`),
	UNIQUE KEY `category_namekey` (`category_namekey`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_characteristic` (
	`characteristic_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`characteristic_parent_id` int(10) NOT NULL DEFAULT '0',
	`characteristic_value` varchar(255) NOT NULL DEFAULT '0',
	`characteristic_alias` varchar(255) NOT NULL DEFAULT '',
	`characteristic_display_type` varchar(255) NOT NULL DEFAULT '',
	`characteristic_params` TEXT NOT NULL DEFAULT '',
	`characteristic_ordering` int(12) unsigned NOT NULL DEFAULT '0',
	`characteristic_display_method` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`characteristic_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_click` (
	`click_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`click_ip` varchar(255) NOT NULL DEFAULT '',
	`click_created` int(10) unsigned NOT NULL DEFAULT '0',
	`click_partner_id` int(10) unsigned NOT NULL DEFAULT '0',
	`click_partner_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`click_referer` varchar(255) NOT NULL DEFAULT '',
	`click_partner_paid` tinyint(4) NOT NULL DEFAULT '0',
	`click_partner_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`click_id`),
	KEY `click_partner_id` (`click_partner_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_config` (
	`config_namekey` varchar(200) NOT NULL,
	`config_value` text NOT NULL,
	`config_default` text NOT NULL,
	PRIMARY KEY (`config_namekey`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_currency` (
	`currency_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`currency_symbol` varchar(255) NOT NULL,
	`currency_code` varchar(255) NOT NULL,
	`currency_format` char(10) NOT NULL DEFAULT '%i',
	`currency_name` varchar(255) NOT NULL,
	`currency_published` tinyint(4) NOT NULL DEFAULT '0',
	`currency_rate` decimal(16,5) NOT NULL DEFAULT '1.00000',
	`currency_locale` text NOT NULL,
	`currency_displayed` tinyint(4) NOT NULL DEFAULT '0',
	`currency_percent_fee` decimal(4,2) NOT NULL DEFAULT '0.00',
	`currency_modified` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`currency_id`),
	UNIQUE KEY `currency_code` (`currency_code`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_discount` (
	`discount_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`discount_type` varchar(255) NOT NULL DEFAULT 'discount',
	`discount_start` int(10) unsigned NOT NULL DEFAULT '0',
	`discount_end` int(10) unsigned NOT NULL DEFAULT '0',
	`discount_flat_amount` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`discount_percent_amount` decimal(12,3) NOT NULL DEFAULT '0.000',
	`discount_minimum_order` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`discount_quota` int(10) unsigned NOT NULL DEFAULT '0',
	`discount_used_times` int(10) unsigned NOT NULL DEFAULT '0',
	`discount_code` varchar(255) NOT NULL DEFAULT '',
	`discount_published` tinyint(4) NOT NULL DEFAULT '0',
	`discount_product_id` varchar(255) NOT NULL DEFAULT '',
	`discount_category_id` varchar(255) NOT NULL DEFAULT '',
	`discount_zone_id` varchar(255) NOT NULL DEFAULT '',
	`discount_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
	`discount_category_childs` tinyint(4) NOT NULL DEFAULT '0',
	`discount_auto_load` tinyint(3) unsigned DEFAULT '0',
	`discount_access` varchar(255) NOT NULL DEFAULT 'all',
	`discount_tax_id` int(10) unsigned DEFAULT '0',
	`discount_minimum_products` int(10) unsigned DEFAULT '0',
	`discount_quota_per_user` int(10) unsigned DEFAULT '0',
	`discount_coupon_nodoubling` tinyint(4) DEFAULT NULL,
	`discount_coupon_product_only` tinyint(4) DEFAULT NULL,
	`discount_affiliate` int(10) NOT NULL DEFAULT '0',
	`discount_site_id` VARCHAR( 255 ) NULL DEFAULT '',
	PRIMARY KEY (`discount_id`),
	UNIQUE KEY `discount_code` (`discount_code`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_download` (
	`file_id` int(10) unsigned NOT NULL,
	`order_id` int(10) unsigned NOT NULL,
	`file_pos` int(10) NOT NULL DEFAULT '1',
	`download_number` int(11) NOT NULL DEFAULT '1',
	PRIMARY KEY (`file_id`,`order_id`,`file_pos`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_email_log` (
	`email_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`email_log_sender_email` varchar(255) NOT NULL DEFAULT '',
	`email_log_sender_name` varchar(255) NOT NULL DEFAULT '',
	`email_log_recipient_email` varchar(255) NOT NULL DEFAULT '',
	`email_log_recipient_name` varchar(255) NOT NULL DEFAULT '',
	`email_log_reply_email` varchar(255) NOT NULL DEFAULT '',
	`email_log_reply_name` varchar(255) NOT NULL DEFAULT '',
	`email_log_cc_email` varchar(255) NOT NULL DEFAULT '',
	`email_log_bcc_email` varchar(255) NOT NULL DEFAULT '',
	`email_log_subject` text NOT NULL,
	`email_log_altbody` text NOT NULL,
	`email_log_body` text NOT NULL,
	`email_log_name` varchar(255) NOT NULL DEFAULT '',
	`email_log_ref_id` varchar(255) NOT NULL DEFAULT '',
	`email_log_params` text NOT NULL,
	`email_log_date` int(10) NOT NULL ,
	`email_log_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
	PRIMARY KEY (`email_log_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_entry` (
	`entry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`order_id` int(10) unsigned NOT NULL,
	PRIMARY KEY (`entry_id`),
	KEY `order_id` (`order_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_field` (
	`field_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`field_table` varchar(50) DEFAULT NULL,
	`field_realname` varchar(250) NOT NULL,
	`field_namekey` varchar(50) NOT NULL,
	`field_type` varchar(50) DEFAULT NULL,
	`field_value` longtext NOT NULL,
	`field_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`field_ordering` smallint(5) unsigned DEFAULT '99',
	`field_options` text,
	`field_core` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`field_required` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`field_default` varchar(250) DEFAULT NULL,
	`field_access` varchar(255) NOT NULL DEFAULT 'all',
	`field_categories` text NOT NULL DEFAULT '',
	`field_with_sub_categories` tinyint(1) NOT NULL DEFAULT '0',
	`field_products` text NOT NULL DEFAULT '',
	`field_frontcomp` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`field_backend` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`field_backend_listing` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`field_display` text NOT NULL DEFAULT '',
	PRIMARY KEY (`field_id`),
	UNIQUE KEY `field_namekey` (`field_namekey`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_file` (
	`file_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`file_name` varchar(255) NOT NULL,
	`file_description` text NOT NULL,
	`file_path` varchar(255) NOT NULL,
	`file_type` varchar(255) NOT NULL DEFAULT 'category',
	`file_ref_id` int(10) unsigned NOT NULL DEFAULT '0',
	`file_free_download` tinyint(3) unsigned NOT NULL DEFAULT '0',
	`file_ordering` int(10) unsigned NOT NULL DEFAULT '0',
	`file_limit` int(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`file_id`),
	KEY `file_type` (`file_type`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_filter` (
	`filter_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
	`filter_name` varchar(250) NOT NULL,
	`filter_namekey` varchar(50) NOT NULL,
	`filter_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
	`filter_type` varchar(50) DEFAULT NULL,
	`filter_category_id` VARCHAR(255) NOT NULL,
	`filter_ordering` smallint(5) unsigned DEFAULT '99',
	`filter_options` text,
	`filter_data` text NOT NULL,
	`filter_access` varchar(250) NOT NULL DEFAULT 'all',
	`filter_direct_application` tinyint(3) NOT NULL DEFAULT '0',
	`filter_value` text NOT NULL,
	`filter_category_childs` tinyint(3) unsigned NOT NULL,
	`filter_height` int(50) unsigned NOT NULL,
	`filter_deletable` tinyint(3) unsigned NOT NULL,
	`filter_dynamic` tinyint(3) unsigned NOT NULL,
	PRIMARY KEY (`filter_id`),
	UNIQUE KEY `filter_namekey` (`filter_namekey`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_geolocation` (
	`geolocation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`geolocation_ip` varchar(255) NOT NULL DEFAULT '',
	`geolocation_type` varchar(255) NOT NULL DEFAULT 'order',
	`geolocation_ref_id` int(10) unsigned NOT NULL DEFAULT '0',
	`geolocation_created` int(10) unsigned NOT NULL DEFAULT '0',
	`geolocation_latitude` decimal(9,6) NOT NULL DEFAULT '0.000000',
	`geolocation_longitude` decimal(9,6) NOT NULL DEFAULT '0.000000',
	`geolocation_postal_code` varchar(255) NOT NULL DEFAULT '',
	`geolocation_country` varchar(255) NOT NULL DEFAULT '',
	`geolocation_country_code` varchar(255) NOT NULL DEFAULT '',
	`geolocation_state` varchar(255) NOT NULL DEFAULT '',
	`geolocation_state_code` varchar(255) NOT NULL DEFAULT '',
	`geolocation_city` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`geolocation_id`),
	KEY `geolocation_type` (`geolocation_type`,`geolocation_ref_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_history` (
	`history_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`history_order_id` int(10) unsigned NOT NULL DEFAULT '0',
	`history_created` int(10) unsigned NOT NULL DEFAULT '0',
	`history_ip` varchar(255) NOT NULL DEFAULT '',
	`history_new_status` varchar(255) NOT NULL DEFAULT '',
	`history_reason` text NOT NULL,
	`history_notified` tinyint(4) NOT NULL DEFAULT '0',
	`history_amount` varchar(255) NOT NULL DEFAULT '',
	`history_package_id` int(10) unsigned NOT NULL DEFAULT '0',
	`history_payment_id` varchar(255) NOT NULL DEFAULT '',
	`history_payment_method` varchar(255) NOT NULL DEFAULT '',
	`history_data` longtext NOT NULL,
	`history_type` varchar(255) NOT NULL DEFAULT '',
	`history_user_id` int(10) unsigned DEFAULT '0',
	PRIMARY KEY (`history_id`),
	KEY `history_order_id` (`history_order_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_limit` (
	`limit_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`limit_product_id` int(11) NOT NULL DEFAULT '0',
	`limit_category_id` int(11) NOT NULL DEFAULT '0',
	`limit_per_product` tinyint(4) NOT NULL DEFAULT '0',
	`limit_periodicity` varchar(255) NOT NULL DEFAULT '',
	`limit_type` varchar(255) NOT NULL DEFAULT '',
	`limit_value` int(10) NOT NULL DEFAULT '0',
	`limit_unit` varchar(255) DEFAULT NULL,
	`limit_currency_id` int(11) NOT NULL DEFAULT '0',
	`limit_access` varchar(255) NOT NULL DEFAULT '',
	`limit_status` varchar(255) NOT NULL DEFAULT '',
	`limit_published` tinyint(4) NOT NULL DEFAULT '0',
	`limit_created` int(10) DEFAULT NULL,
	`limit_modified` int(10) DEFAULT NULL,
	`limit_start` int(10) DEFAULT NULL,
	`limit_end` int(10) DEFAULT NULL,
	PRIMARY KEY (`limit_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_massaction` (
	`massaction_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`massaction_name` varchar(255) NOT NULL DEFAULT '',
	`massaction_description` text NOT NULL,
	`massaction_table` varchar(255) NOT NULL DEFAULT 'product',
	`massaction_published` tinyint(4) NOT NULL DEFAULT '1',
	`massaction_lasttime` int(10) unsigned NOT NULL DEFAULT '0',
	`massaction_triggers` text NOT NULL,
	`massaction_filters` text NOT NULL,
	`massaction_actions` text NOT NULL,
	`massaction_report` text NOT NULL,
	PRIMARY KEY (`massaction_id`),
	KEY `massaction_table` (`massaction_table`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_order` (
	`order_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`order_billing_address_id` int(10) unsigned NOT NULL DEFAULT '0',
	`order_shipping_address_id` int(10) unsigned NOT NULL DEFAULT '0',
	`order_user_id` int(10) unsigned NOT NULL DEFAULT '0',
	`order_status` varchar(255) NOT NULL DEFAULT '',
	`order_type` varchar(255) NOT NULL DEFAULT 'sale',
	`order_number` varchar(255) NOT NULL DEFAULT '',
	`order_created` int(10) unsigned NOT NULL DEFAULT '0',
	`order_modified` int(10) unsigned NOT NULL DEFAULT '0',
	`order_invoice_id` int(10) unsigned NOT NULL DEFAULT '0',
	`order_invoice_number` varchar(255) NOT NULL DEFAULT '',
	`order_invoice_created` int(10) unsigned NOT NULL DEFAULT '0',
	`order_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
	`order_currency_info` text NOT NULL DEFAULT '',
	`order_full_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_tax_info` text NOT NULL DEFAULT '',
	`order_discount_code` varchar(255) NOT NULL DEFAULT '',
	`order_discount_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_discount_tax` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_payment_id` varchar(255) NOT NULL DEFAULT '',
	`order_payment_method` varchar(255) NOT NULL DEFAULT '',
	`order_payment_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_payment_tax` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_payment_params` text NOT NULL DEFAULT '',
	`order_shipping_id` varchar(255) NOT NULL DEFAULT '',
	`order_shipping_method` varchar(255) NOT NULL DEFAULT '',
	`order_shipping_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_shipping_tax` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_shipping_params` text NOT NULL DEFAULT '',
	`order_partner_id` int(10) unsigned NOT NULL DEFAULT '0',
	`order_partner_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_partner_paid` int(11) NOT NULL DEFAULT '0',
	`order_partner_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
	`order_ip` varchar(255) NOT NULL DEFAULT '',
	`order_site_id` varchar(255) NULL DEFAULT '',
	PRIMARY KEY (`order_id`),
	KEY `order_user_id` (`order_user_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_order_product` (
	`order_product_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`order_id` int(10) unsigned NOT NULL DEFAULT '0',
	`product_id` int(10) unsigned NOT NULL DEFAULT '0',
	`order_product_quantity` int(10) unsigned NOT NULL DEFAULT '1',
	`order_product_name` varchar(255) NOT NULL DEFAULT '',
	`order_product_code` varchar(255) NOT NULL DEFAULT '',
	`order_product_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_product_tax` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_product_tax_info` text NOT NULL,
	`order_product_options` text NOT NULL,
	`order_product_option_parent_id` int(10) unsigned DEFAULT '0',
	`order_product_wishlist_id` int(11) NOT NULL DEFAULT '0',
	`order_product_shipping_id` varchar(255) NOT NULL DEFAULT '',
	`order_product_shipping_method` varchar(255) NOT NULL DEFAULT '',
	`order_product_shipping_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_product_shipping_tax` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`order_product_shipping_params` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`order_product_id`),
	KEY `order_id` (`order_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_payment` (
	`payment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`payment_name` varchar(255) NOT NULL DEFAULT '',
	`payment_description` text NOT NULL,
	`payment_images` text NOT NULL,
	`payment_params` text NOT NULL,
	`payment_type` varchar(255) NOT NULL DEFAULT '',
	`payment_zone_namekey` varchar(255) NOT NULL DEFAULT '',
	`payment_ordering` int(10) unsigned NOT NULL DEFAULT '0',
	`payment_published` tinyint(4) NOT NULL DEFAULT '1',
	`payment_access` varchar(255) NOT NULL DEFAULT 'all',
	`payment_shipping_methods` text NOT NULL,
	`payment_currency` varchar(255) NOT NULL,
	`payment_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	PRIMARY KEY (`payment_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_price` (
	`price_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`price_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
	`price_product_id` int(10) unsigned NOT NULL DEFAULT '0',
	`price_value` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`price_min_quantity` int(10) unsigned NOT NULL DEFAULT '0',
	`price_access` varchar(255) NOT NULL DEFAULT 'all',
	`price_site_id` varchar(255) NULL DEFAULT '',
	PRIMARY KEY (`price_id`),
	KEY `price_product_id` (`price_product_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_product` (
	`product_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`product_parent_id` int(11) unsigned NOT NULL DEFAULT '0',
	`product_name` varchar(255) NOT NULL,
	`product_description` text NOT NULL,
	`product_quantity` int(11) NOT NULL DEFAULT '-1',
	`product_code` varchar(255) NOT NULL,
	`product_published` tinyint(4) NOT NULL DEFAULT '0',
	`product_hit` int(11) unsigned NOT NULL DEFAULT '0',
	`product_created` int(11) unsigned NOT NULL DEFAULT '0',
	`product_sale_start` int(10) unsigned NOT NULL DEFAULT '0',
	`product_sale_end` int(10) unsigned NOT NULL DEFAULT '0',
	`product_delay_id` int(10) unsigned NOT NULL DEFAULT '0',
	`product_tax_id` int(10) unsigned NOT NULL DEFAULT '0',
	`product_type` varchar(255) NOT NULL DEFAULT '',
	`product_vendor_id` int(10) unsigned NOT NULL DEFAULT '0',
	`product_manufacturer_id` int(10) unsigned NOT NULL DEFAULT '0',
	`product_url` varchar(255) NOT NULL,
	`product_weight` decimal(12,3) unsigned NOT NULL DEFAULT '0.000',
	`product_keywords` text NOT NULL,
	`product_weight_unit` varchar(255) NOT NULL DEFAULT 'kg',
	`product_modified` int(10) unsigned NOT NULL DEFAULT '0',
	`product_meta_description` varchar(255) NOT NULL DEFAULT '',
	`product_dimension_unit` varchar(255) NOT NULL DEFAULT 'm',
	`product_width` decimal(12,3) NOT NULL DEFAULT '0.000',
	`product_length` decimal(12,3) NOT NULL DEFAULT '0.000',
	`product_height` decimal(12,3) NOT NULL DEFAULT '0.000',
	`product_max_per_order` int(10) unsigned DEFAULT '0',
	`product_access` varchar(255) NOT NULL DEFAULT 'all',
	`product_group_after_purchase` varchar(255) NOT NULL DEFAULT '',
	`product_min_per_order` int(10) unsigned DEFAULT '0',
	`product_contact` smallint(5) unsigned NOT NULL DEFAULT '0',
	`product_display_quantity_field` smallint(5) NOT NULL DEFAULT '0',
	`product_last_seen_date` int(10) unsigned DEFAULT '0',
	`product_sales` int(10) unsigned DEFAULT '0',
	`product_waitlist` smallint(5) unsigned NOT NULL DEFAULT '0',
	`product_layout` varchar(255) NOT NULL DEFAULT '',
	`product_average_score` float NOT NULL,
	`product_total_vote` int(11) NOT NULL DEFAULT '0',
	`product_page_title` varchar(255) NOT NULL DEFAULT '',
	`product_alias` varchar(255) NOT NULL DEFAULT '',
	`product_price_percentage`decimal(15,7) NOT NULL DEFAULT '0',
	`product_msrp` decimal(15,7) NULL DEFAULT '0',
	`product_canonical` VARCHAR( 255 ) NOT NULL DEFAULT '',
	`product_warehouse_id` int(10) unsigned NOT NULL DEFAULT '0',
	`product_quantity_layout` varchar(255) NOT NULL DEFAULT '',
	PRIMARY KEY (`product_id`),
	UNIQUE KEY `product_code` (`product_code`),
	KEY `product_parent_id` (`product_parent_id`),
	KEY `product_main_index` (`product_type`,`product_quantity`,`product_published`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_product_category` (
	`product_category_id` int(255) unsigned NOT NULL AUTO_INCREMENT,
	`category_id` int(10) unsigned NOT NULL,
	`product_id` int(10) unsigned NOT NULL,
	`ordering` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`product_category_id`),
	UNIQUE KEY `category_id` (`category_id`,`product_id`),
	KEY `product_id` (`product_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_product_related` (
	`product_id` int(10) unsigned NOT NULL,
	`product_related_id` int(10) unsigned NOT NULL,
	`product_related_type` varchar(255) NOT NULL DEFAULT 'related',
	`product_related_ordering` int(10) unsigned DEFAULT '0',
	PRIMARY KEY (`product_id`,`product_related_id`,`product_related_type`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_plugin` (
	`plugin_id` INT(10) NOT NULL AUTO_INCREMENT,
	`plugin_type` VARCHAR(255) NOT NULL,
	`plugin_published` INT(4) NOT NULL DEFAULT 0,
	`plugin_name` VARCHAR(255) NOT NULL,
	`plugin_ordering` INT(10) NOT NULL DEFAULT 0,
	`plugin_description` TEXT NOT NULL DEFAULT '',
	`plugin_params` TEXT NOT NULL DEFAULT '',
	`plugin_access` VARCHAR(255) NOT NULL DEFAULT 'all',
	PRIMARY KEY (`plugin_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_shipping` (
	`shipping_id` int(11) NOT NULL AUTO_INCREMENT,
	`shipping_type` varchar(255) NOT NULL DEFAULT 'manual',
	`shipping_zone_namekey` varchar(255) NOT NULL,
	`shipping_tax_id` int(10) unsigned NOT NULL DEFAULT '0',
	`shipping_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`shipping_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
	`shipping_name` varchar(255) NOT NULL,
	`shipping_description` text NOT NULL,
	`shipping_published` tinyint(4) NOT NULL DEFAULT '1',
	`shipping_ordering` int(10) unsigned NOT NULL DEFAULT '0',
	`shipping_currency` varchar(255) NOT NULL,
	`shipping_params` text NOT NULL,
	`shipping_images` varchar(255) NOT NULL DEFAULT '',
	`shipping_access` varchar(255) NOT NULL DEFAULT 'all',
	PRIMARY KEY (`shipping_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_shipping_price` (
	`shipping_price_id` int(11) NOT NULL AUTO_INCREMENT,
	`shipping_id` int(11) NOT NULL,
	`shipping_price_ref_id` int(11) NOT NULL,
	`shipping_price_ref_type` varchar(255) NOT NULL DEFAULT 'product',
	`shipping_price_min_quantity` int(11) NOT NULL DEFAULT '0',
	`shipping_price_value` decimal(15,7) NOT NULL DEFAULT '0.0000000',
	`shipping_fee_value` decimal(15,7) NOT NULL DEFAULT '0.0000000',
	PRIMARY KEY (`shipping_price_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_tax` (
	`tax_namekey` varchar(255) NOT NULL,
	`tax_rate` decimal(17,5) NOT NULL DEFAULT '0.00000',
	PRIMARY KEY (`tax_namekey`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_taxation` (
	`taxation_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`zone_namekey` varchar(255) NOT NULL,
	`category_namekey` varchar(255) NOT NULL,
	`tax_namekey` varchar(255) NOT NULL,
	`taxation_published` tinyint(4) NOT NULL DEFAULT '0',
	`taxation_type` varchar(255) NOT NULL DEFAULT 'individual',
	`taxation_access` varchar(255) NOT NULL DEFAULT 'all',
	`taxation_cumulative` tinyint(4) DEFAULT NULL,
	`taxation_post_code` varchar(255) NOT NULL DEFAULT '',
	`taxation_date_start` int(10) unsigned NOT NULL DEFAULT '0',
	`taxation_date_end` int(10) unsigned NOT NULL DEFAULT '0',
	`taxation_internal_code` varchar(15) NOT NULL DEFAULT '' COMMENT 'Accounting internal code',
	`taxation_note` text NOT NULL COMMENT 'Note to add in the invoice when this tax is used - It uses the language file for the translation',
	`taxation_site_id` varchar(255) NOT NULL DEFAULT '' COMMENT 'Reference to JMS site ID',
	`taxation_ordering` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`taxation_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_user` (
	`user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`user_cms_id` int(10) unsigned NOT NULL,
	`user_email` varchar(255) NOT NULL DEFAULT '',
	`user_partner_email` varchar(255) NOT NULL,
	`user_params` text NOT NULL,
	`user_partner_id` int(10) unsigned NOT NULL DEFAULT '0',
	`user_partner_price` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`user_partner_paid` tinyint(4) NOT NULL DEFAULT '0',
	`user_created_ip` varchar(255) NOT NULL DEFAULT '',
	`user_unpaid_amount` decimal(17,5) NOT NULL DEFAULT '0.00000',
	`user_partner_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
	`user_created` int(10) unsigned NOT NULL DEFAULT '0',
	`user_currency_id` int(10) unsigned NOT NULL DEFAULT '0',
	`user_partner_activated` tinyint(4) NOT NULL DEFAULT '0',
	PRIMARY KEY (`user_id`),
	UNIQUE KEY `user_email` (`user_email`),
	KEY `user_cms_id` (`user_cms_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_variant` (
	`variant_characteristic_id` int(10) unsigned NOT NULL,
	`variant_product_id` int(10) unsigned NOT NULL,
	`ordering` int(10) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`variant_characteristic_id`,`variant_product_id`),
	KEY `variant_product_id` (`variant_product_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_vote` (
	`vote_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`vote_ref_id` int(11) NOT NULL,
	`vote_type` varchar(15) NOT NULL,
	`vote_user_id` varchar(255) NOT NULL,
	`vote_rating` float NOT NULL,
	`vote_comment` text NOT NULL,
	`vote_useful` int(11) NOT NULL,
	`vote_pseudo` varchar(255) NOT NULL,
	`vote_ip` varchar(255) NOT NULL,
	`vote_email` varchar(255) NOT NULL,
	`vote_date` int(10) unsigned NOT NULL,
	`vote_published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`vote_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_vote_user` (
	`vote_user_id` int(11) NOT NULL,
	`vote_user_user_id` varchar(26) NOT NULL,
	`vote_user_useful` tinyint(4) NOT NULL
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_waitlist` (
	`waitlist_id` int(11) NOT NULL AUTO_INCREMENT,
	`product_id` int(11) NOT NULL,
	`date` int(11) NOT NULL,
	`email` varchar(255) NOT NULL,
	`name` varchar(255) DEFAULT NULL,
	`product_item_id` int(11) NOT NULL,
	PRIMARY KEY (`waitlist_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_widget` (
	`widget_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`widget_name` varchar(255) NOT NULL,
	`widget_params` text NOT NULL,
	`widget_published` tinyint(4) NOT NULL DEFAULT '0',
	`widget_ordering` int(11) NOT NULL DEFAULT '0',
	`widget_access` varchar(250) NOT NULL DEFAULT 'all',
	PRIMARY KEY (`widget_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_zone` (
	`zone_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`zone_namekey` varchar(255) NOT NULL,
	`zone_name` varchar(255) NOT NULL,
	`zone_name_english` varchar(255) NOT NULL,
	`zone_code_2` varchar(255) NOT NULL,
	`zone_code_3` varchar(255) NOT NULL,
	`zone_type` varchar(255) NOT NULL DEFAULT 'country',
	`zone_published` tinyint(4) NOT NULL DEFAULT '0',
	`zone_currency_id` int(10) unsigned DEFAULT '0',
	PRIMARY KEY (`zone_id`),
	UNIQUE KEY `zone_namekey` (`zone_namekey`),
	KEY `zone_code_3` (`zone_code_3`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_zone_link` (
	`zone_parent_namekey` varchar(255) NOT NULL,
	`zone_child_namekey` varchar(255) NOT NULL,
	PRIMARY KEY (`zone_parent_namekey`(150),`zone_child_namekey`(150))
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__hikashop_warehouse` (
	`warehouse_id` INT(10) NOT NULL AUTO_INCREMENT,
	`warehouse_name` VARCHAR(255) NOT NULL DEFAULT '',
	`warehouse_published` tinyint(4) NOT NULL DEFAULT '1',
	`warehouse_description` TEXT NOT NULL,
	`warehouse_ordering` INT(10) NOT NULL DEFAULT 0,
	`warehouse_created` int(10) DEFAULT NULL,
	`warehouse_modified` int(10) DEFAULT NULL,
	PRIMARY KEY (`warehouse_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;