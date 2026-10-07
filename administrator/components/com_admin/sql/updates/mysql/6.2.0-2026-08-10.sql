--
-- Add show_subcat_image param to com_contact
--

UPDATE `#__extensions`
SET `params` = JSON_SET(`params`, '$.show_subcat_image', '0')
WHERE `element` = 'comn_contact'
  AND `type` = 'component'
  AND JSON_EXTRACT(`params`, '$.show_subcat_image') IS NULL;
