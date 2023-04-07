UPDATE `#__guidedtour_steps`
SET `type` = 2, `interactive_type` = 2
WHERE `id` IN (20,27,28,33,39,40,46,51,56,57,62,67,72,79,84,91,96);

UPDATE `#__guidedtour_steps`
SET `type` = 2, `interactive_type` = 3
WHERE `id` IN (5,21,22,23,24,25,26,34,35,36,37,38,52,53,54,55,63,64,65,66,74,75,76,77,78,86,87,88,89,90,98,107,108,109);

UPDATE `#__guidedtour_steps`
SET `target` = 'joomla-field-fancy-select .choices input'
WHERE `id` = 5;

UPDATE `#__guidedtour_steps`
SET `target` = 'joomla-field-fancy-select .choices[data-type=select-one]'
WHERE `id` IN (23,35,53,65,75,88);

UPDATE `#__guidedtour_steps`
SET `target` = 'joomla-field-fancy-select .choices[data-type=select-multiple] input'
WHERE `id` IN (26,38,78,90);
