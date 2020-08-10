ALTER TABLE "#__template_styles" ADD COLUMN "inheritable" smallint NOT NULL DEFAULT 0;
ALTER TABLE "#__template_styles" ADD COLUMN "parent" varchar(50) DEFAULT '';
