CREATE TABLE IF NOT EXISTS `#__workflow_automation_rules` (
    `id` int NOT NULL AUTO_INCREMENT,
    `transition_id` int NOT NULL COMMENT 'Foreign Key to #__workflow_transitions.id',
    `published` tinyint NOT NULL DEFAULT 0,
    `ordering` int NOT NULL DEFAULT 0,
    `rule_type` varchar(20) NOT NULL DEFAULT 'delay' COMMENT 'delay or cron',
	`delay_value` int DEFAULT NULL,
	`delay_unit` varchar(10) DEFAULT NULL COMMENT 'minutes, hours, days, months',
    `cron_expression` varchar(100) DEFAULT NULL,
	`item_filter` text COMMENT 'JSON filter tree: which items this rule applies to (evaluated at selection)',
	`fire_condition` text COMMENT 'JSON expression tree: gate evaluated live at fire time',
    `run_as_user_id` int NOT NULL DEFAULT 0 COMMENT 'User identity used to execute the transition',
    `created` datetime NOT NULL,
    `created_by` int NOT NULL DEFAULT 0,
    `modified` datetime NOT NULL,
    `modified_by` int NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
	UNIQUE KEY `idx_transition` (`transition_id`),
	KEY `idx_published` (`published`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__workflow_item_state` (
    `id` int NOT NULL AUTO_INCREMENT,
    `item_id` int NOT NULL DEFAULT 0 COMMENT 'Extension table id value',
    `extension` varchar(50) NOT NULL,
    `stage_id` int NOT NULL COMMENT 'Foreign Key to #__workflow_stages.id',
    `entered_at` datetime NOT NULL COMMENT 'When the item arrived in stage_id',
	`triggered_by` varchar(20) NOT NULL DEFAULT 'manual' COMMENT 'Determine if a transition was triggered manually or by the automation',
	`requires_intervention` tinyint NOT NULL DEFAULT 0 COMMENT 'Set when an automated transition failed; excluded from the scheduler until an admin clears it',
	`last_checked_at` datetime DEFAULT NULL COMMENT 'When the scheduler last considered this item; null means never',
	`last_failure_at` datetime DEFAULT NULL COMMENT 'When this item last could not be evaluated; null means it evaluated cleanly',
	`last_failure_reason` varchar(500) DEFAULT NULL COMMENT 'Why it could not be evaluated, so the same failure is only reported once',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_item_extension` (`item_id`, `extension`),
    KEY `idx_stage_entered` (`stage_id`, `entered_at`),
	KEY `idx_requires_intervention` (`requires_intervention`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__workflow_automation_log` (
    `id` int NOT NULL AUTO_INCREMENT,
    `rule_id` int DEFAULT NULL COMMENT 'Foreign Key to #__workflow_automation_rules.id',
    `item_id` int NOT NULL DEFAULT 0,
    `extension` varchar(50) NOT NULL,
    `transition_id` int NOT NULL,
    `from_stage_id` int NOT NULL DEFAULT 0,
    `to_stage_id` int NOT NULL DEFAULT 0,
    `run_as_user_id` int NOT NULL DEFAULT 0,
    `trigger_type` varchar(20) NOT NULL DEFAULT 'rule',
    `exit_code` tinyint NOT NULL DEFAULT 0 COMMENT '0 ok, 1 permission denied, 2 invalid transition, 3 exception, 4 no longer applicable',
    `note` varchar(500) DEFAULT NULL,
    `executed_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_item_id` (`item_id`),
    KEY `idx_executed_at` (`executed_at`),
    KEY `idx_exit_code` (`exit_code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci;

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_task_workflowtransition', 'plugin', 'workflowtransition', 'task', 0, 1, 1, 0, 1, '', '{}', '', 10, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'workflowtransition' AND e.`folder` = 'task' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_workflow_automation', 'plugin', 'automation', 'workflow', 0, 1, 1, 0, 1, '', '{}', '', 4, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'automation' AND e.`folder` = 'workflow' AND e.`client_id` = 0);

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`)
SELECT 0, 'plg_workflow_remotecheck', 'plugin', 'remotecheck', 'workflow', 0, 0, 1, 0, 1, '', '{}', '', 5, 0
WHERE NOT EXISTS (SELECT * FROM `#__extensions` e WHERE e.`type` = 'plugin' AND e.`element` = 'remotecheck' AND e.`folder` = 'workflow' AND e.`client_id` = 0);

-- entered_at is stamped with the upgrade time rather than a guess. Nothing records when these
-- items actually entered their stage, and using a date like `modified` would leave anything
-- edited long ago instantly overdue, transitioning a whole back catalogue on the first run.
INSERT INTO `#__workflow_item_state` (`item_id`, `extension`, `stage_id`, `entered_at`, `triggered_by`, `requires_intervention`)
SELECT a.`item_id`, a.`extension`, a.`stage_id`, UTC_TIMESTAMP(), 'manual', 0
FROM `#__workflow_associations` a
LEFT JOIN `#__workflow_item_state` s ON s.`item_id` = a.`item_id` AND s.`extension` = a.`extension`
WHERE s.`id` IS NULL;
