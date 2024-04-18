--
-- Add a default value for the colorScheme in the Atum tenmplate on Joomla update
-- only when a value is not already set.
-- New installs will have the default value set in the installation sql.
--

UPDATE "#__template_styles"
SET "params" = jsonb_set("params", '{colorScheme}', '"os"', true)
WHERE "template" = 'atum'
AND NOT ("params" -> 'colorScheme' ? 'os');
