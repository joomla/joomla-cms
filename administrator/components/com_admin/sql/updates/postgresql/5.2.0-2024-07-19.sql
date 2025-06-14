--
-- Add the Guided Tours selectable option to the User Action Logs
--
INSERT INTO "#__action_logs_extensions" ("extension") VALUES ('com_guidedtours');

INSERT INTO "#__action_log_config" ("type_title", "type_alias", "id_holder", "title_holder", "table_name", "text_prefix") VALUES
('guidedtour', 'com_guidedtours.state', 'id', 'title', '#__guidedtours', 'PLG_ACTIONLOG_JOOMLA');
