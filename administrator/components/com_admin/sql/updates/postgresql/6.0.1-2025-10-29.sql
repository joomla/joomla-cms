UPDATE "#__menu"
SET "params" = jsonb_set(
    "params"::jsonb,
    '{featured_categories}',
    COALESCE(
        (
            SELECT jsonb_agg(to_jsonb(CASE WHEN elem = ' ' THEN '' ELSE elem END))
            FROM jsonb_array_elements_text("params"::jsonb->'featured_categories') AS t(elem)
        ),
        '[]'::jsonb
    ),
    false
)
WHERE ("params"::jsonb->'featured_categories') @> '[" "]'::jsonb
    AND "type" = 'component'
    AND "link" = 'index.php?option=com_content&view=featured';

UPDATE "#__menu"
SET "link" = REPLACE("link", '&catid[0]= ', '&catid[0]=')
WHERE "type" = 'component'
    AND "link" LIKE 'index.php?option=com_content&view=archive&catid[0]= %';
