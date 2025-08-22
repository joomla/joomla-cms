UPDATE `#__scheduler_task`
SET `params` = JSON_REMOVE(`params`, '$.email')
WHERE JSON_CONTAINS_PATH(`params`, 'one', '$.email');
