DROP INDEX IF EXISTS "#__template_styles_idx_home";
ALTER TABLE "#__template_style" ALTER COLUMN "home" TYPE smallint;
ALTER TABLE "#__template_style" ALTER COLUMN "home" SET DEFAULT 0;
CREATE INDEX "#__template_styles_idx_home" ON "#__template_style" ("home");
