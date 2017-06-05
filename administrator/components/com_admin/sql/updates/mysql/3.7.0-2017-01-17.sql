-- Sync menutype for admin menu and set client_id correct

-- Note: This file had to be modified with Joomla 3.7.3 because the
-- original version made site menus disappear if there were menu types
-- "main" or "menu" defined for the site.

-- Step 1: If there is any user-defined menu and menu type "main" for the site
-- (client_id = 0), then change the menu type for the menu, any module and the
-- menu type to something hopefully not being used yet.
UPDATE `#__menu`
   SET `menutype` = 'main_is_reserved'
 WHERE `client_id` = 0
   AND `menutype` = 'main'
   AND (SELECT COUNT(`id`) FROM `#__menu_types` WHERE `client_id` = 0 AND `menutype` = 'main') > 0;

UPDATE `#__modules`
   SET `params` = REPLACE(`params`,'"menutype":"main"','"menutype":"main_is_reserved"')
 WHERE `client_id` = 0
   AND (SELECT COUNT(`id`) FROM `#__menu_types` WHERE `client_id` = 0 AND `menutype` = 'main') > 0;

UPDATE `#__menu_types`
   SET `menutype` = 'main_is_reserved'
 WHERE `client_id` = 0 
   AND `menutype` = 'main';

-- Step 2: What remains now are the main menu items, possibly with wrong
-- client_id if there was nothing hit by step 1 because there was no record in
-- the menu types table with client_id = 0.
-- If there is a record in the menutype table we con't care because either
-- it has client_id 1, then it is ok, or it does not exist (which is the normal
-- case for the standard main menu).
UPDATE `#__menu`
   SET `client_id` = 1
 WHERE `menutype` = 'main';

-- Step 3: Synch menu type "menu" correct client_id if there is no user-defined
-- menu type "menu" for either admin or site.
UPDATE `#__menu`
   SET `menutype` = 'main',
       `client_id` = 1
 WHERE `menutype` = 'menu'
   AND (SELECT COUNT(`id`) FROM `#__menu_types` WHERE `menutype` = 'menu') = 0;
