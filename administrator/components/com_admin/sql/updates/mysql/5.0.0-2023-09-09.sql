-- Add com_fields to action logs
INSERT INTO `#__action_logs_extensions` (`extension`) VALUES ('com_fields');

INSERT INTO `#__action_log_config` (`type_title`, `type_alias`, `id_holder`, `title_holder`, `table_name`, `text_prefix`) VALUES
('field', 'com_fields.field', 'id', 'title', '#__fields', 'PLG_ACTIONLOG_JOOMLA');
