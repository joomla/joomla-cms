ALTER TABLE `#__privacy_requests` MODIFY `requested_at` datetime NOT NULL;
ALTER TABLE `#__privacy_requests` MODIFY `confirm_token_created_at` datetime NULL DEFAULT NULL;

ALTER TABLE `#__privacy_consents` MODIFY `created` datetime NOT NULL;

UPDATE `#__privacy_requests` SET `confirm_token_created_at` = NULL WHERE `confirm_token_created_at` = '0000-00-00 00:00:00';
