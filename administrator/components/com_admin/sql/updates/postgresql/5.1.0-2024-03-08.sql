INSERT INTO "#__guidedtour_steps" ("tour_id", "title", "published", "description", "ordering", "position", "target", "type", "interactive_type", "url", "created", "created_by", "modified", "modified_by", "language") VALUES
(0, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_MENUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_MENUS_DESCRIPTION', 1, 'right', '#sidebarmenu', 0, 1, '', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(0, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_QUICKACCESS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_QUICKACCESS_DESCRIPTION', 2, 'center', '', 0, 1, '', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(0, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_NOTIFICATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_NOTIFICATIONS_DESCRIPTION', 3, 'left', '.quickicons-for-update_quickicon .card', 0, 1, '', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(0, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_TOPBAR_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_TOPBAR_DESCRIPTION', 4, 'bottom', '#header', 0, 1, '', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(0, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_FINALWORDS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_FINALWORDS_DESCRIPTION', 5, 'right', '#sidebarmenu nav > ul:first-of-type > li:last-child', 0, 1, '', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*')
ON CONFLICT DO NOTHING;

ALTER TABLE "#__guidedtours" ADD COLUMN "autostart" int NOT NULL DEFAULT 0 /** CAN FAIL **/;

INSERT INTO "#__guidedtours" ("title", "uid", "description", "ordering", "extensions", "url", "created", "created_by", "modified", "modified_by", "published", "language", "access", "autostart") VALUES
('COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_TITLE', 'joomla-welcome', 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_DESCRIPTION', 1, '["com_cpanel"]', 'administrator/index.php', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, 1, '*', 1, 0)
ON CONFLICT DO NOTHING;

UPDATE "#__guidedtour_steps" SET "tour_id" = currval(pg_get_serial_sequence('#__guidedtours','id')) WHERE "tour_id"=0;
