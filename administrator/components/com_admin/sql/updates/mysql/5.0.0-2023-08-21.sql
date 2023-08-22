UPDATE `#__extensions`
   SET `params` = JSON_REPLACE(`params`, '$.filter' , JSON_UNQUOTE('\\\\Joomla\\\\CMS\\\\Component\\\\ComponentHelper::filterText'))
 WHERE `type` = 'plugin'
   AND `folder` = 'fields'
   AND `element` IN ('editor', 'text', 'textarea')
   AND `params` <> ''
   AND JSON_EXTRACT(`params`, '$.filter') = 'JComponentHelper::filterText';

UPDATE `#__fields`
   SET `fieldparams` = JSON_REPLACE(`fieldparams`, '$.filter' , JSON_UNQUOTE('\\\\Joomla\\\\CMS\\\\Component\\\\ComponentHelper::filterText'))
 WHERE `type` IN ('editor', 'text', 'textarea')
   AND `fieldparams` <> ''
   AND JSON_EXTRACT(`fieldparams`, '$.filter') = 'JComponentHelper::filterText';
