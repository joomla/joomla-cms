UPDATE `#__guidedtour_steps`
SET `target` = '#jform_published'
WHERE `target` = '#jform_state' AND `id` = 87;

UPDATE `#__guidedtour_steps`
SET `target` = '#jform_sendEmail0'
WHERE `target` = '#jform_sendEmail';

UPDATE `#__guidedtour_steps`
SET `target` = '#jform_block0'
WHERE `target` = '#jform_block';

UPDATE `#__guidedtour_steps`
SET `target` = '#jform_requireReset0'
WHERE `target` = '#jform_requireReset';
