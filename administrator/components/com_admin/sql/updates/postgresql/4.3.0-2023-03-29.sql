UPDATE "#__guidedtour_steps"
SET "target" = 'joomla-field-fancy-select .choices'
WHERE "id" = 5;

UPDATE "#__guidedtour_steps"
SET "target" = 'joomla-field-fancy-select .choices[data-type=select-multiple]'
WHERE "id" IN (26,38,78,90);
