UPDATE "#__extensions" 
SET "params" = REPLACE("params", '"com_categories",', '"com_categories","com_checkin",')
WHERE "name" = 'com_actionlogs';

INSERT INTO "#__action_logs_extensions" ("extension") VALUES
('com_checkin');

SELECT setval('#__action_logs_extensions_id_seq', max(id)) FROM "#__action_logs_extensions";