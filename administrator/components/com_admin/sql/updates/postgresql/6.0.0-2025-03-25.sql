--
-- Add a template tag for ip_address on Joomla update
-- only when a value is not already set.
-- New installs will have the default value set in the installation support sql.
--
UPDATE #__mail_templates
SET params = (
    params::jsonb || jsonb_build_object(
        'tags',
        (params::jsonb -> 'tags') || '"ip_address"'::jsonb
    )
)::text
WHERE template_id = 'com_actionlogs.notification'
  AND NOT EXISTS (
      SELECT 1
      FROM jsonb_array_elements_text(params::jsonb -> 'tags') AS tag
      WHERE tag = 'ip_address'
  );
