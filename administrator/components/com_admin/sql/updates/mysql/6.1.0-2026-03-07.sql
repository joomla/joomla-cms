UPDATE `#__extensions`
   SET `params` = JSON_REPLACE(`params`, '$.html_height' , '550px')
 WHERE `type` = 'plugin'
   AND `folder` = 'editors'
   AND `element` = 'tinymce'
   AND `params` <> ''
   AND JSON_EXTRACT(`params`, '$.html_height') = '550';

UPDATE `#__extensions`
   SET `params` = JSON_REPLACE(`params`, '$.html_width' , '100%')
 WHERE `type` = 'plugin'
   AND `folder` = 'editors'
   AND `element` = 'tinymce'
   AND `params` <> ''
   AND JSON_EXTRACT(`params`, '$.html_width') = '750';
