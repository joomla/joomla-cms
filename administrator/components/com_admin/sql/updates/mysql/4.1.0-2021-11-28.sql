--
-- Convert core templates to new mode
--
UPDATE `#__template_styles`
   SET `params` = REPLACE(`params`,'"useFontScheme":"templates\\/cassiopeia\\/css\\/','"useFontScheme":"media\\/templates\\/site\\/cassiopeia\\/css\\/')
 WHERE `template` = 'cassiopeia'
   AND `client_id` = 0;
