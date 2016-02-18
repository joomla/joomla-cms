ALTER TABLE `#__categories` ADD COLUMN `path_published` TINYINT NOT NULL DEFAULT 0 AFTER `level`;

UPDATE `#__categories` SET `path_published` = 1 WHERE id = 1;

UPDATE `#__categories` c INNER JOIN (
SELECT c2.id, CASE WHEN MIN(p.published) > 0 THEN MAX(p.published) ELSE MIN(p.published) END AS path_published
FROM `#__categories` c2
INNER JOIN `#__categories` p ON c2.lft >= p.lft AND c2.rgt <= p.rgt
GROUP BY c2.id) n
ON c.id = n.id
SET c.path_published = n.path_published;
