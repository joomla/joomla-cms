DROP INDEX IF EXISTS "#__menu_idx_client_id_parent_id_alias";

CREATE UNIQUE INDEX "#__menu_idx_client_id_parent_id_alias_language" ON "#__menu" ("client_id", "parent_id", "alias", "language");