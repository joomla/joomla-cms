--
-- Add a template tag for ip_address on Joomla update
-- only when a value is not already set.
-- New installs will have the default value set in the installation support sql.
--

UPDATE `#_mail_templates`
SET `params` = JSON_SET(
    CAST(`params` AS JSON),
    CONCAT('$.tags[', JSON_LENGTH(JSON_EXTRACT(CAST(`params` AS JSON), '$.tags')), ']'),
    'ip_address'
)
WHERE `template_id` = 'com_actionlogs.notification'
  AND JSON_CONTAINS(
        JSON_EXTRACT(CAST(`params` AS JSON), '$.tags'),
        JSON_QUOTE('ip_address')
      ) = 0;
