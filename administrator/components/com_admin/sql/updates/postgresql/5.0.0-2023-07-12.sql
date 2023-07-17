ALTER TABLE "#__menu_types" ADD COLUMN "ordering" int NOT NULL DEFAULT 0;
UPDATE "#__menu_types" SET "ordering" = "id" WHERE "client_id" = 0;
