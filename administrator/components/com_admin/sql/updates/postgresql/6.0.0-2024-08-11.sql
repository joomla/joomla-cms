-- Update link to featured

UPDATE "#__menu"
SET "link" = 'index.php?option=com_content&view=articles&filter[featured]=1'
WHERE "link" = 'index.php?option=com_content&view=featured'
AND "client_id" = 1;
