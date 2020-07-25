ALTER TABLE "#__template_styles" ADD COLUMN "parent" smallint NOT NULL DEFAULT 0;
ALTER TABLE "#__template_styles" ADD COLUMN "inherits" varchar(50) DEFAULT "";
