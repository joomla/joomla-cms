-- uninstall previous what's new tours
DELETE FROM `#__guidedtour_steps`
 WHERE `tour_id` IN (SELECT `id` FROM `#__guidedtours` WHERE `uid` IN ('joomla-whatsnew-5-2', 'joomla-whatsnew-5-3', 'joomla-whatsnew-5-4'));

DELETE FROM `#__guidedtours`
 WHERE `uid` IN ('joomla-whatsnew-5-2', 'joomla-whatsnew-5-3', 'joomla-whatsnew-5-4');
