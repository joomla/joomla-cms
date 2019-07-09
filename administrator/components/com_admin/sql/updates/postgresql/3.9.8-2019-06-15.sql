DROP INDEX IF EXISTS "#__template_styles_idx_home";
ALTER TABLE "#__template_styles" ALTER COLUMN "home" TYPE smallint;
ALTER TABLE "#__template_styles" ALTER COLUMN "home" SET DEFAULT 0;
CREATE INDEX "#__template_styles_idx_client_id" ON "#__template_styles" ("client_id");
CREATE INDEX "#__template_styles_idx_client_id_home" ON "#__template_styles" ("client_id", "home");
