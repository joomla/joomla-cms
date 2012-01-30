ALTER TABLE "#__languages" ADD COLUMN "ordering" bigint NOT NULL default 0;

CREATE INDEX "#__languages_idx_ordering" ON "#__languages" ("ordering");