-- Remove dummy entries for #__ucm_content rows in the #__assets table
DELETE FROM "#__assets" WHERE "name" LIKE '#__ucm_content.%';
