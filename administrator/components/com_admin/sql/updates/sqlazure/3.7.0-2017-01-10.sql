-- Normalize finder tables default values.
-- finder_filters table
ALTER TABLE [#__finder_filters] ALTER COLUMN [title] [nvarchar](255) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_filters] ALTER COLUMN [alias] [nvarchar](255) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_filters] ALTER COLUMN [created_by] [bigint] NOT NULL DEFAULT 0;
ALTER TABLE [#__finder_filters] ALTER COLUMN [created_by_alias] [nvarchar](255) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_filters] ALTER COLUMN [data] [nvarchar](max) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_filters] ALTER COLUMN [params] [nvarchar](max) NOT NULL DEFAULT '';
-- finder_links table
ALTER TABLE [#__finder_links] ALTER COLUMN [url] [nvarchar](255) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_links] ALTER COLUMN [route] [nvarchar](255) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_links] ALTER COLUMN [title] [nvarchar](255) DEFAULT NUL;
ALTER TABLE [#__finder_links] ALTER COLUMN [description] [nvarchar](max) DEFAULT NULL;
ALTER TABLE [#__finder_links] ALTER COLUMN [md5sum] [nvarchar](32) DEFAULT NULL;
ALTER TABLE [#__finder_links] ALTER COLUMN [language] [nvarchar](8) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_links] ALTER COLUMN [type_id] [int] NOT NULL DEFAULT 0;
ALTER TABLE [#__finder_links] ALTER COLUMN [object] [nvarchar](max) NOT NULL DEFAULT '';
-- finder_links_termsx tables
ALTER TABLE [#__finder_links_terms0] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms1] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms2] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms3] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms4] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms5] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms6] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms7] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms8] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_terms9] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_termsa] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_termsb] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_termsc] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_termsd] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_termse] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_links_termsf] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
-- finder_taxonomy table
ALTER TABLE [#__finder_taxonomy] ALTER COLUMN [title] [nvarchar](255) NOT NULL DEFAULT '';
-- finder_terms table
ALTER TABLE [#__finder_terms] ALTER COLUMN [term] [nvarchar](75) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_terms] ALTER COLUMN [stem] [nvarchar](75) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_terms] ALTER COLUMN [soundex] [nvarchar](75) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_terms] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
-- finder_terms_common table
ALTER TABLE [#__finder_terms_common] ALTER COLUMN [term] [nvarchar](75) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_terms_common] ALTER COLUMN [language] [nvarchar](3) NOT NULL DEFAULT '';
-- finder_tokens table
ALTER TABLE [#__finder_tokens] ALTER COLUMN [term] [nvarchar](75) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_tokens] ALTER COLUMN [stem] [nvarchar](75) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_tokens] ALTER COLUMN [weight] [real] NOT NULL DEFAULT 1;
-- finder_tokens_aggregate table
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [term_id] [bigint] NOT NULL DEFAULT 0;
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [map_suffix] [nchar](1) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [term] [nvarchar](75) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [stem] [nvarchar](75) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [term_weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [context_weight] [real] NOT NULL DEFAULT 1;
ALTER TABLE [#__finder_tokens_aggregate] ALTER COLUMN [total_weight] [real] NOT NULL DEFAULT 1;
-- finder_types table
ALTER TABLE [#__finder_types] ALTER COLUMN [title] [nvarchar](100) NOT NULL DEFAULT '';
ALTER TABLE [#__finder_types] ALTER COLUMN [mime] [nvarchar](100) NOT NULL DEFAULT '';
