UPDATE "#__guidedtour_steps"
SET "ordering" = "ordering" + 1
WHERE "title" IN ('COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_SAVECLOSE_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONGRATULATIONS_TITLE');

INSERT INTO "#__guidedtour_steps" ("title", "description", "position", "target", "type", "interactive_type", "url", "published", "language", "note", "tour_id", "created", "created_by", "modified", "modified_by", "ordering")
SELECT 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_AUTOSTART_TITLE', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_AUTOSTART_DESCRIPTION', 'bottom', '#jform_autostart0', 2, 3, '', 1, '*', '', "tour_id", CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, "ordering" + 1
  FROM "#__guidedtour_steps"
 WHERE "title" = 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_COMPONENT_TITLE';
