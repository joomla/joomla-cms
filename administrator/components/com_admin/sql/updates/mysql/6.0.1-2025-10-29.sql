UPDATE `#__menu`
SET `params` = JSON_SET(`params`,
    JSON_UNQUOTE(JSON_SEARCH(`params`, 'one', ' ', NULL, '$.featured_categories[*]')),
    ''
    )
WHERE JSON_VALID(`params`) = 1
    AND JSON_SEARCH(`params`, 'one', ' ', NULL, '$.featured_categories[*]') IS NOT NULL
    AND `type` = 'component'
    AND `link` = 'index.php?option=com_content&view=featured';

UPDATE `#__menu`
SET `link` = REPLACE(`link`, '&catid[0]= ', '&catid[0]=')
WHERE `type` = 'component'
    AND `link` LIKE 'index.php?option=com_content&view=archive&catid[0]= %';
