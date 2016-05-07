CREATE INDEX "idx_published_ordering" ON "#__languages" ("published","ordering");
CREATE INDEX "idx_client_id" ON "#__template_styles" ("client_id");
CREATE INDEX "idx_alias_item_id" ON "#__contentitem_tag_map" ("type_alias","content_item_id");
CREATE INDEX "idx_type_ordering" ON "#__extensions" ("type","ordering");
CREATE INDEX "idx_client_id_published_lft" ON "#__menu" ("client_id","published","lft");
CREATE INDEX "idx_ordering_title" ON "#__viewlevels" ("ordering","title");
