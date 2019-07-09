DROP INDEX IF EXISTS "#__template_styles_idx_client_id_home";
ALTER TABLE "#__template_styles" ALTER COLUMN "home" TYPE varchar(7);
ALTER TABLE "#__template_styles" ALTER COLUMN "home" SET DEFAULT '0';
CREATE INDEX "#__template_styles_idx_client_id_home_2" ON "#__template_styles" ("client_id", "home");
