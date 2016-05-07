-- Insert the new com_finder menu childs.
INSERT INTO "#__menu" ("menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "checked_out", "checked_out_time", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id")
VALUES
('menu', 'com_finder_indexed_content', 'Indexed Content', '', 'Smart Search/Indexed Content', 'index.php?option=com_finder', 'component', 0, 1, 2, 27, 0, '1970-01-01 00:00:00', 0, 0, 'class:finder', 0, '', 43, 44, 0, '*', 1),
('menu', 'com_finder_content_maps', 'Content Maps', '', 'Smart Search/Content Maps', 'index.php?option=com_finder&view=maps', 'component', 0, 1, 2, 27, 0, '1970-01-01 00:00:00', 0, 0, 'class:finder-maps', 0, '', 44, 45, 0, '*', 1),
('menu', 'com_finder_filters', 'Filters', '', 'Smart Search/Filters', 'index.php?option=com_finder&view=filters', 'component', 0, 1, 2, 27, 0, '1970-01-01 00:00:00', 0, 0, 'class:finder-filters', 0, '', 46, 47, 0, '*', 1);

-- Get the current com_finder parent menu id and update the childs.
UPDATE "#__menu" SET "parent_id" = (
									SELECT "m2"."id" FROM ( SELECT * FROM "#__menu" ) AS "m2"
									WHERE "m2"."menutype" = 'menu'
									AND "m2"."level" = 1
									AND "m2"."component_id" = 27
									)
WHERE "menutype" = 'menu' AND "level" = 2 AND "component_id" = 27;
