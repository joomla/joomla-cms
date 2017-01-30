
-- Create forms table for com_fields
CREATE TABLE IF NOT EXISTS `#__fields_forms`
(
  id INT(10) unsigned NOT NULL AUTO_INCREMENT,
  asset_id INT(10) DEFAULT '0' NOT NULL,
  context VARCHAR(255) CHAR SET utf8 DEFAULT '' NOT NULL,
  title VARCHAR(255) DEFAULT '' NOT NULL,
  note VARCHAR(255) DEFAULT '' NOT NULL,
  description TEXT NOT NULL,
  is_subform TINYINT(1) DEFAULT '0' NOT NULL,
  state TINYINT(1) DEFAULT '0' NOT NULL,
  checked_out INT(11) DEFAULT '0' NOT NULL,
  checked_out_time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
  ordering INT(11) DEFAULT '0' NOT NULL,
  language CHAR(7) DEFAULT '' NOT NULL,
  created DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
  created_by INT(10) unsigned DEFAULT '0' NOT NULL,
  modified DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
  modified_by INT(10) unsigned DEFAULT '0' NOT NULL,
  access INT(11) DEFAULT '1' NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_checkout` (`checked_out`),
  KEY `idx_context` (`context`),
  KEY `idx_created_by` (`created_by`),
  KEY `idx_language` (`language`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

-- Create forms-categories table for com_fields
CREATE TABLE IF NOT EXISTS `#__fields_forms_categories`
(
  form_id INT(11) DEFAULT '0' NOT NULL,
  category_id INT(11) DEFAULT '0' NOT NULL,
  CONSTRAINT `PRIMARY` PRIMARY KEY (form_id, category_id)
);


-- Add columns to various com_fields related tables in order to support the forms

ALTER TABLE `#__fields` ADD COLUMN `form_id` int(10) UNSIGNED DEFAULT '0'  AFTER `context`;
ALTER TABLE `#__fields` ADD COLUMN `group_id` int(10) UNSIGNED DEFAULT '0'  AFTER `form_id`;

ALTER TABLE `#__fields_groups` ADD COLUMN `form_id` int(10) UNSIGNED DEFAULT '0'  AFTER `context`;

ALTER TABLE `#__fields_values` ADD COLUMN `form_id` int(10) UNSIGNED DEFAULT '0'  AFTER `context`;
ALTER TABLE `#__fields_values` ADD COLUMN `index` int(10) UNSIGNED DEFAULT NULL AFTER `form_id`;

-- Insert sub-form plugin

INSERT INTO `#__extensions` (`extension_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
(478, 'plg_fields_subform', 'plugin', 'subform', 'fields', 0, 1, 1, 0, '', '', '', '', 0, '0000-00-00 00:00:00', 0, 0);
