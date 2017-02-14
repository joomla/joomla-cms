--
-- Table: #__finder_filters
--
CREATE TABLE "#__finder_filters" (
  "filter_id" serial NOT NULL,
  "title" character varying(255) NOT NULL,
  "alias" character varying(255) NOT NULL,
  "state" smallint DEFAULT 1 NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created_by" integer NOT NULL,
  "created_by_alias" character varying(255) NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "map_count" integer DEFAULT 0 NOT NULL,
  "data" text NOT NULL,
  "params" text,
  PRIMARY KEY ("filter_id")
);

--
-- Table: #__finder_links
--
CREATE TABLE "#__finder_links" (
  "link_id" serial NOT NULL,
  "url" character varying(255) NOT NULL,
  "route" character varying(255) NOT NULL,
  "title" character varying(255) DEFAULT NULL,
  "description" character varying(255) DEFAULT NULL,
  "indexdate" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "md5sum" character varying(32) DEFAULT NULL,
  "published" smallint DEFAULT 1 NOT NULL,
  "state" integer DEFAULT 1,
  "access" integer DEFAULT 0,
  "language" character varying(8) NOT NULL,
  "publish_start_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_end_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "start_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "end_date" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "list_price" numeric(8,2) DEFAULT 0 NOT NULL,
  "sale_price" numeric(8,2) DEFAULT 0 NOT NULL,
  "type_id" bigint NOT NULL,
  "object" bytea NOT NULL,
  PRIMARY KEY ("link_id")
);
CREATE INDEX "#__finder_links_idx_type" on "#__finder_links" ("type_id");
CREATE INDEX "#__finder_links_idx_title" on "#__finder_links" ("title");
CREATE INDEX "#__finder_links_idx_md5" on "#__finder_links" ("md5sum");
CREATE INDEX "#__finder_links_idx_url" on "#__finder_links" (url(75));
CREATE INDEX "#__finder_links_idx_published_list" on "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "list_price");
CREATE INDEX "#__finder_links_idx_published_sale" on "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "sale_price");

--
-- Table: #__finder_links_terms0
--
CREATE TABLE "#__finder_links_terms0" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms0_idx_term_weight" on "#__finder_links_terms0" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms0_idx_link_term_weight" on "#__finder_links_terms0" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms1
--
CREATE TABLE "#__finder_links_terms1" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms1_idx_term_weight" on "#__finder_links_terms1" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms1_idx_link_term_weight" on "#__finder_links_terms1" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms2
--
CREATE TABLE "#__finder_links_terms2" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms2_idx_term_weight" on "#__finder_links_terms2" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms2_idx_link_term_weight" on "#__finder_links_terms2" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms3
--
CREATE TABLE "#__finder_links_terms3" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms3_idx_term_weight" on "#__finder_links_terms3" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms3_idx_link_term_weight" on "#__finder_links_terms3" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms4
--
CREATE TABLE "#__finder_links_terms4" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms4_idx_term_weight" on "#__finder_links_terms4" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms4_idx_link_term_weight" on "#__finder_links_terms4" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms5
--
CREATE TABLE "#__finder_links_terms5" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms5_idx_term_weight" on "#__finder_links_terms5" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms5_idx_link_term_weight" on "#__finder_links_terms5" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms6
--
CREATE TABLE "#__finder_links_terms6" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms6_idx_term_weight" on "#__finder_links_terms6" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms6_idx_link_term_weight" on "#__finder_links_terms6" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms7
--
CREATE TABLE "#__finder_links_terms7" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms7_idx_term_weight" on "#__finder_links_terms7" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms7_idx_link_term_weight" on "#__finder_links_terms7" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms8
--
CREATE TABLE "#__finder_links_terms8" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms8_idx_term_weight" on "#__finder_links_terms8" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms8_idx_link_term_weight" on "#__finder_links_terms8" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_terms9
--
CREATE TABLE "#__finder_links_terms9" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms9_idx_term_weight" on "#__finder_links_terms9" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms9_idx_link_term_weight" on "#__finder_links_terms9" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsa
--
CREATE TABLE "#__finder_links_termsa" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsa_idx_term_weight" on "#__finder_links_termsa" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsa_idx_link_term_weight" on "#__finder_links_termsa" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsb
--
CREATE TABLE "#__finder_links_termsb" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsb_idx_term_weight" on "#__finder_links_termsb" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsb_idx_link_term_weight" on "#__finder_links_termsb" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsc
--
CREATE TABLE "#__finder_links_termsc" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsc_idx_term_weight" on "#__finder_links_termsc" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsc_idx_link_term_weight" on "#__finder_links_termsc" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsd
--
CREATE TABLE "#__finder_links_termsd" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsd_idx_term_weight" on "#__finder_links_termsd" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsd_idx_link_term_weight" on "#__finder_links_termsd" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termse
--
CREATE TABLE "#__finder_links_termse" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termse_idx_term_weight" on "#__finder_links_termse" ("term_id", "weight");
CREATE INDEX "#__finder_links_termse_idx_link_term_weight" on "#__finder_links_termse" ("link_id", "term_id", "weight");

--
-- Table: #__finder_links_termsf
--
CREATE TABLE "#__finder_links_termsf" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_termsf_idx_term_weight" on "#__finder_links_termsf" ("term_id", "weight");
CREATE INDEX "#__finder_links_termsf_idx_link_term_weight" on "#__finder_links_termsf" ("link_id", "term_id", "weight");

--
-- Table: #__finder_taxonomy
--
CREATE TABLE "#__finder_taxonomy" (
  "id" serial NOT NULL,
  "parent_id" integer DEFAULT 0 NOT NULL,
  "title" character varying(255) NOT NULL,
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
-- Dumping data for table #__finder_taxonomy
--
UPDATE "#__finder_taxonomy" SET ("id", "parent_id", "title", "state", "access", "ordering") = (1, 0, 'ROOT', 0, 0, 0) 
WHERE "id"=1;

INSERT INTO "#__finder_taxonomy" ("id", "parent_id", "title", "state", "access", "ordering") 
SELECT 1, 0, 'ROOT', 0, 0, 0 WHERE 1 NOT IN 
(SELECT 1 FROM "#__finder_taxonomy" WHERE "id"=1);



--
-- Table: #__finder_taxonomy_map
--
CREATE TABLE "#__finder_taxonomy_map" (
  "link_id" integer NOT NULL,
  "node_id" integer NOT NULL,
  PRIMARY KEY ("link_id", "node_id")
);
CREATE INDEX "#__finder_taxonomy_map_link_id" on "#__finder_taxonomy_map" ("link_id");
CREATE INDEX "#__finder_taxonomy_map_node_id" on "#__finder_taxonomy_map" ("node_id");

--
-- Table: #__finder_terms
--
CREATE TABLE "#__finder_terms" (
  "term_id" serial NOT NULL,
  "term" character varying(75) NOT NULL,
  "stem" character varying(75) NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  "soundex" character varying(75) NOT NULL,
  "links" integer DEFAULT 0 NOT NULL,
  PRIMARY KEY ("term_id"),
  CONSTRAINT "#__finder_terms_idx_term" UNIQUE ("term")
);
CREATE INDEX "#__finder_terms_idx_term_phrase" on "#__finder_terms" ("term", "phrase");
CREATE INDEX "#__finder_terms_idx_stem_phrase" on "#__finder_terms" ("stem", "phrase");
CREATE INDEX "#__finder_terms_idx_soundex_phrase" on "#__finder_terms" ("soundex", "phrase");

--
-- Table: #__finder_terms_common
--
CREATE TABLE "#__finder_terms_common" (
  "term" character varying(75) NOT NULL,
  "language" character varying(3) NOT NULL
);
CREATE INDEX "#__finder_terms_common_idx_word_lang" on "#__finder_terms_common" ("term", "language");
CREATE INDEX "#__finder_terms_common_idx_lang" on "#__finder_terms_common" ("language");


--
-- Dumping data for table `#__finder_terms_common`
--

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('a', 'en') WHERE "term"='a';

INSERT INTO "#__finder_terms_common" ("term", "language") 
SELECT 'a', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='a');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('about', 'en') WHERE "term"='about';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'about', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='about');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('after', 'en') WHERE "term"='after';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'after', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='after');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('ago', 'en') WHERE "term"='ago';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'ago', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='ago');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('all', 'en') WHERE "term"='all';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'all', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='all');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('am', 'en') WHERE "term"='am';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'am', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='am');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('an', 'en') WHERE "term"='an';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'an', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='an');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('and', 'en') WHERE "term"='and';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'and', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='and');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('any', 'en') WHERE "term"='any';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'any', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='any');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('are', 'en') WHERE "term"='are';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'are', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='are');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('aren''t', 'en') WHERE "term"='aren''t';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'aren''t', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='aren''t');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('as', 'en') WHERE "term"='as';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'as', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='as');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('at', 'en') WHERE "term"='at';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'at', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='at');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('be', 'en') WHERE "term"='be';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'be', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='be');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('but', 'en') WHERE "term"='but';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'but', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='but');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('by', 'en') WHERE "term"='by';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'by', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='by');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('for', 'en') WHERE "term"='for';

INSERT INTO "#__finder_terms_common" ("term", "language") SELECT 'for', 'en' WHERE 1 NOT IN 
(SELECT 1 FROM "#__finder_terms_common" WHERE "term"='for');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('from', 'en') WHERE "term"='from';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'from', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='from');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('get', 'en') WHERE "term"='get';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'get', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='get');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('go', 'en') WHERE "term"='go';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'go', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='go');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('how', 'en') WHERE "term"='how';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'how', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='how');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('if', 'en') WHERE "term"='if';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'if', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='if');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('in', 'en') WHERE "term"='in';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'in', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='in');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('into', 'en') WHERE "term"='into';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'into', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='into');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('is', 'en') WHERE "term"='is';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'is', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='is');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('isn''t', 'en') WHERE "term"='isn''t';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'isn''t', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='isn''t');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('it', 'en') WHERE "term"='it';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'it', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='it');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('its', 'en') WHERE "term"='its';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'its', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='its');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('me', 'en') WHERE "term"='me';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'me', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='me');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('more', 'en') WHERE "term"='more';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'more', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='more');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('most', 'en') WHERE "term"='most';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'most', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='most');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('must', 'en') WHERE "term"='must';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'must', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='must');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('my', 'en') WHERE "term"='my';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'my', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='my');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('new', 'en') WHERE "term"='new';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'new', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='new');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('no', 'en') WHERE "term"='no';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'no', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='no');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('none', 'en') WHERE "term"='none';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'none', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='none');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('not', 'en') WHERE "term"='not';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'not', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='not');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('nothing', 'en') WHERE "term"='nothing';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'nothing', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='nothing');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('of', 'en') WHERE "term"='of';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'of', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='of');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('off', 'en') WHERE "term"='off';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'off', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='off');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('often', 'en') WHERE "term"='often';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'often', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='often');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('old', 'en') WHERE "term"='old';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'old', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='old');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('on', 'en') WHERE "term"='on';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'on', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='on');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('onc', 'en') WHERE "term"='onc';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'onc', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='onc');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('once', 'en') WHERE "term"='once';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'once', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='once');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('only', 'en') WHERE "term"='only';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'only', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='only');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('or', 'en') WHERE "term"='or';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'or', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='or');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('other', 'en') WHERE "term"='other';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'other', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='other');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('our', 'en') WHERE "term"='our';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'our', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='our');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('ours', 'en') WHERE "term"='ours';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'ours', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='ours');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('out', 'en') WHERE "term"='out';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'out', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='out');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('over', 'en') WHERE "term"='over';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'over', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='over');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('page', 'en') WHERE "term"='page';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'page', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='page');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('she', 'en') WHERE "term"='she';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'she', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='she');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('should', 'en') WHERE "term"='should';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'should', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='should');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('small', 'en') WHERE "term"='small';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'small', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='small');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('so', 'en') WHERE "term"='so';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'so', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='so');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('some', 'en') WHERE "term"='some';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'some', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='some');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('than', 'en') WHERE "term"='than';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'than', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='than');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('thank', 'en') WHERE "term"='thank';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'thank', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='thank');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('that', 'en') WHERE "term"='that';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'that', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='that');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('the', 'en') WHERE "term"='the';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'the', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='the');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('their', 'en') WHERE "term"='their';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'their', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='their');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('theirs', 'en') WHERE "term"='theirs';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'theirs', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='theirs');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('them', 'en') WHERE "term"='them';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'them', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='them');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('then', 'en') WHERE "term"='then';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'then', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='then');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('there', 'en') WHERE "term"='there';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'there', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='there');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('these', 'en') WHERE "term"='these';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'these', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='these');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('they', 'en') WHERE "term"='they';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'they', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='they');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('this', 'en') WHERE "term"='this';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'this', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='this');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('those', 'en') WHERE "term"='those';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'those', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='those');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('thus', 'en') WHERE "term"='thus';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'thus', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='thus');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('time', 'en') WHERE "term"='time';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'time', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='time');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('times', 'en') WHERE "term"='times';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'times', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='times');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('to', 'en') WHERE "term"='to';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'to', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='to');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('too', 'en') WHERE "term"='too';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'too', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='too');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('true', 'en') WHERE "term"='true';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'true', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='true');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('under', 'en')WHERE "term"='under';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'under', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='under');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('until', 'en') WHERE "term"='until';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'until', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='until');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('up', 'en') WHERE "term"='up';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'up', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='up');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('upon', 'en') WHERE "term"='upon';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'upon', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='upon');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('use', 'en') WHERE "term"='use';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'use', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='use');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('user', 'en') WHERE "term"='user';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'user', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='user');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('users', 'en') WHERE "term"='users';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'users', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='users');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('version', 'en') WHERE "term"='version';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'version', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='version');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('very', 'en') WHERE "term"='very';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'very', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='very');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('via', 'en') WHERE "term"='via';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'via', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='via');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('want', 'en') WHERE "term"='want';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'want', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='want');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('was', 'en') WHERE "term"='was';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'was', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='was');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('way', 'en') WHERE "term"='way';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'way', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='way');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('were', 'en') WHERE "term"='were';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'were', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='were');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('what', 'en') WHERE "term"='what';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'what', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='what');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('when', 'en') WHERE "term"='when';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'when', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='when');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('where', 'en') WHERE "term"='where';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'where', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='where');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('whi', 'en') WHERE "term"='whi';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'whi', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='whi');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('which', 'en') WHERE "term"='which';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'which', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='which');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('who', 'en') WHERE "term"='who';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'who', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='who');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('whom', 'en') WHERE "term"='whom';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'whom', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='whom');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('whose', 'en') WHERE "term"='whose';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'whose', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='whose');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('why', 'en') WHERE "term"='why';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'why', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='why');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('wide', 'en') WHERE "term"='wide';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'wide', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='wide');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('will', 'en') WHERE "term"='will';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'will', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='will');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('with', 'en') WHERE "term"='with';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'with', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='with');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('within', 'en') WHERE "term"='within';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'within', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='within');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('without', 'en') WHERE "term"='without';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'without', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='without');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('would', 'en') WHERE "term"='would';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'would', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='would');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('yes', 'en') WHERE "term"='yes';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'yes', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='yes');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('yet', 'en') WHERE "term"='yet';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'yet', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='yet');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('you', 'en') WHERE "term"='you';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'you', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='you');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('your', 'en') WHERE "term"='your';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'your', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='your');

--
UPDATE "#__finder_terms_common" SET ("term", "language") = ('yours', 'en') WHERE "term"='yours';

INSERT INTO "#__finder_terms_common" ("term", "language")
SELECT 'yours', 'en' WHERE 1 NOT IN (SELECT 1 FROM "#__finder_terms_common" WHERE "term"='yours');



--
-- Table: #__finder_tokens
--
CREATE TABLE "#__finder_tokens" (
  "term" character varying(75) NOT NULL,
  "stem" character varying(75) NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" numeric(8,2) DEFAULT 1 NOT NULL,
  "context" smallint DEFAULT 2 NOT NULL
);
CREATE INDEX "#__finder_tokens_idx_word" on "#__finder_tokens" ("term");
CREATE INDEX "#__finder_tokens_idx_context" on "#__finder_tokens" ("context");

--
-- Table: #__finder_tokens_aggregate
--
CREATE TABLE "#__finder_tokens_aggregate" (
  "term_id" integer NOT NULL,
  "map_suffix" character(1) NOT NULL,
  "term" character varying(75) NOT NULL,
  "stem" character varying(75) NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "term_weight" numeric(8,2) NOT NULL,
  "context" smallint DEFAULT 2 NOT NULL,
  "context_weight" numeric(8,2) NOT NULL,
  "total_weight" numeric(8,2) NOT NULL
);
CREATE INDEX "#__finder_tokens_aggregate_token" on "#__finder_tokens_aggregate" ("term");
CREATE INDEX "_#__finder_tokens_aggregate_keyword_id" on "#__finder_tokens_aggregate" ("term_id");

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

