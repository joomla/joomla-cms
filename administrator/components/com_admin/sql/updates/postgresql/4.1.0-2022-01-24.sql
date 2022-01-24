DROP INDEX IF EXISTS "#__redirect_links_idx_link_modifed";
CREATE INDEX "#__redirect_links_idx_link_modified" on "#__redirect_links" ("modified_date");
