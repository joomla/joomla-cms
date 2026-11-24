UPDATE "#__assets"
SET "rules" = jsonb_set(
	"rules"::jsonb,
	'{core.api}',
	"rules"::jsonb -> 'core.manage',
	true
)::text
WHERE (NULLIF("rules", '')::jsonb -> 'core.manage') IS NOT NULL
	AND (NULLIF("rules", '')::jsonb -> 'core.api') IS NULL;
