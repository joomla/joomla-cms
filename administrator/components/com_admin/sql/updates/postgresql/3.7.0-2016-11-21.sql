-- Replace language image UNIQUE index for a normal INDEX.
ALTER TABLE "#__languages" DROP CONSTRAINT "#__idx_image";
CREATE INDEX "#__idx_image" ON "#__languages" ("image");
