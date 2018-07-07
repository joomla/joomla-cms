-- Replace language image UNIQUE index for a normal INDEX.
ALTER TABLE "#__languages" DROP CONSTRAINT "#__languages_idx_image";
