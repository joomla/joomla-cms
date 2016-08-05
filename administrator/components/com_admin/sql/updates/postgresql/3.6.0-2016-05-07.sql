CREATE INDEX "#__languages_idx_published_ordering" ON "#__languages" ("published","ordering");
CREATE INDEX "#__template_styles_idx_client_id" ON "#__template_styles" ("client_id");
CREATE INDEX "#__contentitem_tag_map_idx_alias_item_id" ON "#__contentitem_tag_map" ("type_alias","content_item_id");
CREATE INDEX "#__extensions_idx_type_ordering" ON "#__extensions" ("type","ordering");
CREATE INDEX "#__menu_idx_client_id_published_lft" ON "#__menu" ("client_id","published","lft");
CREATE INDEX "#__viewlevels_idx_ordering_title" ON "#__viewlevels" ("ordering","title");
