ALTER TABLE `#__user_mfa` ADD COLUMN `tries` int NOT NULL DEFAULT 0 /** CAN FAIL **/;
ALTER TABLE `#__user_mfa` ADD COLUMN `last_try` datetime /** CAN FAIL **/;
