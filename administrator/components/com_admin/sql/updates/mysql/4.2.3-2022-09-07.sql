-- Remove the record of any template overrides where the template has already been uninstalled
DELETE FROM `#__template_overrides` WHERE `template` NOT IN (SELECT `name` FROM `#__extensions` WHERE `type`='template');
