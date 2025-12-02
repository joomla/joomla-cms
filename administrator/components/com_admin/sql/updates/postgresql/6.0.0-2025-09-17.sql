INSERT INTO "#__guidedtours" ("title", "description", "extensions", "url", "published", "language", "note", "access", "uid", "autostart", "created", "created_by", "modified", "modified_by")
SELECT 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_0_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_0_DESCRIPTION', '["com_cpanel"]', 'administrator/index.php', 1, '*', '', 1, 'joomla-whatsnew-6-0', 1, CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0
WHERE NOT EXISTS (SELECT * FROM "#__guidedtours" g WHERE g."uid" = 'joomla-whatsnew-6-0');

INSERT INTO "#__guidedtour_steps" ("title", "description", "position", "target", "type", "interactive_type", "url", "published", "language", "note", "params", "created", "created_by", "modified", "modified_by", "tour_id")
SELECT 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_0_STEP_0_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_0_STEP_0_DESCRIPTION', 'center', '', 0, 1, '', 1, '*', '', '{"required":1,"requiredvalue":""}', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, MAX("id")
  FROM "#__guidedtours"
 WHERE "uid" = 'joomla-whatsnew-6-0';

INSERT INTO "#__guidedtour_steps" ("title", "description", "position", "target", "type", "interactive_type", "url", "published", "language", "note", "params", "created", "created_by", "modified", "modified_by", "tour_id")
SELECT 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_0_STEP_1_TITLE', 'COM_GUIDEDTOURS_TOUR_WHATSNEW_6_0_STEP_1_DESCRIPTION', 'right', '#sidebarmenu nav > ul:first-of-type > li:last-child', 0, 1, '', 1, '*', '', '"{\"required\":1,\"requiredvalue\":\"\"}"', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, MAX("id")
  FROM "#__guidedtours"
 WHERE "uid" = 'joomla-whatsnew-6-0';
