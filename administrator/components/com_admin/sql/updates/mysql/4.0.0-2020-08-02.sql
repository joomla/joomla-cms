ALTER TABLE `#__content` ADD INDEX `idx_catid_ordering` (`catid`, `ordering`);

--
-- Add index for ordering on #__content_frontpage table
--
ALTER TABLE `#__content_frontpage` ADD INDEX `idx_ordering` (`ordering`);

-- Reverse ordering in #__content table
UPDATE `#__content` AS n
INNER JOIN (
	SELECT (
		SELECT @rownum := IF(@group = catid OR ((@group := catid) AND 0), @rownum + 1, 1)
		FROM (SELECT @rownum := 0, @group := '') AS r) AS new_ordering, id, catid, ordering
	FROM `#__content`
	ORDER BY catid DESC, ordering DESC) n2 ON n2.id = n.id
SET n.ordering = n2.new_ordering;

-- Reverse ordering in #__content_frontpage table
UPDATE `#__content_frontpage` AS n
INNER JOIN (
	SELECT (SELECT @rownum := @rownum + 1 FROM (SELECT @rownum := 0) AS r) AS new_ordering, content_id, ordering
	FROM `#__content_frontpage`
	ORDER BY ordering DESC
) n2 ON n2.content_id = n.content_id
SET n.ordering = n2.new_ordering;
