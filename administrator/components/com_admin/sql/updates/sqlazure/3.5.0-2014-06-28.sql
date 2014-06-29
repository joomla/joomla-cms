SET IDENTITY_INSERT [#__content_types]  ON;

INSERT INTO [#__content_types] ([type_id], [type_title], [type_alias])
SELECT 16, 'Image', 'com_media.image'
UNION ALL
SELECT 17, 'Media Category', 'com_media.category';

SET IDENTITY_INSERT #__content_types  OFF;
