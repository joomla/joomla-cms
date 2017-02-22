ALTER TABLE "#__contentitem_tag_map" DROP CONSTRAINT "#__uc_ItemnameTagid", ADD CONSTRAINT "#__uc_ItemnameTagid" UNIQUE ("type_id", "content_item_id", "tag_id");
