-- Update the com_templates options to add the diff options
UPDATE `#__extensions` SET `params` = REPLACE(`params`, '}', ',"difference":"SideBySide"}') WHERE `name` = 'com_templates';
