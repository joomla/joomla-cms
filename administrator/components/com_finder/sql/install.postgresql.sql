--
-- Table structure for table `#__finder_filters`
--

CREATE TABLE IF NOT EXISTS "#__finder_filters" (
  "filter_id" serial NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 1 NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" integer DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "map_count" integer DEFAULT 0 NOT NULL,
  "data" text DEFAULT '' NOT NULL,
  "params" text DEFAULT '' NOT NULL,
  PRIMARY KEY ("filter_id")
);

--
-- Table structure for table `#__finder_links`
--

CREATE TABLE IF NOT EXISTS "#__finder_links" (
  "link_id" serial NOT NULL,
  "url" varchar(255) DEFAULT '' NOT NULL,
  "route" varchar(255) DEFAULT '' NOT NULL,
  "title" varchar(400) DEFAULT NULL,
  "description" text DEFAULT '' NOT NULL,
  "indexdate" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "md5sum" varchar(32) DEFAULT NULL,
  "published" smallint DEFAULT 1 NOT NULL,
  "state" integer DEFAULT 1 NOT NULL,
  "access" integer DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "publish_start_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_end_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "start_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "end_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "list_price" numeric(8,2) DEFAULT 0 NOT NULL,
  "sale_price" numeric(8,2) DEFAULT 0 NOT NULL,
  "type_id" bigint DEFAULT 0 NOT NULL,
  "object" bytea DEFAULT '' NOT NULL,
  PRIMARY KEY ("link_id")
);
CREATE INDEX "#__finder_links_idx_type" on "#__finder_links" ("type_id");
CREATE INDEX "#__finder_links_idx_title" on "#__finder_links" ("title");
CREATE INDEX "#__finder_links_idx_md5" on "#__finder_links" ("md5sum");
CREATE INDEX "#__finder_links_idx_language" on "#__finder_links" ("language");
CREATE INDEX "#__finder_links_idx_url" on "#__finder_links" (substr(url,0,76));
CREATE INDEX "#__finder_links_idx_published_list" on "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "list_price");
CREATE INDEX "#__finder_links_idx_published_sale" on "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "sale_price");

--
-- Table structure for table `#__finder_links_terms0`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms0" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms0_idx_term_weight" on "#__finder_links_terms0" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms0_idx_link_term_weight" on "#__finder_links_terms0" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms1`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms1" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms1_idx_term_weight" on "#__finder_links_terms1" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms1_idx_link_term_weight" on "#__finder_links_terms1" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms2`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms2" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms2_idx_term_weight" on "#__finder_links_terms2" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms2_idx_link_term_weight" on "#__finder_links_terms2" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms3`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms3" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms3_idx_term_weight" on "#__finder_links_terms3" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms3_idx_link_term_weight" on "#__finder_links_terms3" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms4`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms4" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms4_idx_term_weight" on "#__finder_links_terms4" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms4_idx_link_term_weight" on "#__finder_links_terms4" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms5`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms5" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms5_idx_term_weight" on "#__finder_links_terms5" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms5_idx_link_term_weight" on "#__finder_links_terms5" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms6`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms6" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms6_idx_term_weight" on "#__finder_links_terms6" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms6_idx_link_term_weight" on "#__finder_links_terms6" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms7`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms7" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms7_idx_term_weight" on "#__finder_links_terms7" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms7_idx_link_term_weight" on "#__finder_links_terms7" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms8`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms8" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms8_idx_term_weight" on "#__finder_links_terms8" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms8_idx_link_term_weight" on "#__finder_links_terms8" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_terms9`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms9" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms9_idx_term_weight" on "#__finder_links_terms9" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms9_idx_link_term_weight" on "#__finder_links_terms9" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_termsa`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_termsa" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsa_idx_term_weight" on "#__finder_links_termsa" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsa_idx_link_term_weight" on "#__finder_links_termsa" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_termsb`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_termsb" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsb_idx_term_weight" on "#__finder_links_termsb" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsb_idx_link_term_weight" on "#__finder_links_termsb" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_termsc`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_termsc" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsc_idx_term_weight" on "#__finder_links_termsc" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsc_idx_link_term_weight" on "#__finder_links_termsc" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_termsd`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_termsd" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsd_idx_term_weight" on "#__finder_links_termsd" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsd_idx_link_term_weight" on "#__finder_links_termsd" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_termse`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_termse" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termse_idx_term_weight" on "#__finder_links_termse" ("term_id", "weight");
CREATE INDEX "#__finder_links_termse_idx_link_term_weight" on "#__finder_links_termse" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_links_termsf`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_termsf" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsf_idx_term_weight" on "#__finder_links_termsf" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsf_idx_link_term_weight" on "#__finder_links_termsf" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_taxonomy`
--

CREATE TABLE IF NOT EXISTS "#__finder_taxonomy" (
  "id" serial NOT NULL,
  "parent_id" integer DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 1 NOT NULL,
  "access" smallint DEFAULT 0 NOT NULL,
  "ordering" smallint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__finder_taxonomy_parent_id" on "#__finder_taxonomy" ("parent_id");
CREATE INDEX "#__finder_taxonomy_state" on "#__finder_taxonomy" ("state");
CREATE INDEX "#__finder_taxonomy_ordering" on "#__finder_taxonomy" ("ordering");
CREATE INDEX "#__finder_taxonomy_access" on "#__finder_taxonomy" ("access");
CREATE INDEX "#__finder_taxonomy_idx_parent_published" on "#__finder_taxonomy" ("parent_id", "state", "access");

--
-- Dumping data for table `#__finder_taxonomy`
--

INSERT INTO "#__finder_taxonomy" ("id", "parent_id", "title", "state", "access", "ordering") VALUES
(1, 0, 'ROOT', 0, 0, 0);

SELECT setval('#__finder_taxonomy_id_seq', 2, false);

--
-- Table structure for table `#__finder_taxonomy_map`
--

CREATE TABLE IF NOT EXISTS "#__finder_taxonomy_map" (
  "link_id" integer NOT NULL,
  "node_id" integer NOT NULL,
  PRIMARY KEY ("link_id", "node_id")
);
CREATE INDEX "#__finder_taxonomy_map_link_id" on "#__finder_taxonomy_map" ("link_id");
CREATE INDEX "#__finder_taxonomy_map_node_id" on "#__finder_taxonomy_map" ("node_id");

--
-- Table structure for table `#__finder_terms`
--

CREATE TABLE IF NOT EXISTS "#__finder_terms" (
  "term_id" serial NOT NULL,
  "term" varchar(75) DEFAULT '' NOT NULL,
  "stem" varchar(75) DEFAULT '' NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  "soundex" varchar(75) DEFAULT '' NOT NULL,
  "links" integer DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  PRIMARY KEY ("term_id"),
  CONSTRAINT "#__finder_terms_idx_term" UNIQUE ("term")
);
CREATE INDEX "#__finder_terms_idx_term_phrase" on "#__finder_terms" ("term", "phrase");
CREATE INDEX "#__finder_terms_idx_stem_phrase" on "#__finder_terms" ("stem", "phrase");
CREATE INDEX "#__finder_terms_idx_soundex_phrase" on "#__finder_terms" ("soundex", "phrase");

--
-- Table structure for table `#__finder_terms_common`
--

CREATE TABLE IF NOT EXISTS "#__finder_terms_common" (
  "term" varchar(75) DEFAULT '' NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL
);
CREATE INDEX "#__finder_terms_common_idx_word_lang" on "#__finder_terms_common" ("term", "language");
CREATE INDEX "#__finder_terms_common_idx_lang" on "#__finder_terms_common" ("language");

--
-- Dumping data for table `#__finder_terms_common`
--

INSERT INTO "#__finder_terms_common" ("term", "language") VALUES
('a', 'en'),
('about', 'en'),
('after', 'en'),
('ago', 'en'),
('all', 'en'),
('am', 'en'),
('an', 'en'),
('and', 'en'),
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
('nothing', 'en'),
('of', 'en'),
('off', 'en'),
('often', 'en'),
('old', 'en'),
('on', 'en'),
('onc', 'en'),
('once', 'en'),
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
-- Table structure for table `#__finder_tokens`
--

CREATE TABLE IF NOT EXISTS "#__finder_tokens" (
  "term" varchar(75) DEFAULT '' NOT NULL,
  "stem" varchar(75) DEFAULT '' NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  "context" smallint DEFAULT 2 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL
);
CREATE INDEX "#__finder_tokens_idx_word" on "#__finder_tokens" ("term");
CREATE INDEX "#__finder_tokens_idx_stem" on "#__finder_tokens" ("stem");
CREATE INDEX "#__finder_tokens_idx_context" on "#__finder_tokens" ("context");
CREATE INDEX "#__finder_tokens_idx_language" on "#__finder_tokens" ("language");

--
-- Table structure for table `#__finder_tokens_aggregate`
--

CREATE TABLE IF NOT EXISTS "#__finder_tokens_aggregate" (
  "term_id" integer DEFAULT 0 NOT NULL,
  "map_suffix" varchar(1) DEFAULT '' NOT NULL,
  "term" varchar(75) DEFAULT '' NOT NULL,
  "stem" varchar(75) DEFAULT '' NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "term_weight" numeric(8,2) NOT NULL DEFAULT 0,
  "context" smallint DEFAULT 2 NOT NULL,
  "context_weight" numeric(8,2) NOT NULL DEFAULT 0,
  "total_weight" numeric(8,2) NOT NULL DEFAULT 0,
  "language" varchar(7) DEFAULT '' NOT NULL
);
CREATE INDEX "#__finder_tokens_aggregate_token" on "#__finder_tokens_aggregate" ("term");
CREATE INDEX "_#__finder_tokens_aggregate_keyword_id" on "#__finder_tokens_aggregate" ("term_id");

--
-- Table structure for table `#__finder_types`
--

CREATE TABLE IF NOT EXISTS "#__finder_types" (
  "id" serial NOT NULL,
  "title" varchar(100) DEFAULT '' NOT NULL,
  "mime" varchar(100) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__finder_types_title" UNIQUE ("title")
);

--
-- Here is SOUNDEX replacement for those who can't enable fuzzystrmatch module
--   from contrib folder.
-- This function comes from https://wiki.postgresql.org/wiki/Soundex
--   and is distributed with GPL license.
-- Thanks to its author, Marti Raudsepp, that published this piece of code.
--

CREATE OR REPLACE FUNCTION soundex(input text) RETURNS text
IMMUTABLE STRICT COST 500 LANGUAGE plpgsql
AS $$
DECLARE
  soundex text = '';
  char text;
  symbol text;
  last_symbol text = '';
  pos int = 1;
BEGIN
  WHILE length(soundex) < 4 LOOP
    char = upper(substr(input, pos, 1));
    pos = pos + 1;
    CASE char
    WHEN '' THEN
      -- End of input string
      IF soundex = '' THEN
        RETURN '';
      ELSE
        RETURN rpad(soundex, 4, '0');
      END IF;
    WHEN 'B', 'F', 'P', 'V' THEN
      symbol = '1';
    WHEN 'C', 'G', 'J', 'K', 'Q', 'S', 'X', 'Z' THEN
      symbol = '2';
    WHEN 'D', 'T' THEN
      symbol = '3';
    WHEN 'L' THEN
      symbol = '4';
    WHEN 'M', 'N' THEN
      symbol = '5';
    WHEN 'R' THEN
      symbol = '6';
    ELSE
      -- Not a consonant; no output, but next similar consonant will be re-recorded
      symbol = '';
    END CASE;

    IF soundex = '' THEN
      -- First character; only accept strictly English ASCII characters
      IF char ~>=~ 'A' AND char ~<=~ 'Z' THEN
        soundex = char;
        last_symbol = symbol;
      END IF;
    ELSIF last_symbol != symbol THEN
      soundex = soundex || symbol;
      last_symbol = symbol;
    END IF;
  END LOOP;

  RETURN soundex;
END;
$$;
