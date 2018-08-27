--
-- Add index for ordering in category on #__content table
--
CREATE INDEX "#__content_idx_catid_ordering" ON "#__content" ("catid", "ordering");

--
-- Add index for ordering on #__content_frontpage table
--
CREATE INDEX "#__content_frontpage_idx_ordering" ON "#__content_frontpage" ("ordering");

-- Reverse ordering in #__content table
UPDATE "#__content" n
SET ordering = n2.new_ordering
FROM (
	SELECT id, ROW_NUMBER() OVER (PARTITION BY catid ORDER BY ordering DESC) AS new_ordering
	FROM "#__content"
) n2
WHERE n.id = n2.id;

-- Reverse ordering in #__content_frontpage table
UPDATE "#__content_frontpage" n
SET ordering = n2.new_ordering
FROM (
	SELECT content_id, ROW_NUMBER() OVER (ORDER BY ordering DESC) AS new_ordering
	FROM "#__content_frontpage"
) n2
WHERE n.content_id = n2.content_id;
