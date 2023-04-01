UPDATE "#__guidedtour_steps"
SET "target" = '.col-lg-9'
WHERE "target" = '#jform_description,#jform_description_ifr' AND "id" = 63;

UPDATE "#__guidedtour_steps"
SET "title" = 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_DETAILS_TITLE'
WHERE "title" = 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_DESCRIPTION_TITLE';

UPDATE "#__guidedtour_steps"
SET "description" = 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_DETAILS_DESCRIPTION'
WHERE "description" = 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_DESCRIPTION_DESCRIPTION';
