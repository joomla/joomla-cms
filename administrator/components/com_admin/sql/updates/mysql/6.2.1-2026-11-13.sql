UPDATE `#__assets`
SET `rules` = JSON_SET(
	`rules`,
	'$."core.api"',
	JSON_EXTRACT(`rules`, '$."core.manage"')
)
WHERE JSON_VALID(`rules`)
	AND JSON_CONTAINS_PATH(`rules`, 'one', '$."core.manage"') = 1
	AND JSON_CONTAINS_PATH(`rules`, 'one', '$."core.api"') = 0;
