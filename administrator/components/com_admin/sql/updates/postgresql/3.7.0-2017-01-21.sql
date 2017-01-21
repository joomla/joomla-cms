-- Drop fields-categories table for com_fields
DROP TABLE `#__fields_categories`;

-- Create forms table for com_fields
CREATE TABLE `#__fields_forms`
(
  id INT(10) unsigned PRIMARY KEY NOT NULL AUTO_INCREMENT,
  asset_id INT(10) DEFAULT '0' NOT NULL,
  context VARCHAR(255) DEFAULT '' NOT NULL,
  title VARCHAR(255) DEFAULT '' NOT NULL,
  note VARCHAR(255) DEFAULT '' NOT NULL,
  description TEXT NOT NULL,
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
  excluded_except_as_subform TINYINT(1) DEFAULT '0' NOT NULL
);
CREATE INDEX idx_access ON `#__fields_forms` (access);
CREATE INDEX idx_checkout ON `#__fields_forms` (checked_out);
CREATE INDEX idx_context ON `#__fields_forms` (context);
CREATE INDEX idx_created_by ON `#__fields_forms` (created_by);
CREATE INDEX idx_language ON `#__fields_forms` (language);
CREATE INDEX idx_state ON `#__fields_forms` (state);

-- Create forms-categories table for com_fields
CREATE TABLE `#__fields_forms_categories`
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
ALTER TABLE `#__fields_values` ADD COLUMN `array_id` int(10) UNSIGNED DEFAULT '0'  AFTER `form_id`;
