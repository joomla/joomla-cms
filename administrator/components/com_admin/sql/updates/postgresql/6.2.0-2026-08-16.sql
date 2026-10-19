--
-- Add additional category steps to existing guided tours.
--

UPDATE "#__guidedtour_steps"
SET "target" = 'joomla-field-fancy-select:has(#jform_tags) .choices'
WHERE "title" IN (
    'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_TAGS_TITLE',
    'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_TAGS_TITLE',
    'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_TAGS_TITLE'
);

UPDATE "#__guidedtour_steps"
SET "ordering" = "ordering" + 1
WHERE ("tour_id" = 3 AND "ordering" >= 25)
   OR ("tour_id" = 7 AND "ordering" >= 67)
   OR ("tour_id" = 8 AND "ordering" >= 77)
   OR ("tour_id" = 9 AND "ordering" >= 90);

INSERT INTO "#__guidedtour_steps" ("tour_id", "title", "published", "description", "ordering", "position", "target", "type", "interactive_type", "url", "created", "created_by", "modified", "modified_by", "language") VALUES
(3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_ADDITIONAL_CATEGORIES_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_ADDITIONAL_CATEGORIES_DESCRIPTION', 25, 'top', 'joomla-field-fancy-select:has(#jform_secondary_categories) .choices', 2, 3, 'administrator/index.php?option=com_content&view=article&layout=edit', '2026-08-18 00:00:00', 42, '2026-08-18 00:00:00', 42, '*'),
(7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_ADDITIONAL_CATEGORIES_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_ADDITIONAL_CATEGORIES_DESCRIPTION', 67, 'top', 'joomla-field-fancy-select:has(#jform_secondary_categories) .choices', 2, 3, 'administrator/index.php?option=com_banners&view=banner&layout=edit', '2026-08-18 00:00:00', 42, '2026-08-18 00:00:00', 42, '*'),
(8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_ADDITIONAL_CATEGORIES_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_ADDITIONAL_CATEGORIES_DESCRIPTION', 77, 'top', 'joomla-field-fancy-select:has(#jform_secondary_categories) .choices', 2, 3, 'administrator/index.php?option=com_contact&view=contact&layout=edit', '2026-08-18 00:00:00', 42, '2026-08-18 00:00:00', 42, '*'),
(9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_ADDITIONAL_CATEGORIES_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_ADDITIONAL_CATEGORIES_DESCRIPTION', 90, 'top', 'joomla-field-fancy-select:has(#jform_secondary_categories) .choices', 2, 3, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', '2026-08-18 00:00:00', 42, '2026-08-18 00:00:00', 42, '*');
