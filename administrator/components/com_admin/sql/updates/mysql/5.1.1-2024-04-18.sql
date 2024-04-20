--
-- Add a default value for the colorScheme in the Atum template on Joomla update
-- only when a value is not already set.
-- New installs will have the default value set in the installation sql.
--

UPDATE `#__template_styles`
SET `params` = JSON_SET(`params`, '$.colorScheme', 'os')
WHERE `template` = 'atum'
AND JSON_EXTRACT(`params`, '$.colorScheme') IS NULL;
