ALTER TABLE [#__contentitem_tag_map] 
DROP CONSTRAINT [#__contentitem_tag_map$uc_ItemnameTagid]
;

ALTER TABLE [#__contentitem_tag_map] 
ADD CONSTRAINT [#__contentitem_tag_map$uc_ItemnameTagid] UNIQUE NONCLUSTERED
([type_id] ASC,	[content_item_id] ASC,	[tag_id] ASC
)WITH (PAD_INDEX  = OFF, STATISTICS_NORECOMPUTE  = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS  = ON, ALLOW_PAGE_LOCKS  = ON) ON [PRIMARY]
;
