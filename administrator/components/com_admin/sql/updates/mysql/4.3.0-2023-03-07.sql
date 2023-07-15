UPDATE `#__guidedtour_steps`
SET `target` = '#jform_description,#jform_description_ifr'
WHERE `target` = '#jform_description';

UPDATE `#__guidedtour_steps`
SET `target` = '#jform_articletext,#jform_articletext_ifr'
WHERE `target` = '#jform_articletext';
