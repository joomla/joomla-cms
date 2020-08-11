ALTER TABLE `#__content` ADD INDEX `idx_catid_ordering` (`catid`, `ordering`);

--
-- Add index for ordering on #__content_frontpage table
--
ALTER TABLE `#__content_frontpage` ADD INDEX `idx_ordering` (`ordering`);

-- Reverse ordering in #__content table
SET @new_ordering := 0;
SET @category := '';
UPDATE `#__content` AS n
INNER JOIN (
  SELECT 
    @new_ordering:=CASE
        WHEN @category:= catid 
			THEN @new_ordering + 1
        ELSE 1
    END AS new_ordering,
    @category:=catid catid,
    id,
    ordering
  FROM
    `#__content`
  ORDER BY catid DESC, ordering DESC
) AS n2
SET n.ordering = n2.new_ordering
WHERE n.id = n2.id;

-- Reverse ordering in #__content_frontpage table
SET @new_ordering := 0; 
UPDATE `#__content_frontpage` AS n
INNER JOIN (
  SELECT 
    (@row_number:=@row_number + 1) AS new_ordering, 
    content_id, 
    ordering
   FROM
    `#__content_frontpage`
   ORDER BY ordering DESC
) n2 ON n2.content_id = n.content_id
SET n.ordering = n2.new_ordering;
