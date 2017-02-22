ALTER TABLE "#__menu_types" ADD "client_id" int DEFAULT 0 NOT NULL;

UPDATE "#__menu" SET "published" = 1 WHERE "menutype" = 'main' OR "menutype" = 'menu';
