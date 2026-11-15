-- Update content types for lookup tags

UPDATE `#__content_types`
SET `content_history_options` = JSON_ARRAY_APPEND(
	`content_history_options`,
	'$.displayLookup',
	JSON_OBJECT(
		'sourceColumn', 'tags',
		'targetTable', '#__tags',
		'targetColumn', 'id',
		'displayColumn', 'title'
		)
	)
WHERE `type_alias` IN (
		 'com_content.article',
		 'com_contact.contact',
		 'com_newsfeeds.newsfeed',
		 'com_content.category',
		 'com_contact.category',
		 'com_newsfeeds.category',
		 'com_banners.category',
		 'com_users.category'
	)
AND NOT JSON_CONTAINS(
	JSON_EXTRACT(`content_history_options`, '$.displayLookup'),
	JSON_OBJECT(
		'sourceColumn', 'tags',
		'targetTable', '#__tags',
		'targetColumn', 'id',
		'displayColumn', 'title'
		)
	);
