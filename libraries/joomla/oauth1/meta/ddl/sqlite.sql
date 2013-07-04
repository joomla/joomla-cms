# @package     Joomla.Platform
# @subpackage  OAuth1
# @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE

CREATE TABLE IF NOT EXISTS `#__oauth_nonce` (
	`nonce_id` INTEGER PRIMARY KEY AUTOINCREMENT,
	`consumer_key` TEXT NOT NULL,
	`timestamp` INTEGER NOT NULL DEFAULT 0,
	`nonce` TEXT NOT NULL DEFAULT ''
);

CREATE TABLE IF NOT EXISTS `#__oauth_clients` (
	`client_id` INTEGER PRIMARY KEY AUTOINCREMENT,
	`key` TEXT NOT NULL DEFAULT '',
	`alias` TEXT NOT NULL DEFAULT '',
	`secret` TEXT NOT NULL DEFAULT '',
	`title` TEXT NOT NULL DEFAULT '',
	CONSTRAINT `idx_oauth_clients_key` UNIQUE (`key`)
);

CREATE TABLE `#__oauth_credentials` (
	`credentials_id` INTEGER PRIMARY KEY AUTOINCREMENT,
	`key` TEXT NOT NULL  DEFAULT '',
	`secret` TEXT NOT NULL DEFAULT '',
	`client_key` TEXT NOT NULL DEFAULT '',
	`type` TEXT NOT NULL DEFAULT '',
	`callback_url` TEXT NOT NULL DEFAULT '',
	`verifier_key` TEXT NOT NULL DEFAULT '',
	`resource_owner_id` INTEGER NOT NULL DEFAULT 0,
	`expiration_date` INTEGER NOT NULL DEFAULT 0,
	`temporary_expiration_date` INTEGER DEFAULT 0
);
