ALTER TABLE `#__privacy_requests` MODIFY `requested_at` datetime NOT NULL;
ALTER TABLE `#__privacy_requests` MODIFY `confirm_token_created_at` datetime NOT NULL;

ALTER TABLE `#__privacy_consents` MODIFY `created` datetime NOT NULL;
