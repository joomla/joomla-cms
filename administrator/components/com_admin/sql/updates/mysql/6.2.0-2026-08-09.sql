--
-- Add show_subcat_image param to com_newsfeeds
--

UPDATE `#__extensions`
SET `params` = JSON_SET(`params`, '$.show_subcat_image', '0')
WHERE `element` = 'com_newsfeeds'
  AND `type` = 'component'
  AND JSON_EXTRACT(`params`, '$.show_subcat_image') IS NULL;
