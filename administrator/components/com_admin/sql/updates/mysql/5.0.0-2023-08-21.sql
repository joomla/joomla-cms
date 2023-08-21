UPDATE `#__extensions`
   SET `params` = JSON_REPLACE(`params`, '$.filter' , '\\Joomla\\CMS\\Component\\ComponentHelper::filterText')
 WHERE `type` = 'plugin'
   AND `folder` = 'fields'
   AND `element` IN ('editor', 'text', 'textarea')
   AND `params` <> ''
   AND JSON_EXTRACT(`params`, '$.filter') = 'JComponentHelper::filterText';
