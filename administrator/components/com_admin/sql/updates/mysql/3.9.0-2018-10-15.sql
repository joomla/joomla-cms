ALTER TABLE `#__action_logs` ADD INDEX (`user_id`);
ALTER TABLE `#__action_logs` ADD INDEX (`user_id`, `log_date`);
ALTER TABLE `#__action_logs` ADD INDEX (`user_id`, `extension`);
ALTER TABLE `#__action_logs` ADD INDEX (`extension`, `item_id`);
