-- Set core extensions as locked extensions.
UPDATE `#__extensions`
SET `locked` = 1
WHERE  (`type` = 'plugin' AND
    (
        (`folder` = 'system' AND `element` = 'schedulerunner')
        OR (`folder` = 'task' AND `element` IN ('checkfiles', 'demotasks', 'requests', 'sitestatus'))
    )
);
