-- Update content types for lookup tags

UPDATE "#__content_types"
SET "content_history_options" = jsonb_set(
	"content_history_options"::jsonb,
	'{displayLookup}',
	"content_history_options"::jsonb->'displayLookup' ||
		jsonb_build_object(
		'sourceColumn', 'tags',
		'targetTable', '#__tags',
		'targetColumn', 'id',
		'displayColumn', 'title'
		)
	)
WHERE "type_alias" IN (
		 'com_content.article',
		 'com_contact.contact',
		 'com_newsfeeds.newsfeed',
		 'com_content.category',
		 'com_contact.category',
		 'com_newsfeeds.category',
		 'com_banners.category',
		 'com_users.category'
	)
AND NOT EXISTS (
	SELECT * FROM jsonb_array_elements("content_history_options"::jsonb->'displayLookup')
	WHERE value = jsonb_build_object(
		'sourceColumn', 'tags',
		'targetTable', '#__tags',
		'targetColumn', 'id',
		'displayColumn', 'title'
		)
	);
