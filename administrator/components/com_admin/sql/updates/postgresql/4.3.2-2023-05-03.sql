UPDATE "#__extensions"
   SET "params" = '{"template_positions_display":"0","upload_limit":"10","image_formats":"gif,bmp,jpg,jpeg,png,webp","source_formats":"txt,less,ini,xml,js,php,css,scss,sass,json","font_formats":"woff,woff2,ttf,otf","compressed_formats":"zip","difference":"SideBySide"}'
 WHERE "name" = 'com_templates' AND "params" IN ('{,"difference":"SideBySide"}', '{}', '');
