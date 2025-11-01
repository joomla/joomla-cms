UPDATE `#__extensions`
SET `params` = JSON_SET(`params`, '$.removed_asset', '1')
WHERE `type` = 'plugin' AND `element` = 'compat' AND `folder` = 'behaviour'
AND JSON_EXTRACT(`params`, '$.removed_asset') IS NULL;
