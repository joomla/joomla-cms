--
-- Add show_subcat_image param to com_content
--

UPDATE `#_extensions`
SET `params` = JSON_SET(`params`, '$.show_subcat_image', '0')
WHERE `element` = 'com_content'
  AND `type` = 'component'
  AND JSON_EXTRACT(`params`, '$.show_subcat_image') IS NULL;
