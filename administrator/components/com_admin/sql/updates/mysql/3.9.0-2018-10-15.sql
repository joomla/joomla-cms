ALTER TABLE `#__action_logs` ADD INDEX `idx_user_id` (`user_id`);
ALTER TABLE `#__action_logs` ADD INDEX `idx_user_id_logdate` (`user_id`, `log_date`);
ALTER TABLE `#__action_logs` ADD INDEX `idx_user_id_extension` (`user_id`, `extension`);
ALTER TABLE `#__action_logs` ADD INDEX `idx_extension_item_id` (`extension`, `item_id`);
