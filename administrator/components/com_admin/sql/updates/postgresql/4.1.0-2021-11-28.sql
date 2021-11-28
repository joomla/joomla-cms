--
-- Convert core templates to new mode
--
UPDATE "#__template_styles" SET "inheritable" = 1 WHERE "template" = 'atum' AND "client_id" = 1 OR "template" = 'cassiopeia' AND "client_id" = 0;
