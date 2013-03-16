ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [term_id] [bigint] NULL;
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [map_suffix] [nchar](1) NULL;
ALTER TABLE [#__finder_tokens_aggregate] ADD DEFAULT ((0)) FOR [term_id];
ALTER TABLE [#__finder_tokens_aggregate] ADD DEFAULT ((0)) FOR [total_weight];
