UPDATE `#__extensions` SET `locked` = 1
 WHERE `type` = 'plugin' AND `folder` = 'schemaorg'
   AND `element` IN ('article', 'blogposting', 'book', 'event', 'jobposting', 'organization', 'person', 'recipe', 'custom')
   AND `locked` <> 1;

UPDATE `#__extensions` SET `locked` = 1
 WHERE `type` = 'plugin' AND `element` = 'schemaorg' AND `folder` = 'system'
   AND `locked` <> 1;

UPDATE `#__extensions` SET `locked` = 1
 WHERE `type` = 'plugin' AND `element` = 'globalcheckin' AND `folder` = 'task'
   AND `locked` <> 1;
