UPDATE "#__extensions" 
SET "params" = REPLACE("params", '"com_categories",', '"com_categories","com_checkin",')
WHERE "name" = 'com_actionlogs';

SET IDENTITY_INSERT #__extensions  ON;

INSERT INTO "#__action_logs_extensions" ("extension") VALUES
('com_checkin');

SET IDENTITY_INSERT #__extensions  OFF;