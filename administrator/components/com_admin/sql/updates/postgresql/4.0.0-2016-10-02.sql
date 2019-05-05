DELETE FROM "#__extensions" WHERE "type" = 'template' AND "element" = 'hathor' AND "client_id" = 1;
DELETE FROM "#__template_styles" WHERE "template" = 'hathor' AND "client_id" = 1;
ALTER TABLE "#__user_keys" DROP COLUMN "invalid";
