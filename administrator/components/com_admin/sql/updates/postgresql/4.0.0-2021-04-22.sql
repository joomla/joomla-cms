ALTER TABLE "#__extensions" ADD COLUMN "extension" VARCHAR(127) NOT NULL DEFAULT '';
UPDATE "#__mail_templates" SET "extension" = SUBSTRING("template_id", 1, POSITION('.' IN "template_id") - 1);
