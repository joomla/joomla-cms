UPDATE `#__modules`
SET `params` = REPLACE(`params`, 'docs.', 'guide.')
WHERE `params` LIKE '%docs.%';
