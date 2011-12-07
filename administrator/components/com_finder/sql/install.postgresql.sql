--
-- Table: #__finder_filters
--

CREATE TABLE "#__finder_filters" (
  "filter_id" serial NOT NULL,
  "title" character varying(255) DEFAULT '' NOT NULL,
  "alias" character varying(255) DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "created_by_alias" character varying(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "map_count" bigint DEFAULT 0 NOT NULL,
  "data" text NOT NULL,
  "description" text,
  PRIMARY KEY ("filter_id")
);

--
-- Table: #__finder_links
--

CREATE TABLE "#__finder_links" (
  "link_id" serial NOT NULL,
  "url" character varying(255) DEFAULT '' NOT NULL,
  "route" character varying(255) DEFAULT '' NOT NULL,
  "title" character varying(255) DEFAULT '' NOT NULL,
  "description" character varying(255) DEFAULT '' NOT NULL,
  "indexdate" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "md5sum" character varying(32) DEFAULT '' NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "access" smallint DEFAULT 0 NOT NULL,
  "language" character(8) NOT NULL,
  "publish_start_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_end_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "start_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "end_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "list_price" double precision DEFAULT 0 NOT NULL,
  "sale_price" double precision DEFAULT 0 NOT NULL,
  "type_id" bigint DEFAULT 0 NOT NULL,
  "object" bytea NOT NULL,
  PRIMARY KEY ("link_id")
);
CREATE INDEX "#__finder_links_idx_type" ON "#__finder_links" ("type_id");
CREATE INDEX "#__finder_links_idx_title" ON "#__finder_links" ("title");
CREATE INDEX "#__finder_links_idx_md5" ON "#__finder_links" ("md5sum");
CREATE INDEX "#__finder_links_idx_url" ON "#__finder_links" ("url");
CREATE INDEX "#__finder_links_idx_published_list" ON "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "list_price");
CREATE INDEX "#__finder_links_idx_published_sale" ON "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "sale_price");

--
-- Table: #__finder_links_terms0
--

CREATE TABLE "#__finder_links_terms0" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms0_idx_term_weight" ON "#__finder_links_terms0" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms0_idx_link_term_weight" ON "#__finder_links_terms0" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms1
--

CREATE TABLE "#__finder_links_terms1" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms1_idx_term_weight" ON "#__finder_links_terms1" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms1_idx_link_term_weight" ON "#__finder_links_terms1" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms2
--

CREATE TABLE "#__finder_links_terms2" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms2_idx_term_weight" ON "#__finder_links_terms2" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms2_idx_link_term_weight" ON "#__finder_links_terms2" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms3
--

CREATE TABLE "#__finder_links_terms3" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms3_idx_term_weight" ON "#__finder_links_terms3" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms3_idx_link_term_weight" ON "#__finder_links_terms3" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms4
--

CREATE TABLE "#__finder_links_terms4" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms4_idx_term_weight" ON "#__finder_links_terms4" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms4_idx_link_term_weight" ON "#__finder_links_terms4" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms5
--

CREATE TABLE "#__finder_links_terms5" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms5_idx_term_weight" ON "#__finder_links_terms5" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms5_idx_link_term_weight" ON "#__finder_links_terms5" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms6
--

CREATE TABLE "#__finder_links_terms6" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms6_idx_term_weight" ON "#__finder_links_terms6" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms6_idx_link_term_weight" ON "#__finder_links_terms6" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms7
--

CREATE TABLE "#__finder_links_terms7" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms7_idx_term_weight" ON "#__finder_links_terms7" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms7_idx_link_term_weight" ON "#__finder_links_terms7" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms8
--

CREATE TABLE "#__finder_links_terms8" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms8_idx_term_weight" ON "#__finder_links_terms8" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms8_idx_link_term_weight" ON "#__finder_links_terms8" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_terms9
--

CREATE TABLE "#__finder_links_terms9" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms9_idx_term_weight" ON "#__finder_links_terms9" ("type_id", "weight");
CREATE INDEX "#__finder_links_terms9_idx_link_term_weight" ON "#__finder_links_terms9" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_termsa
--

CREATE TABLE "#__finder_links_termsa" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsa_idx_term_weight" ON "#__finder_links_termsa" ("type_id", "weight");
CREATE INDEX "#__finder_links_termsa_idx_link_term_weight" ON "#__finder_links_termsa" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_termsb
--

CREATE TABLE "#__finder_links_termsb" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsb_idx_term_weight" ON "#__finder_links_termsb" ("type_id", "weight");
CREATE INDEX "#__finder_links_termsb_idx_link_term_weight" ON "#__finder_links_termsb" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_termsc
--

CREATE TABLE "#__finder_links_termsc" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsc_idx_term_weight" ON "#__finder_links_termsc" ("type_id", "weight");
CREATE INDEX "#__finder_links_termsc_idx_link_term_weight" ON "#__finder_links_termsc" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_termsd
--

CREATE TABLE "#__finder_links_termsd" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsd_idx_term_weight" ON "#__finder_links_termsd" ("type_id", "weight");
CREATE INDEX "#__finder_links_termsd_idx_link_term_weight" ON "#__finder_links_termsd" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_termse
--

CREATE TABLE "#__finder_links_termse" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termse_idx_term_weight" ON "#__finder_links_termse" ("type_id", "weight");
CREATE INDEX "#__finder_links_termse_idx_link_term_weight" ON "#__finder_links_termse" ("link_id", "type_id", "weight");

--
-- Table: #__finder_links_termsf
--

CREATE TABLE "#__finder_links_termsf" (
  "link_id" bigint NOT NULL,
  "term_id" bigint NOT NULL,
  "weight" real NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsf_idx_term_weight" ON "#__finder_links_termsf" ("type_id", "weight");
CREATE INDEX "#__finder_links_termsf_idx_link_term_weight" ON "#__finder_links_termsf" ("link_id", "type_id", "weight");

--
-- Table: #__finder_taxonomy
--

CREATE TABLE "#__finder_taxonomy" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "title" character varying(255) DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 1 NOT NULL,
  "access" smallint DEFAULT 0 NOT NULL,
  "ordering" smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__finder_taxonomy_parent_id" ON "#__finder_taxonomy" ("parent_id");
CREATE INDEX "#__finder_taxonomy_state" ON "#__finder_taxonomy" ("state");
CREATE INDEX "#__finder_taxonomy_ordering" ON "#__finder_taxonomy" ("ordering");
CREATE INDEX "#__finder_taxonomy_access" ON "#__finder_taxonomy" ("access");
CREATE INDEX "#__finder_taxonomy_idx_parent_published" ON "#__finder_taxonomy" ("parent_id", "state", "access");

--
-- Dumping data for table #__finder_taxonomy
--
INSERT INTO "#__finder_taxonomy" ("id", "parent_id", "title", "state", "access", "ordering")
VALUES
	(1, 0, 'ROOT', 0, 0, 0);

--
-- Table: #__finder_taxonomy_map
--

CREATE TABLE "#__finder_taxonomy_map" (
  "link_id" bigint DEFAULT 0 NOT NULL,
  "node_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "node_id")
);
CREATE INDEX "#__finder_taxonomy_map_link_id" ON "#__finder_taxonomy_map" ("link_id");
CREATE INDEX "#__finder_taxonomy_map_node_id" ON "#__finder_taxonomy_map" ("node_id");

--
-- Table: #__finder_terms
--

CREATE TABLE "#__finder_terms" (
  "term_id" serial NOT NULL,
  "term" character varying(75) DEFAULT '' NOT NULL,
  "stem" character varying(75) DEFAULT '' NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" real DEFAULT 0 NOT NULL,
  "soundex" character varying(75) DEFAULT '' NOT NULL,
  "links" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("term_id"),
  CONSTRAINT "#__finder_terms_idx_term" UNIQUE ("term")
);
CREATE INDEX "#__finder_terms_idx_term_phrase" ON "#__finder_terms" ("term", "phrase");
CREATE INDEX "#__finder_terms_idx_stem_phrase" ON "#__finder_terms" ("stem", "phrase");
CREATE INDEX "#__finder_terms_idx_soundex_phrase" ON "#__finder_terms" ("soundex", "phrase");

--
-- Table: #__finder_terms_common
--

CREATE TABLE "#__finder_terms_common" (
  "term" character varying(75) DEFAULT '' NOT NULL,
  "language" character varying(75) DEFAULT '' NOT NULL,
);
CREATE INDEX "#__finder_terms_common_idx_word_lang" ON "#__finder_terms_common" ("term", "language");
CREATE INDEX "#__finder_terms_common_idx_lang" ON "#__finder_terms_common" ("language");

--
-- Dumping data for table #__finder_taxonomy
--
INSERT INTO "#__finder_terms_common" ("term", "language")
VALUES
	('a', 'en'),
	('about', 'en'),
	('after', 'en'),
	('ago', 'en'),
	('all', 'en'),
	('am', 'en'),
	('an', 'en'),
	('and', 'en'),
	('ani', 'en'),
	('any', 'en'),
	('are', 'en'),
	('aren''t', 'en'),
	('as', 'en'),
	('at', 'en'),
	('be', 'en'),
	('but', 'en'),
	('by', 'en'),
	('for', 'en'),
	('from', 'en'),
	('get', 'en'),
	('go', 'en'),
	('how', 'en'),
	('if', 'en'),
	('in', 'en'),
	('into', 'en'),
	('is', 'en'),
	('isn''t', 'en'),
	('it', 'en'),
	('its', 'en'),
	('me', 'en'),
	('more', 'en'),
	('most', 'en'),
	('must', 'en'),
	('my', 'en'),
	('new', 'en'),
	('no', 'en'),
	('none', 'en'),
	('not', 'en'),
	('noth', 'en'),
	('nothing', 'en'),
	('of', 'en'),
	('off', 'en'),
	('often', 'en'),
	('old', 'en'),
	('on', 'en'),
	('onc', 'en'),
	('once', 'en'),
	('onli', 'en'),
	('only', 'en'),
	('or', 'en'),
	('other', 'en'),
	('our', 'en'),
	('ours', 'en'),
	('out', 'en'),
	('over', 'en'),
	('page', 'en'),
	('she', 'en'),
	('should', 'en'),
	('small', 'en'),
	('so', 'en'),
	('some', 'en'),
	('than', 'en'),
	('thank', 'en'),
	('that', 'en'),
	('the', 'en'),
	('their', 'en'),
	('theirs', 'en'),
	('them', 'en'),
	('then', 'en'),
	('there', 'en'),
	('these', 'en'),
	('they', 'en'),
	('this', 'en'),
	('those', 'en'),
	('thus', 'en'),
	('time', 'en'),
	('times', 'en'),
	('to', 'en'),
	('too', 'en'),
	('true', 'en'),
	('under', 'en'),
	('until', 'en'),
	('up', 'en'),
	('upon', 'en'),
	('use', 'en'),
	('user', 'en'),
	('users', 'en'),
	('veri', 'en'),
	('version', 'en'),
	('very', 'en'),
	('via', 'en'),
	('want', 'en'),
	('was', 'en'),
	('way', 'en'),
	('were', 'en'),
	('what', 'en'),
	('when', 'en'),
	('where', 'en'),
	('whi', 'en'),
	('which', 'en'),
	('who', 'en'),
	('whom', 'en'),
	('whose', 'en'),
	('why', 'en'),
	('wide', 'en'),
	('will', 'en'),
	('with', 'en'),
	('within', 'en'),
	('without', 'en'),
	('would', 'en'),
	('yes', 'en'),
	('yet', 'en'),
	('you', 'en'),
	('your', 'en'),
	('yours', 'en');

--
-- Table: #__finder_tokens
--

CREATE TABLE "#__finder_tokens" (
  "term" character varying(75) DEFAULT '' NOT NULL,
  "stem" character varying(75) DEFAULT '' NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" real DEFAULT 0 NOT NULL,
  "context" smallint DEFAULT 0 NOT NULL,
);
CREATE INDEX "#__finder_tokens_idx_word" ON "#__finder_tokens" ("term");
CREATE INDEX "#__finder_tokens_idx_context" ON "#__finder_tokens" ("context");

--
-- Table: #__finder_tokens_aggregate
--

CREATE TABLE "#__finder_tokens_aggregate" (
  "term_id" bigint NOT NULL,
  "map_suffix" char(1) NOT NULL,
  "term" character varying(75) DEFAULT '' NOT NULL,
  "stem" character varying(75) DEFAULT '' NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "term_weight" real DEFAULT 0 NOT NULL,
  "context" smallint DEFAULT 2 NOT NULL,
  "context_weight" real DEFAULT 0 NOT NULL,
  "total_weight" real DEFAULT 0 NOT NULL,
);
CREATE INDEX "#__finder_tokens_aggregate_token" ON "#__finder_tokens_aggregate" ("term");
CREATE INDEX "#__finder_tokens_aggregate_keyword_id" ON "#__finder_tokens_aggregate" ("term_id");

--
-- Table: #__finder_types
--

CREATE TABLE "#__finder_types" (
  "id" serial NOT NULL,
  "title" character varying(100) NOT NULL,
  "mime" character varying(100) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__finder_types_title" UNIQUE ("title")
);
