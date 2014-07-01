SET IDENTITY_INSERT [#__content_types]  ON;

INSERT INTO [#__content_types] ([type_id], [type_title], [type_alias])
SELECT 16, 'Image', 'com_media.image'
UNION ALL
SELECT 17, 'Media Category', 'com_media.category';

SET IDENTITY_INSERT #__content_types  OFF;

SET IDENTITY_INSERT [#__categories]  ON;

INSERT INTO [#__categories] ([id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [published], [access], [params], [metadata], [created_user_id], [hits], [language], [version])
SELECT 8, 33, 1, 13, 14, 1, 'uncategorised', 'com_media', 'Uncategorised', 'uncategorised', 1, 1, '{"category_layout":"","image":""}', '{"author":"","robots":""}', 42, 0, '*', 1;

SET IDENTITY_INSERT #__categories  OFF;


