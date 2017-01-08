-- Normalize finder tables default values.
-- finder_filters table
ALTER TABLE "#__finder_filters" ALTER COLUMN "title" SET DEFAULT '';
ALTER TABLE "#__finder_filters" ALTER COLUMN "alias" SET DEFAULT '';
ALTER TABLE "#__finder_filters" ALTER COLUMN "created_by" SET DEFAULT 0;
ALTER TABLE "#__finder_filters" ALTER COLUMN "created_by_alias" SET DEFAULT '';
ALTER TABLE "#__finder_filters" ALTER COLUMN "data" SET DEFAULT '';
ALTER TABLE "#__finder_filters" ALTER COLUMN "params" SET DEFAULT '';
ALTER TABLE "#__finder_filters" ALTER COLUMN "params" SET NOT NULL;
-- finder_links table
ALTER TABLE "#__finder_links" ALTER COLUMN "url" SET DEFAULT '';
ALTER TABLE "#__finder_links" ALTER COLUMN "route" SET DEFAULT '';
ALTER TABLE "#__finder_links" ALTER COLUMN "state" SET NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "access" SET NOT NULL;
ALTER TABLE "#__finder_links" ALTER COLUMN "type_id" SET DEFAULT 0;
ALTER TABLE "#__finder_links" ALTER COLUMN "object" SET DEFAULT '';
-- finder_links_termsx tables
ALTER TABLE "#__finder_links_terms0" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms1" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms2" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms3" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms4" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms5" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms6" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms7" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms8" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_terms9" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_termsa" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_termsb" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_termsc" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_termsd" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_termse" ALTER COLUMN "weight" SET DEFAULT 1;
ALTER TABLE "#__finder_links_termsf" ALTER COLUMN "weight" SET DEFAULT 1;
-- finder_terms table
ALTER TABLE "#__finder_terms" ALTER COLUMN "term" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "stem" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "soundex" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "language" SET DEFAULT '';
ALTER TABLE "#__finder_terms" ALTER COLUMN "weight" SET DEFAULT 1;
-- finder_terms_common table
ALTER TABLE "#__finder_terms_common" ALTER COLUMN "term" SET DEFAULT '';
-- finder_tokens table
ALTER TABLE "#__finder_tokens" ALTER COLUMN "term" SET DEFAULT '';
ALTER TABLE "#__finder_tokens" ALTER COLUMN "stem" SET DEFAULT '';
ALTER TABLE "#__finder_tokens" ALTER COLUMN "language" SET DEFAULT '';
-- finder_tokens_aggregate table
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "term_id" SET DEFAULT 0;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "map_suffix" SET DEFAULT '';
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "term" SET DEFAULT '';
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "stem" SET DEFAULT '';
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "term_weight" SET DEFAULT 1;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "context_weight" SET DEFAULT 1;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "total_weight" SET DEFAULT 1;
ALTER TABLE "#__finder_tokens_aggregate" ALTER COLUMN "language" SET DEFAULT '';
-- finder_types table
ALTER TABLE "#__finder_types" ALTER COLUMN "title" SET DEFAULT '';
ALTER TABLE "#__finder_types" ALTER COLUMN "mime" SET DEFAULT '';
