--
-- Fix wrong asset name for com_content basic workflow stage if a new asset with the right
-- name hasn't been created yet when saving the workflow stage in backend in past.
--
UPDATE `#__assets`
   SET `name` = 'com_content.stage.1'
 WHERE `name` = 'com_content.state.1'
   AND (SELECT c.`count` FROM (SELECT COUNT(b.`id`) AS `count` FROM `#__assets` b WHERE b.`name` = 'com_content.stage.1') AS c) = 0;

--
-- Fix wrong asset titles for workflow transitions
--
UPDATE `#__assets` SET `title` = 'Unpublish' WHERE `name` = 'com_content.transition.1' AND `title` = 'Publish';
UPDATE `#__assets` SET `title` = 'Publish'   WHERE `name` = 'com_content.transition.2' AND `title` = 'Unpublish';
UPDATE `#__assets` SET `title` = 'Trash'     WHERE `name` = 'com_content.transition.3' AND `title` = 'Archive';
UPDATE `#__assets` SET `title` = 'Archive'   WHERE `name` = 'com_content.transition.4' AND `title` = 'Trash';

--
-- Set asset ID of com_content basic workflow stage to the right value if not already set.
-- The right value is either the asset fixed with the first update statement at the top
-- of this file or a new asset which has been created yet when saving the workflow stage
-- in backend in past.
--
UPDATE `#__workflow_stages` s
 INNER JOIN (SELECT `name`, MAX(`id`) AS `id` FROM `#__assets` GROUP BY `name`) AS a ON a.`name` = CONCAT('com_content.stage.', s.`id`)
   SET s.`asset_id` = a.`id`
 WHERE s.`id` = 1
   AND s.`asset_id` = 0;
