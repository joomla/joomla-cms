-- after 4.0.0 RC6
UPDATE `#__assets`
   SET `name` = 'com_content.stage.1'
 WHERE `name` = 'com_content.state.1';

UPDATE `#__workflow_stages`
   SET `asset_id` = 57
 WHERE `asset_id` = 0;
