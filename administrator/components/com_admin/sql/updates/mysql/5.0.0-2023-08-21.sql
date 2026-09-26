--
-- Attention: In the below SQL statements, the value of the filter is unescaped, i.e. uses "\\", while
-- in base.sql the same value is using "\\\\". This is expected because of how JSON_REPLACE works.
--
UPDATE `#__extensions`
   SET `params` = JSON_REPLACE(`params`, '$.filter' , '\\Joomla\\CMS\\Component\\ComponentHelper::filterText')
 WHERE `type` = 'plugin'
   AND `folder` = 'fields'
   AND `element` IN ('editor', 'text', 'textarea')
   AND `params` <> ''
   AND JSON_EXTRACT(`params`, '$.filter') = 'JComponentHelper::filterText';

UPDATE `#__fields`
   SET `fieldparams` = JSON_REPLACE(`fieldparams`, '$.filter' , '\\Joomla\\CMS\\Component\\ComponentHelper::filterText')
 WHERE `type` IN ('editor', 'text', 'textarea')
   AND `fieldparams` <> ''
   AND JSON_EXTRACT(`fieldparams`, '$.filter') = 'JComponentHelper::filterText';
