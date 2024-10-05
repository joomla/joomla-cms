--
-- Table structure for table `#__banners`
--

CREATE TABLE IF NOT EXISTS "#__banners" (
  "id" serial NOT NULL,
  "cid" bigint DEFAULT 0 NOT NULL,
  "type" bigint DEFAULT 0 NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "imptotal" bigint DEFAULT 0 NOT NULL,
  "impmade" bigint DEFAULT 0 NOT NULL,
  "clicks" bigint DEFAULT 0 NOT NULL,
  "clickurl" varchar(2048) DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "catid" bigint DEFAULT 0 NOT NULL,
  "description" text NOT NULL,
  "custombannercode" varchar(2048) NOT NULL,
  "sticky" smallint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "metakey" text,
  "params" text NOT NULL,
  "own_prefix" smallint DEFAULT 0 NOT NULL,
  "metakey_prefix" varchar(255) DEFAULT '' NOT NULL,
  "purchase_type" smallint DEFAULT -1 NOT NULL,
  "track_clicks" smallint DEFAULT -1 NOT NULL,
  "track_impressions" smallint DEFAULT -1 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "reset" timestamp without time zone,
  "created" timestamp without time zone NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__banners_idx_state" ON "#__banners" ("state");
CREATE INDEX "#__banners_idx_own_prefix" ON "#__banners" ("own_prefix");
CREATE INDEX "#__banners_idx_metakey_prefix" ON "#__banners" ("metakey_prefix");
CREATE INDEX "#__banners_idx_banner_catid" ON "#__banners" ("catid");
CREATE INDEX "#__banners_idx_language" ON "#__banners" ("language");

--
-- Table structure for table `#__banner_clients`
--

CREATE TABLE IF NOT EXISTS "#__banner_clients" (
  "id" serial NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "contact" varchar(255) DEFAULT '' NOT NULL,
  "email" varchar(255) DEFAULT '' NOT NULL,
  "extrainfo" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "metakey" text,
  "own_prefix" smallint DEFAULT 0 NOT NULL,
  "metakey_prefix" varchar(255) DEFAULT '' NOT NULL,
  "purchase_type" smallint DEFAULT -1 NOT NULL,
  "track_clicks" smallint DEFAULT -1 NOT NULL,
  "track_impressions" smallint DEFAULT -1 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__banner_clients_idx_own_prefix" ON "#__banner_clients" ("own_prefix");
CREATE INDEX "#__banner_clients_idx_metakey_prefix" ON "#__banner_clients" ("metakey_prefix");

--
-- Table structure for table `#__banner_tracks`
--

CREATE TABLE IF NOT EXISTS "#__banner_tracks" (
  "track_date" timestamp without time zone NOT NULL,
  "track_type" bigint NOT NULL,
  "banner_id" bigint NOT NULL,
  "count" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("track_date", "track_type", "banner_id")
);
CREATE INDEX "#__banner_tracks_idx_track_date" ON "#__banner_tracks" ("track_date");
CREATE INDEX "#__banner_tracks_idx_track_type" ON "#__banner_tracks" ("track_type");
CREATE INDEX "#__banner_tracks_idx_banner_id" ON "#__banner_tracks" ("banner_id");

--
-- Table structure for table `#__contact_details`
--

CREATE TABLE IF NOT EXISTS "#__contact_details" (
  "id" serial NOT NULL,
  "name" varchar(255) NOT NULL,
  "alias" varchar(255) NOT NULL,
  "con_position" varchar(255),
  "address" text,
  "suburb" varchar(100),
  "state" varchar(100),
  "country" varchar(100),
  "postcode" varchar(100),
  "telephone" varchar(255),
  "fax" varchar(255),
  "misc" text,
  "image" varchar(255),
  "email_to" varchar(255),
  "default_con" smallint NOT NULL DEFAULT 0,
  "published" smallint NOT NULL DEFAULT 0,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "ordering" bigint NOT NULL DEFAULT 0,
  "params" text NOT NULL,
  "user_id" bigint NOT NULL DEFAULT 0,
  "catid" bigint NOT NULL DEFAULT 0,
  "access" bigint NOT NULL DEFAULT 0,
  "mobile" varchar(255) NOT NULL DEFAULT '',
  "webpage" varchar(255) NOT NULL DEFAULT '',
  "sortname1" varchar(255) NOT NULL DEFAULT '',
  "sortname2" varchar(255) NOT NULL DEFAULT '',
  "sortname3" varchar(255) NOT NULL DEFAULT '',
  "language" varchar(7) NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" integer NOT NULL DEFAULT 0,
  "created_by_alias" varchar(255) NOT NULL DEFAULT '',
  "modified" timestamp without time zone NOT NULL,
  "modified_by" integer NOT NULL DEFAULT 0,
  "metakey" text,
  "metadesc" text NOT NULL,
  "metadata" text NOT NULL,
  "featured" smallint NOT NULL DEFAULT 0,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "version" bigint NOT NULL DEFAULT 1,
  "hits" bigint NOT NULL DEFAULT 0,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__contact_details_idx_access" ON "#__contact_details" ("access");
CREATE INDEX "#__contact_details_idx_checkout" ON "#__contact_details" ("checked_out");
CREATE INDEX "#__contact_details_idx_state" ON "#__contact_details" ("published");
CREATE INDEX "#__contact_details_idx_catid" ON "#__contact_details" ("catid");
CREATE INDEX "#__contact_details_idx_createdby" ON "#__contact_details" ("created_by");
CREATE INDEX "#__contact_details_idx_featured_catid" ON "#__contact_details" ("featured", "catid");
CREATE INDEX "#__contact_details_idx_language" ON "#__contact_details" ("language");

COMMENT ON COLUMN "#__contact_details"."featured" IS 'Set if contact is featured.';

--
-- Table structure for table `#__content`
--

CREATE TABLE IF NOT EXISTS "#__content" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "introtext" text NOT NULL,
  "fulltext" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "catid" bigint DEFAULT 0 NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "images" text NOT NULL,
  "urls" text NOT NULL,
  "attribs" varchar(5120) NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "metakey" text,
  "metadesc" text NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL,
  "metadata" text NOT NULL,
  "featured" smallint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__content_idx_access" ON "#__content" ("access");
CREATE INDEX "#__content_idx_checkout" ON "#__content" ("checked_out");
CREATE INDEX "#__content_idx_state" ON "#__content" ("state");
CREATE INDEX "#__content_idx_catid" ON "#__content" ("catid");
CREATE INDEX "#__content_idx_createdby" ON "#__content" ("created_by");
CREATE INDEX "#__content_idx_featured_catid" ON "#__content" ("featured", "catid");
CREATE INDEX "#__content_idx_language" ON "#__content" ("language");
CREATE INDEX "#__content_idx_alias" ON "#__content" ("alias");

COMMENT ON COLUMN "#__content"."asset_id" IS 'FK to the #__assets table.';
COMMENT ON COLUMN "#__content"."featured" IS 'Set if article is featured.';
COMMENT ON COLUMN "#__content"."language" IS 'The language code for the article.';


--
-- Table structure for table `#__content_frontpage`
--

CREATE TABLE IF NOT EXISTS "#__content_frontpage" (
  "content_id" bigint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "featured_up" timestamp without time zone,
  "featured_down" timestamp without time zone,
  PRIMARY KEY ("content_id")
);

--
-- Table structure for table `#__content_rating`
--

CREATE TABLE IF NOT EXISTS "#__content_rating" (
  "content_id" bigint DEFAULT 0 NOT NULL,
  "rating_sum" bigint DEFAULT 0 NOT NULL,
  "rating_count" bigint DEFAULT 0 NOT NULL,
  "lastip" varchar(50) DEFAULT '' NOT NULL,
  PRIMARY KEY ("content_id")
);

--
-- Table structure for table `#__finder_filters`
--

CREATE TABLE IF NOT EXISTS "#__finder_filters" (
  "filter_id" serial NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(255) NOT NULL,
  "state" smallint DEFAULT 1 NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" integer DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "map_count" integer DEFAULT 0 NOT NULL,
  "data" text,
  "params" text,
  PRIMARY KEY ("filter_id")
);

--
-- Table structure for table `#__finder_links`
--

CREATE TABLE IF NOT EXISTS "#__finder_links" (
  "link_id" serial NOT NULL,
  "url" varchar(255) NOT NULL,
  "route" varchar(400) NOT NULL,
  "title" varchar(400) DEFAULT NULL,
  "description" text,
  "indexdate" timestamp without time zone NOT NULL,
  "md5sum" varchar(32) DEFAULT NULL,
  "published" smallint DEFAULT 1 NOT NULL,
  "state" integer DEFAULT 1 NOT NULL,
  "access" integer DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "publish_start_date" timestamp without time zone,
  "publish_end_date" timestamp without time zone,
  "start_date" timestamp without time zone,
  "end_date" timestamp without time zone,
  "list_price" numeric(8,2) DEFAULT 0 NOT NULL,
  "sale_price" numeric(8,2) DEFAULT 0 NOT NULL,
  "type_id" bigint NOT NULL,
  "object" bytea,
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
-- Table structure for table `#__finder_links_terms`
--

CREATE TABLE IF NOT EXISTS "#__finder_links_terms" (
  "link_id" integer NOT NULL,
  "term_id" integer NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  PRIMARY KEY ("link_id", "term_id")
);
CREATE INDEX "#__finder_links_terms_idx_term_weight" on "#__finder_links_terms" ("term_id", "weight");
CREATE INDEX "#__finder_links_terms_idx_link_term_weight" on "#__finder_links_terms" ("link_id", "term_id", "weight");

--
-- Table structure for table `#__finder_logging`
--

CREATE TABLE IF NOT EXISTS "#__finder_logging" (
  "searchterm" character varying(255) NOT NULL DEFAULT '',
  "md5sum" character varying(32) NOT NULL DEFAULT '',
  "query" bytea NOT NULL,
  "hits" integer NOT NULL DEFAULT 1,
  "results" integer NOT NULL DEFAULT 0,
  PRIMARY KEY ("md5sum")
);
CREATE INDEX "#__finder_logging_idx_md5sum" on "#__finder_logging" ("md5sum");
CREATE INDEX "#__finder_logging_idx_searchterm" on "#__finder_logging" ("searchterm");

--
-- Table structure for table `#__finder_taxonomy`
--

CREATE TABLE IF NOT EXISTS "#__finder_taxonomy" (
  "id" serial NOT NULL,
  "parent_id" integer DEFAULT 0 NOT NULL,
  "lft" integer DEFAULT 0 NOT NULL,
  "rgt" integer DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" VARCHAR(400) NOT NULL DEFAULT '',
  "title" VARCHAR(255) NOT NULL DEFAULT '',
  "alias" VARCHAR(400) NOT NULL DEFAULT '',
  "state" smallint DEFAULT 1 NOT NULL,
  "access" smallint DEFAULT 1 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__finder_taxonomy_state" on "#__finder_taxonomy" ("state");
CREATE INDEX "#__finder_taxonomy_access" on "#__finder_taxonomy" ("access");
CREATE INDEX "#__finder_taxonomy_path" on "#__finder_taxonomy" ("path");
CREATE INDEX "#__finder_taxonomy_level" on "#__finder_taxonomy" ("level");
CREATE INDEX "#__finder_taxonomy_lft_rgt" on "#__finder_taxonomy" ("lft", "rgt");
CREATE INDEX "#__finder_taxonomy_alias" on "#__finder_taxonomy" ("alias");
CREATE INDEX "#__finder_taxonomy_language" on "#__finder_taxonomy" ("language");
CREATE INDEX "#__finder_taxonomy_idx_parent_published" on "#__finder_taxonomy" ("parent_id", "state", "access");

--
-- Dumping data for table `#__finder_taxonomy`
--

INSERT INTO "#__finder_taxonomy" ("id", "parent_id", "lft", "rgt", "level", "path", "title", "alias", "state", "access", "language") VALUES
(1, 0, 0, 1, 0, '', 'ROOT', 'root', 1, 1, '*');

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
  "term" varchar(75) NOT NULL,
  "stem" varchar(75) DEFAULT '' NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" numeric(8,2) DEFAULT 0 NOT NULL,
  "soundex" varchar(75) DEFAULT '' NOT NULL,
  "links" integer DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  PRIMARY KEY ("term_id"),
  CONSTRAINT "#__finder_terms_idx_term_language" UNIQUE ("term", "language")
);
CREATE INDEX "#__finder_terms_idx_term_phrase" on "#__finder_terms" ("term", "phrase");
CREATE INDEX "#__finder_terms_idx_stem_phrase" on "#__finder_terms" ("stem", "phrase");
CREATE INDEX "#__finder_terms_idx_soundex_phrase" on "#__finder_terms" ("soundex", "phrase");
CREATE INDEX "#__finder_terms_idx_language" on "#__finder_terms" ("language");

--
-- Table structure for table `#__finder_terms_common`
--

CREATE TABLE IF NOT EXISTS "#__finder_terms_common" (
  "term" varchar(75) NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "custom" integer DEFAULT 0 NOT NULL,
  CONSTRAINT "#__finder_terms_common_idx_term_language" UNIQUE ("term", "language")
);
CREATE INDEX "#__finder_terms_common_idx_lang" on "#__finder_terms_common" ("language");

--
-- Dumping data for table `#__finder_terms_common`
--

INSERT INTO "#__finder_terms_common" ("term", "language", "custom") VALUES
('i', 'en', 0),
('me', 'en', 0),
('my', 'en', 0),
('myself', 'en', 0),
('we', 'en', 0),
('our', 'en', 0),
('ours', 'en', 0),
('ourselves', 'en', 0),
('you', 'en', 0),
('your', 'en', 0),
('yours', 'en', 0),
('yourself', 'en', 0),
('yourselves', 'en', 0),
('he', 'en', 0),
('him', 'en', 0),
('his', 'en', 0),
('himself', 'en', 0),
('she', 'en', 0),
('her', 'en', 0),
('hers', 'en', 0),
('herself', 'en', 0),
('it', 'en', 0),
('its', 'en', 0),
('itself', 'en', 0),
('they', 'en', 0),
('them', 'en', 0),
('their', 'en', 0),
('theirs', 'en', 0),
('themselves', 'en', 0),
('what', 'en', 0),
('which', 'en', 0),
('who', 'en', 0),
('whom', 'en', 0),
('this', 'en', 0),
('that', 'en', 0),
('these', 'en', 0),
('those', 'en', 0),
('am', 'en', 0),
('is', 'en', 0),
('are', 'en', 0),
('was', 'en', 0),
('were', 'en', 0),
('be', 'en', 0),
('been', 'en', 0),
('being', 'en', 0),
('have', 'en', 0),
('has', 'en', 0),
('had', 'en', 0),
('having', 'en', 0),
('do', 'en', 0),
('does', 'en', 0),
('did', 'en', 0),
('doing', 'en', 0),
('would', 'en', 0),
('should', 'en', 0),
('could', 'en', 0),
('ought', 'en', 0),
('i''m', 'en', 0),
('you''re', 'en', 0),
('he''s', 'en', 0),
('she''s', 'en', 0),
('it''s', 'en', 0),
('we''re', 'en', 0),
('they''re', 'en', 0),
('i''ve', 'en', 0),
('you''ve', 'en', 0),
('we''ve', 'en', 0),
('they''ve', 'en', 0),
('i''d', 'en', 0),
('you''d', 'en', 0),
('he''d', 'en', 0),
('she''d', 'en', 0),
('we''d', 'en', 0),
('they''d', 'en', 0),
('i''ll', 'en', 0),
('you''ll', 'en', 0),
('he''ll', 'en', 0),
('she''ll', 'en', 0),
('we''ll', 'en', 0),
('they''ll', 'en', 0),
('isn''t', 'en', 0),
('aren''t', 'en', 0),
('wasn''t', 'en', 0),
('weren''t', 'en', 0),
('hasn''t', 'en', 0),
('haven''t', 'en', 0),
('hadn''t', 'en', 0),
('doesn''t', 'en', 0),
('don''t', 'en', 0),
('didn''t', 'en', 0),
('won''t', 'en', 0),
('wouldn''t', 'en', 0),
('shan''t', 'en', 0),
('shouldn''t', 'en', 0),
('can''t', 'en', 0),
('cannot', 'en', 0),
('couldn''t', 'en', 0),
('mustn''t', 'en', 0),
('let''s', 'en', 0),
('that''s', 'en', 0),
('who''s', 'en', 0),
('what''s', 'en', 0),
('here''s', 'en', 0),
('there''s', 'en', 0),
('when''s', 'en', 0),
('where''s', 'en', 0),
('why''s', 'en', 0),
('how''s', 'en', 0),
('a', 'en', 0),
('an', 'en', 0),
('the', 'en', 0),
('and', 'en', 0),
('but', 'en', 0),
('if', 'en', 0),
('or', 'en', 0),
('because', 'en', 0),
('as', 'en', 0),
('until', 'en', 0),
('while', 'en', 0),
('of', 'en', 0),
('at', 'en', 0),
('by', 'en', 0),
('for', 'en', 0),
('with', 'en', 0),
('about', 'en', 0),
('against', 'en', 0),
('between', 'en', 0),
('into', 'en', 0),
('through', 'en', 0),
('during', 'en', 0),
('before', 'en', 0),
('after', 'en', 0),
('above', 'en', 0),
('below', 'en', 0),
('to', 'en', 0),
('from', 'en', 0),
('up', 'en', 0),
('down', 'en', 0),
('in', 'en', 0),
('out', 'en', 0),
('on', 'en', 0),
('off', 'en', 0),
('over', 'en', 0),
('under', 'en', 0),
('again', 'en', 0),
('further', 'en', 0),
('then', 'en', 0),
('once', 'en', 0),
('here', 'en', 0),
('there', 'en', 0),
('when', 'en', 0),
('where', 'en', 0),
('why', 'en', 0),
('how', 'en', 0),
('all', 'en', 0),
('any', 'en', 0),
('both', 'en', 0),
('each', 'en', 0),
('few', 'en', 0),
('more', 'en', 0),
('most', 'en', 0),
('other', 'en', 0),
('some', 'en', 0),
('such', 'en', 0),
('no', 'en', 0),
('nor', 'en', 0),
('not', 'en', 0),
('only', 'en', 0),
('own', 'en', 0),
('same', 'en', 0),
('so', 'en', 0),
('than', 'en', 0),
('too', 'en', 0),
('very', 'en', 0);

--
-- Table structure for table `#__finder_tokens`
--

CREATE TABLE IF NOT EXISTS "#__finder_tokens" (
  "term" varchar(75) NOT NULL,
  "stem" varchar(75) DEFAULT '' NOT NULL,
  "common" smallint DEFAULT 0 NOT NULL,
  "phrase" smallint DEFAULT 0 NOT NULL,
  "weight" numeric(8,2) DEFAULT 1 NOT NULL,
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
  "term_id" integer NOT NULL,
  "term" varchar(75) NOT NULL,
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
  "title" varchar(100) NOT NULL,
  "mime" varchar(100) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__finder_types_title" UNIQUE ("title")
);

--
-- Table structure for table `#__messages`
--

CREATE TABLE IF NOT EXISTS "#__messages" (
  "message_id" serial NOT NULL,
  "user_id_from" bigint DEFAULT 0 NOT NULL,
  "user_id_to" bigint DEFAULT 0 NOT NULL,
  "folder_id" smallint DEFAULT 0 NOT NULL,
  "date_time" timestamp without time zone NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "priority" smallint DEFAULT 0 NOT NULL,
  "subject" varchar(255) DEFAULT '' NOT NULL,
  "message" text NOT NULL,
  PRIMARY KEY ("message_id")
);
CREATE INDEX "#__messages_useridto_state" ON "#__messages" ("user_id_to", "state");

--
-- Table structure for table `#__messages_cfg`
--

CREATE TABLE IF NOT EXISTS "#__messages_cfg" (
  "user_id" bigint DEFAULT 0 NOT NULL,
  "cfg_name" varchar(100) DEFAULT '' NOT NULL,
  "cfg_value" varchar(255) DEFAULT '' NOT NULL,
  CONSTRAINT "#__messages_cfg_idx_user_var_name" UNIQUE ("user_id", "cfg_name")
);

--
-- Table structure for table `#__newsfeeds`
--

CREATE TABLE IF NOT EXISTS "#__newsfeeds" (
  "catid" bigint DEFAULT 0 NOT NULL,
  "id" serial NOT NULL,
  "name" varchar(100) DEFAULT '' NOT NULL,
  "alias" varchar(100) DEFAULT '' NOT NULL,
  "link" varchar(2048) DEFAULT '' NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "numarticles" bigint DEFAULT 1 NOT NULL,
  "cache_time" bigint DEFAULT 3600 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "rtl" smallint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "params" text NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" integer DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" integer DEFAULT 0 NOT NULL,
  "metakey" text,
  "metadesc" text NOT NULL,
  "metadata" text NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "description" text NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL,
  "images" text NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__newsfeeds_idx_access" ON "#__newsfeeds" ("access");
CREATE INDEX "#__newsfeeds_idx_checkout" ON "#__newsfeeds" ("checked_out");
CREATE INDEX "#__newsfeeds_idx_state" ON "#__newsfeeds" ("published");
CREATE INDEX "#__newsfeeds_idx_catid" ON "#__newsfeeds" ("catid");
CREATE INDEX "#__newsfeeds_idx_createdby" ON "#__newsfeeds" ("created_by");
CREATE INDEX "#__newsfeeds_idx_language" ON "#__newsfeeds" ("language");

--
-- Table structure for table `#__privacy_requests`
--

CREATE TABLE "#__privacy_requests" (
  "id" serial NOT NULL,
  "email" varchar(100) DEFAULT '' NOT NULL,
  "requested_at" timestamp without time zone NOT NULL,
  "status" smallint DEFAULT 0 NOT NULL,
  "request_type" varchar(25) DEFAULT '' NOT NULL,
  "confirm_token" varchar(100) DEFAULT '' NOT NULL,
  "confirm_token_created_at" timestamp without time zone,
  PRIMARY KEY ("id")
);

--
-- Table structure for table `#__privacy_consents`
--

CREATE TABLE "#__privacy_consents" (
  "id" serial NOT NULL,
  "user_id" bigint DEFAULT 0 NOT NULL,
  "state" smallint DEFAULT 1 NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "subject" varchar(255) DEFAULT '' NOT NULL,
  "body" text NOT NULL,
  "remind" smallint DEFAULT 0 NOT NULL,
  "token" varchar(100) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__privacy_consents_idx_user_id" ON "#__privacy_consents" ("user_id");

--
-- Table structure for table `#__redirect_links`
--

CREATE TABLE IF NOT EXISTS "#__redirect_links" (
  "id" serial NOT NULL,
  "old_url" varchar(2048) NOT NULL,
  "new_url" varchar(2048),
  "referer" varchar(2048) NOT NULL,
  "comment" varchar(255) DEFAULT '' NOT NULL,
  "hits" bigint DEFAULT 0 NOT NULL,
  "published" smallint NOT NULL,
  "created_date" timestamp without time zone NOT NULL,
  "modified_date" timestamp without time zone NOT NULL,
  "header" integer DEFAULT 301 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__redirect_links_idx_old_url" ON "#__redirect_links" ("old_url");
CREATE INDEX "#__redirect_links_idx_link_modified" ON "#__redirect_links" ("modified_date");

--
-- Table: #__action_logs
--
CREATE TABLE "#__action_logs" (
  "id" serial NOT NULL,
  "message_language_key" varchar(255) NOT NULL DEFAULT '',
  "message" text NOT NULL,
  "log_date" timestamp without time zone NOT NULL,
  "extension" varchar(50) NOT NULL DEFAULT '',
  "user_id" integer DEFAULT 0 NOT NULL,
  "item_id" integer DEFAULT 0 NOT NULL,
  "ip_address" varchar(40) NOT NULL DEFAULT '0.0.0.0',
  PRIMARY KEY ("id")
);

CREATE INDEX "#__action_logs_idx_user_id" ON "#__action_logs" ("user_id");
CREATE INDEX "#__action_logs_idx_user_id_logdate" ON "#__action_logs" ("user_id", "log_date");
CREATE INDEX "#__action_logs_idx_user_id_extension" ON "#__action_logs" ("user_id", "extension");
CREATE INDEX "#__action_logs_idx_extension_itemid" ON "#__action_logs" ("extension", "item_id");

--
-- Table: #__action_logs_extensions
--
CREATE TABLE "#__action_logs_extensions" (
  "id" serial NOT NULL,
  "extension" varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY ("id")
);

--
-- Dumping data for table `#__action_logs_extensions`
--
INSERT INTO "#__action_logs_extensions" ("id", "extension") VALUES
(1, 'com_banners'),
(2, 'com_cache'),
(3, 'com_categories'),
(4, 'com_config'),
(5, 'com_contact'),
(6, 'com_content'),
(7, 'com_installer'),
(8, 'com_media'),
(9, 'com_menus'),
(10, 'com_messages'),
(11, 'com_modules'),
(12, 'com_newsfeeds'),
(13, 'com_plugins'),
(14, 'com_redirect'),
(15, 'com_tags'),
(16, 'com_templates'),
(17, 'com_users'),
(18, 'com_checkin'),
(19, 'com_scheduler'),
(20, 'com_fields'),
(21, 'com_guidedtours');

SELECT setval('#__action_logs_extensions_id_seq', 22, false);
-- --------------------------------------------------------

--
-- Table: #__action_log_config
--
CREATE TABLE "#__action_log_config" (
  "id" serial NOT NULL,
  "type_title" varchar(255) NOT NULL DEFAULT '',
  "type_alias" varchar(255) NOT NULL DEFAULT '',
  "id_holder" varchar(255) NULL,
  "title_holder" varchar(255) NULL,
  "table_name" varchar(255) NULL,
  "text_prefix" varchar(255) NULL,
  PRIMARY KEY ("id")
);

--
-- Dumping data for table `#__action_log_config`
--
INSERT INTO "#__action_log_config" ("id", "type_title", "type_alias", "id_holder", "title_holder", "table_name", "text_prefix") VALUES
(1, 'article', 'com_content.article', 'id' ,'title' , '#__content', 'PLG_ACTIONLOG_JOOMLA'),
(2, 'article', 'com_content.form', 'id', 'title' , '#__content', 'PLG_ACTIONLOG_JOOMLA'),
(3, 'banner', 'com_banners.banner', 'id' ,'name' , '#__banners', 'PLG_ACTIONLOG_JOOMLA'),
(4, 'user_note', 'com_users.note', 'id', 'subject' ,'#__user_notes', 'PLG_ACTIONLOG_JOOMLA'),
(5, 'media', 'com_media.file', '' , 'name' , '',  'PLG_ACTIONLOG_JOOMLA'),
(6, 'category', 'com_categories.category', 'id' , 'title' , '#__categories', 'PLG_ACTIONLOG_JOOMLA'),
(7, 'menu', 'com_menus.menu', 'id' ,'title' , '#__menu_types', 'PLG_ACTIONLOG_JOOMLA'),
(8, 'menu_item', 'com_menus.item', 'id' , 'title' , '#__menu', 'PLG_ACTIONLOG_JOOMLA'),
(9, 'newsfeed', 'com_newsfeeds.newsfeed', 'id' ,'name' , '#__newsfeeds', 'PLG_ACTIONLOG_JOOMLA'),
(10, 'link', 'com_redirect.link', 'id', 'old_url' , '#__redirect_links', 'PLG_ACTIONLOG_JOOMLA'),
(11, 'tag', 'com_tags.tag', 'id', 'title' , '#__tags', 'PLG_ACTIONLOG_JOOMLA'),
(12, 'style', 'com_templates.style', 'id' , 'title' , '#__template_styles', 'PLG_ACTIONLOG_JOOMLA'),
(13, 'plugin', 'com_plugins.plugin', 'extension_id' , 'name' , '#__extensions', 'PLG_ACTIONLOG_JOOMLA'),
(14, 'component_config', 'com_config.component', 'extension_id' , 'name', '', 'PLG_ACTIONLOG_JOOMLA'),
(15, 'contact', 'com_contact.contact', 'id', 'name', '#__contact_details', 'PLG_ACTIONLOG_JOOMLA'),
(16, 'module', 'com_modules.module', 'id' ,'title', '#__modules', 'PLG_ACTIONLOG_JOOMLA'),
(17, 'access_level', 'com_users.level', 'id' , 'title', '#__viewlevels', 'PLG_ACTIONLOG_JOOMLA'),
(18, 'banner_client', 'com_banners.client', 'id', 'name', '#__banner_clients', 'PLG_ACTIONLOG_JOOMLA'),
(19, 'application_config', 'com_config.application', '', 'name', '', 'PLG_ACTIONLOG_JOOMLA'),
(20, 'task', 'com_scheduler.task', 'id', 'title', '#__scheduler_tasks', 'PLG_ACTIONLOG_JOOMLA'),
(21, 'field', 'com_fields.field', 'id', 'title', '#__fields', 'PLG_ACTIONLOG_JOOMLA'),
(22, 'guidedtour', 'com_guidedtours.state', 'id', 'title', '#__guidedtours', 'PLG_ACTIONLOG_JOOMLA');


SELECT setval('#__action_log_config_id_seq', 23, false);

--
-- Table structure for table `#__action_logs_users`
--

CREATE TABLE "#__action_logs_users" (
  "user_id" integer NOT NULL,
  "notify" integer NOT NULL,
  "extensions" text NOT NULL,
  PRIMARY KEY ("user_id")
);

CREATE INDEX "#__action_logs_users_idx_notify" ON "#__action_logs_users" ("notify");

-- --------------------------------------------------------

--
-- Table structure for table "#__scheduler_tasks"
--

CREATE TABLE IF NOT EXISTS "#__scheduler_tasks" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) NOT NULL,
  "type" varchar(128) NOT NULL,
  "execution_rules" text,
  "cron_rules" text,
  "state" smallint DEFAULT 0 NOT NULL,
  "last_exit_code" integer DEFAULT 0 NOT NULL,
  "last_execution" timestamp without time zone,
  "next_execution" timestamp without time zone,
  "times_executed" integer DEFAULT 0 NOT NULL,
  "times_failed" integer DEFAULT 0,
  "locked" timestamp without time zone,
  "priority" smallint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "cli_exclusive" smallint DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "note" text,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__scheduler_tasks_idx_type" ON "#__scheduler_tasks" ("type");
CREATE INDEX "#__scheduler_tasks_idx_state" ON "#__scheduler_tasks" ("state");
CREATE INDEX "#__scheduler_tasks_idx_last_exit" ON "#__scheduler_tasks" ("last_exit_code");
CREATE INDEX "#__scheduler_tasks_idx_next_exec" ON "#__scheduler_tasks" ("next_execution");
CREATE INDEX "#__scheduler_tasks_idx_locked" ON "#__scheduler_tasks" ("locked");
CREATE INDEX "#__scheduler_tasks_idx_priority" ON "#__scheduler_tasks" ("priority");
CREATE INDEX "#__scheduler_tasks_idx_cli_exclusive" ON "#__scheduler_tasks" ("cli_exclusive");
CREATE INDEX "#__scheduler_tasks_idx_checked_out" ON "#__scheduler_tasks" ("checked_out");

INSERT INTO "#__scheduler_tasks" ("id", "asset_id", "title", "type", "execution_rules", "cron_rules", "state", "last_execution", "next_execution", "locked", "params", "created", "created_by") VALUES
(1, 97, 'Rotate Logs', 'rotation.logs', CONCAT('{"rule-type":"interval-days","interval-days":"30","exec-day":"', TO_CHAR(CURRENT_TIMESTAMP AT TIME ZONE 'UTC', 'FMDD'), '","exec-time":"', TO_CHAR(CURRENT_TIMESTAMP AT TIME ZONE 'UTC', 'HH24:00'), '"}'), '{"type":"interval","exp":"P30D"}', 1, NULL, TO_TIMESTAMP(TO_CHAR(CURRENT_TIMESTAMP AT TIME ZONE 'UTC' + INTERVAL '30 days', 'YYYY-MM-DD HH24:00:00'), 'YYYY-MM-DD HH24:MI:SS'), NULL, '{"individual_log":false,"log_file":"","notifications":{"success_mail":"0","failure_mail":"1","fatal_failure_mail":"1","orphan_mail":"1"},"logstokeep":1}', CURRENT_TIMESTAMP AT TIME ZONE 'UTC', 42),
(2, 98, 'Session GC', 'session.gc', CONCAT('{"rule-type":"interval-hours","interval-hours":"24","exec-day":"01","exec-time":"', TO_CHAR(CURRENT_TIMESTAMP AT TIME ZONE 'UTC', 'HH24:00'), '"}'), '{"type":"interval","exp":"PT24H"}', 1, NULL, TO_TIMESTAMP(TO_CHAR(CURRENT_TIMESTAMP AT TIME ZONE 'UTC' + INTERVAL '24 hours', 'YYYY-MM-DD HH24:00:00'), 'YYYY-MM-DD HH24:MI:SS'), NULL, '{"individual_log":false,"log_file":"","notifications":{"success_mail":"0","failure_mail":"1","fatal_failure_mail":"1","orphan_mail":"1"},"enable_session_gc":1,"enable_session_metadata_gc":1}', CURRENT_TIMESTAMP AT TIME ZONE 'UTC', 42),
(3, 99, 'Update Notification', 'update.notification', CONCAT('{"rule-type":"interval-hours","interval-hours":"24","exec-day":"01","exec-time":"', TO_CHAR(CURRENT_TIMESTAMP AT TIME ZONE 'UTC', 'HH24:00'), '"}'), '{"type":"interval","exp":"PT24H"}', 1, NULL, TO_TIMESTAMP(TO_CHAR(CURRENT_TIMESTAMP AT TIME ZONE 'UTC' + INTERVAL '24 hours', 'YYYY-MM-DD HH24:00:00'), 'YYYY-MM-DD HH24:MI:SS'), NULL, '{"individual_log":false,"log_file":"","notifications":{"success_mail":"0","failure_mail":"1","fatal_failure_mail":"1","orphan_mail":"1"},"email":"","language_override":""}', CURRENT_TIMESTAMP AT TIME ZONE 'UTC', 42);

SELECT setval('#__scheduler_tasks_id_seq', 4, false);

-- --------------------------------------------------------

--
-- Table structure for table "#__schemaorg"
--

CREATE TABLE IF NOT EXISTS "#__schemaorg" (
  "id" serial NOT NULL,
  "itemId" bigint,
  "context" varchar(100),
  "schemaType" varchar(100),
  "schema" text,
  PRIMARY KEY ("id")
);

-- --------------------------------------------------------

--
-- Table structure for table `#__guidedtours`
--

CREATE TABLE IF NOT EXISTS "#__guidedtours" (
  "id" serial NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "uid" varchar(255) DEFAULT '' NOT NULL,
  "description" text NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "extensions" text NOT NULL,
  "url" varchar(255) NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone,
  "checked_out" integer,
  "published" smallint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "access" bigint NOT NULL DEFAULT 0,
  "autostart" int NOT NULL DEFAULT 0,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__guidedtours_idx_access" ON "#__guidedtours" ("access");
CREATE INDEX "#__guidedtours_idx_state" ON "#__guidedtours" ("published");
CREATE INDEX "#__guidedtours_idx_language" ON "#__guidedtours" ("language");
CREATE INDEX "#__guidedtours_idx_uid" ON "#__guidedtours" ("uid");
--
-- Dumping data for table `#__guidedtours`
--

INSERT INTO "#__guidedtours" ("id", "title", "uid", "description", "ordering", "extensions", "url", "created", "created_by", "modified", "modified_by", "checked_out_time", "checked_out", "published", "language", "access", "autostart") VALUES
(1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_TITLE', 'joomla-guidedtours', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_DESCRIPTION', 1, '["com_guidedtours"]', 'administrator/index.php?option=com_guidedtours&view=tours', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_TITLE', 'joomla-guidedtoursteps', 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_DESCRIPTION', 2, '["com_guidedtours"]', 'administrator/index.php?option=com_guidedtours&view=tours', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_TITLE', 'joomla-articles', 'COM_GUIDEDTOURS_TOUR_ARTICLES_DESCRIPTION', 3, '["com_content","com_categories"]', 'administrator/index.php?option=com_content&view=articles', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_TITLE', 'joomla-categories', 'COM_GUIDEDTOURS_TOUR_CATEGORIES_DESCRIPTION', 4, '["com_content","com_categories"]', 'administrator/index.php?option=com_categories&view=categories&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(5, 'COM_GUIDEDTOURS_TOUR_MENUS_TITLE', 'joomla-menus', 'COM_GUIDEDTOURS_TOUR_MENUS_DESCRIPTION', 5, '["com_menus"]', 'administrator/index.php?option=com_menus&view=menus', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(6, 'COM_GUIDEDTOURS_TOUR_TAGS_TITLE', 'joomla-tags', 'COM_GUIDEDTOURS_TOUR_TAGS_DESCRIPTION', 6, '["com_tags"]', 'administrator/index.php?option=com_tags&view=tags', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(7, 'COM_GUIDEDTOURS_TOUR_BANNERS_TITLE', 'joomla-banners', 'COM_GUIDEDTOURS_TOUR_BANNERS_DESCRIPTION', 7, '["com_banners"]', 'administrator/index.php?option=com_banners&view=banners', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_TITLE', 'joomla-contacts', 'COM_GUIDEDTOURS_TOUR_CONTACTS_DESCRIPTION', 8, '["com_contact"]', 'administrator/index.php?option=com_contact&view=contacts', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_TITLE', 'joomla-newsfeeds', 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_DESCRIPTION', 9, '["com_newsfeeds"]', 'administrator/index.php?option=com_newsfeeds&view=newsfeeds', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(10, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_TITLE', 'joomla-smartsearch', 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_DESCRIPTION', 10, '["com_finder"]', 'administrator/index.php?option=com_finder&view=filters', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(11, 'COM_GUIDEDTOURS_TOUR_USERS_TITLE', 'joomla-users', 'COM_GUIDEDTOURS_TOUR_USERS_DESCRIPTION', 11, '["com_users"]', 'administrator/index.php?option=com_users&view=users', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 0),
(12, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_TITLE', 'joomla-welcome', 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_DESCRIPTION', 12, '["com_cpanel"]', 'administrator/index.php', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, NULL, NULL, 1, '*', 1, 1);

SELECT setval('#__guidedtours_id_seq', 13, false);

-- --------------------------------------------------------

--
-- Table structure for table `#__guidedtour_steps`
--

CREATE TABLE IF NOT EXISTS "#__guidedtour_steps" (
  "id" serial NOT NULL,
  "tour_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "description" text NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "position" varchar(255) NOT NULL,
  "target" varchar(255) NOT NULL,
  "type" bigint NOT NULL,
  "interactive_type" bigint DEFAULT 1 NOT NULL,
  "url" varchar(255) NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone,
  "checked_out" integer,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "params" text,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__guidedtour_steps_idx_tour_id" ON "#__guidedtour_steps" ("tour_id");
CREATE INDEX "#__guidedtour_steps_idx_state" ON "#__guidedtour_steps" ("published");
CREATE INDEX "#__guidedtour_steps_idx_language" ON "#__guidedtour_steps" ("language");

--
-- Dumping data for table `#__guidedtour_steps`
--

INSERT INTO "#__guidedtour_steps" ("id", "tour_id", "title", "published", "description", "ordering", "position", "target", "type", "interactive_type", "url", "created", "created_by", "modified", "modified_by", "language") VALUES
(1, 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_NEW_DESCRIPTION', 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_guidedtours&view=tours', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(2, 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_TITLE_DESCRIPTION', 2, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(3, 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_URL_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_URL_DESCRIPTION', 3, 'top', '#jform_url', 2, 2, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(4, 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONTENT_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONTENT_DESCRIPTION', 4, 'bottom', '#jform_description,#jform_description_ifr', 2, 3, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(5, 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_COMPONENT_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_COMPONENT_DESCRIPTION', 5, 'top', 'joomla-field-fancy-select .choices', 2, 3, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(6, 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_AUTOSTART_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_AUTOSTART_DESCRIPTION', 6, 'bottom', '#jform_autostart0', 2, 3, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(7, 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_SAVECLOSE_DESCRIPTION', 7, 'top', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(8, 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURS_STEP_CONGRATULATIONS_DESCRIPTION', 8, 'bottom', '', 0, 1, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(9, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_COUNTER_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_COUNTER_DESCRIPTION', 9, 'top', '#toursList tbody tr:nth-last-of-type(1) td:nth-of-type(5) .btn', 2, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(10, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_NEW_DESCRIPTION', 10, 'bottom', '.button-new', 2, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(11, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_TITLE_DESCRIPTION', 11, 'bottom', '#jform_title', 2, 2, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(12, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_DESCRIPTION_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_DESCRIPTION_DESCRIPTION', 12, 'bottom', '#jform_description,#jform_description_ifr', 2, 3, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(13, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_STATUS_DESCRIPTION', 13, 'bottom', '#jform_published', 2, 3, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(14, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_POSITION_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_POSITION_DESCRIPTION', 14, 'top', '#jform_position', 2, 3, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(15, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_TARGET_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_TARGET_DESCRIPTION', 15, 'top', '#jform_target', 2, 3, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(16, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_TYPE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_TYPE_DESCRIPTION', 16, 'top', '#jform_type', 2, 3, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(17, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_SAVECLOSE_DESCRIPTION', 17, 'bottom', '#save-group-children-save .button-save', 2, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(18, 2, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_GUIDEDTOURSTEPS_STEP_CONGRATULATIONS_DESCRIPTION', 18, 'bottom', '', 0, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(19, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_NEW_DESCRIPTION', 19, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_content&view=articles', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(20, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_TITLE_DESCRIPTION', 20, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(21, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_ALIAS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_ALIAS_DESCRIPTION', 21, 'bottom', '#jform_alias', 2, 2, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(22, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_CONTENT_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_CONTENT_DESCRIPTION', 22, 'bottom', '#jform_articletext,#jform_articletext_ifr', 2, 3, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(23, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_STATUS_DESCRIPTION', 23, 'bottom', '#jform_state', 2, 3, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(24, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_CATEGORY_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_CATEGORY_DESCRIPTION', 24, 'top', 'joomla-field-fancy-select .choices[data-type=select-one]', 2, 3, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(25, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_FEATURED_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_FEATURED_DESCRIPTION', 25, 'bottom', '#jform_featured0', 2, 3, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(26, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_ACCESS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_ACCESS_DESCRIPTION', 26, 'bottom', '#jform_access', 2, 3, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(27, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_TAGS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_TAGS_DESCRIPTION', 27, 'top', 'joomla-field-fancy-select .choices[data-type=select-multiple]', 2, 3, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(28, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_NOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_NOTE_DESCRIPTION', 28, 'top', '#jform_note', 2, 2, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(29, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_VERSIONNOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_VERSIONNOTE_DESCRIPTION', 29, 'top', '#jform_version_note', 2, 2, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(30, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_SAVECLOSE_DESCRIPTION', 30, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(31, 3, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_ARTICLES_STEP_CONGRATULATIONS_DESCRIPTION', 31, 'bottom', '', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(32, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_NEW_DESCRIPTION', 32, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_categories&view=categories&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(33, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_TITLE_DESCRIPTION', 33, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(34, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_ALIAS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_ALIAS_DESCRIPTION', 34, 'bottom', '#jform_alias', 2, 2, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(35, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_CONTENT_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_CONTENT_DESCRIPTION', 35, 'bottom', '#jform_description,#jform_description_ifr', 2, 3, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(36, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_PARENT_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_PARENT_DESCRIPTION', 36, 'top', 'joomla-field-fancy-select .choices[data-type=select-one]', 2, 3, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(37, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_STATUS_DESCRIPTION', 37, 'bottom', '#jform_published', 2, 3, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(38, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_ACCESS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_ACCESS_DESCRIPTION', 38, 'bottom', '#jform_access', 2, 3, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(39, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_TAGS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_TAGS_DESCRIPTION', 39, 'top', 'joomla-field-fancy-select .choices[data-type=select-multiple]', 2, 3, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(40, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_NOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_NOTE_DESCRIPTION', 40, 'top', '#jform_note', 2, 2, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(41, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_VERSIONNOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_VERSIONNOTE_DESCRIPTION', 41, 'top', '#jform_version_note', 2, 2, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(42, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_SAVECLOSE_DESCRIPTION', 42, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(43, 4, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CATEGORIES_STEP_CONGRATULATIONS_DESCRIPTION', 43, 'bottom', '', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(44, 5, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_NEW_DESCRIPTION', 44, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_menus&view=menus', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(45, 5, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_TITLE_DESCRIPTION', 45, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(46, 5, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_UNIQUENAME_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_UNIQUENAME_DESCRIPTION', 46, 'top', '#jform_menutype', 2, 2, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(47, 5, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_DESCRIPTION_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_DESCRIPTION_DESCRIPTION', 47, 'top', '#jform_menudescription', 2, 2, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(48, 5, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_SAVECLOSE_DESCRIPTION', 48, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(49, 5, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_MENUS_STEP_CONGRATULATIONS_DESCRIPTION', 49, 'bottom', '', 0, 1, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(50, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_NEW_DESCRIPTION', 50, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_tags&view=tags', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(51, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_TITLE_DESCRIPTION', 51, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(52, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_ALIAS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_ALIAS_DESCRIPTION', 52, 'bottom', '#jform_alias', 2, 2, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(53, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_CONTENT_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_CONTENT_DESCRIPTION', 53, 'bottom', '#jform_description,#jform_description_ifr', 2, 3, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(54, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_PARENT_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_PARENT_DESCRIPTION', 54, 'top', 'joomla-field-fancy-select .choices[data-type=select-one]', 2, 3, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(55, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_STATUS_DESCRIPTION', 55, 'bottom', '#jform_published', 2, 3, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(56, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_ACCESS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_ACCESS_DESCRIPTION', 56, 'bottom', '#jform_access', 2, 3, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(57, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_NOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_NOTE_DESCRIPTION', 57, 'top', '#jform_note', 2, 2, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(58, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_VERSIONNOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_VERSIONNOTE_DESCRIPTION', 58, 'top', '#jform_version_note', 2, 2, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(59, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_SAVECLOSE_DESCRIPTION', 59, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(60, 6, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_TAGS_STEP_CONGRATULATIONS_DESCRIPTION', 60, 'bottom', '', 0, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(61, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_NEW_DESCRIPTION', 61, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_banners&view=banners', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(62, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_TITLE_DESCRIPTION', 62, 'bottom', '#jform_name', 2, 2, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(63, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_ALIAS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_ALIAS_DESCRIPTION', 63, 'bottom', '#jform_alias', 2, 2, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(64, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_DETAILS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_DETAILS_DESCRIPTION', 64, 'bottom', '.col-lg-9', 2, 3, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(65, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_STATUS_DESCRIPTION', 65, 'bottom', '#jform_state', 2, 3, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(66, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_CATEGORY_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_CATEGORY_DESCRIPTION', 66, 'top', 'joomla-field-fancy-select .choices[data-type=select-one]', 2, 3, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(67, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_PINNED_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_PINNED_DESCRIPTION', 67, 'bottom', '#jform_sticky1', 2, 3, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(68, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_VERSIONNOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_VERSIONNOTE_DESCRIPTION', 68, 'top', '#jform_version_note', 2, 2, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(69, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_SAVECLOSE_DESCRIPTION', 69, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(70, 7, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_BANNERS_STEP_CONGRATULATIONS_DESCRIPTION', 70, 'bottom', '', 0, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(71, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_NEW_DESCRIPTION', 71, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_contact&view=contacts', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(72, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_TITLE_DESCRIPTION', 72, 'bottom', '#jform_name', 2, 2, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(73, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_ALIAS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_ALIAS_DESCRIPTION', 73, 'bottom', '#jform_alias', 2, 2, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(74, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_DETAILS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_DETAILS_DESCRIPTION', 74, 'bottom', '.col-lg-9', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(75, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_STATUS_DESCRIPTION', 75, 'bottom', '#jform_published', 2, 3, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(76, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_CATEGORY_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_CATEGORY_DESCRIPTION', 76, 'top', 'joomla-field-fancy-select .choices[data-type=select-one]', 2, 3, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(77, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_FEATURED_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_FEATURED_DESCRIPTION', 77, 'bottom', '#jform_featured0', 2, 3, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(78, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_ACCESS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_ACCESS_DESCRIPTION', 78, 'bottom', '#jform_access', 2, 3, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(79, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_TAGS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_TAGS_DESCRIPTION', 79, 'top', 'joomla-field-fancy-select .choices[data-type=select-multiple]', 2, 3, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(80, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_VERSIONNOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_VERSIONNOTE_DESCRIPTION', 80, 'top', '#jform_version_note', 2, 2, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(81, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_SAVECLOSE_DESCRIPTION', 81, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(82, 8, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_CONTACTS_STEP_CONGRATULATIONS_DESCRIPTION', 82, 'bottom', '', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(83, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_NEW_DESCRIPTION', 83, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeeds', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(84, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_TITLE_DESCRIPTION', 84, 'bottom', '#jform_name', 2, 2, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(85, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_ALIAS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_ALIAS_DESCRIPTION', 85, 'bottom', '#jform_alias', 2, 2, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(86, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_LINK_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_LINK_DESCRIPTION', 86, 'bottom', '#jform_link', 2, 2, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(87, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_DESCRIPTION_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_DESCRIPTION_DESCRIPTION', 87, 'bottom', '#jform_description,#jform_description_ifr', 2, 3, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(88, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_STATUS_DESCRIPTION', 88, 'bottom', '#jform_published', 2, 3, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(89, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_CATEGORY_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_CATEGORY_DESCRIPTION', 89, 'top', 'joomla-field-fancy-select .choices[data-type=select-one]', 2, 3, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(90, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_ACCESS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_ACCESS_DESCRIPTION', 90, 'bottom', '#jform_access', 2, 3, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(91, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_TAGS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_TAGS_DESCRIPTION', 91, 'top', 'joomla-field-fancy-select .choices[data-type=select-multiple]', 2, 3, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(92, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_VERSIONNOTE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_VERSIONNOTE_DESCRIPTION', 92, 'top', '#jform_version_note', 2, 2, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(93, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_SAVECLOSE_DESCRIPTION', 93, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(94, 9, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_NEWSFEEDS_STEP_CONGRATULATIONS_DESCRIPTION', 94, 'bottom', '', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(95, 10, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_NEW_DESCRIPTION', 95, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_finder&view=filters', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(96, 10, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_TITLE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_TITLE_DESCRIPTION', 96, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(97, 10, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_ALIAS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_ALIAS_DESCRIPTION', 97, 'bottom', '#jform_alias', 2, 2, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(98, 10, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_CONTENT_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_CONTENT_DESCRIPTION', 98, 'bottom', '.col-lg-9', 0, 1, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(99, 10, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_STATUS_DESCRIPTION', 99, 'bottom', '#jform_state', 2, 3, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(100, 10, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_SAVECLOSE_DESCRIPTION', 100, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(101, 10, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_SMARTSEARCH_STEP_CONGRATULATIONS_DESCRIPTION', 101, 'bottom', '', 0, 1, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(102, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_NEW_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_NEW_DESCRIPTION', 102, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(103, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_NAME_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_NAME_DESCRIPTION', 103, 'bottom', '#jform_name', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(104, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_LOGINNAME_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_LOGINNAME_DESCRIPTION', 104, 'bottom', '#jform_username', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(105, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_PASSWORD_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_PASSWORD_DESCRIPTION', 105, 'bottom', '#jform_password', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(106, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_PASSWORD2_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_PASSWORD2_DESCRIPTION', 106, 'bottom', '#jform_password2', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(107, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_EMAIL_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_EMAIL_DESCRIPTION', 107, 'bottom', '#jform_email', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(108, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_SYSTEMEMAIL_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_SYSTEMEMAIL_DESCRIPTION', 108, 'top', '#jform_sendEmail0', 2, 3, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(109, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_STATUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_STATUS_DESCRIPTION', 109, 'top', '#jform_block0', 2, 3, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(110, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_PASSWORDRESET_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_PASSWORDRESET_DESCRIPTION', 110, 'top', '#jform_requireReset0', 2, 3, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(111, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_SAVECLOSE_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_SAVECLOSE_DESCRIPTION', 111, 'bottom', '#save-group-children-save .button-save', 2, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(112, 11, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_CONGRATULATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_USERS_STEP_CONGRATULATIONS_DESCRIPTION', 112, 'bottom', '', 0, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),

(113, 12, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_MENUS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_MENUS_DESCRIPTION', 113, 'right', '#sidebarmenu', 0, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(114, 12, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_QUICKACCESS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_QUICKACCESS_DESCRIPTION', 114, 'center', '', 0, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(115, 12, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_NOTIFICATIONS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_NOTIFICATIONS_DESCRIPTION', 115, 'left', '.quickicons-for-update_quickicon .card', 0, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(116, 12, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_TOPBAR_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_TOPBAR_DESCRIPTION', 116, 'bottom', '#header', 0, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*'),
(117, 12, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_FINALWORDS_TITLE', 1, 'COM_GUIDEDTOURS_TOUR_WELCOMETOJOOMLA_STEP_FINALWORDS_DESCRIPTION', 117, 'right', '#sidebarmenu nav > ul:first-of-type > li:last-child', 0, 1, '', CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42, '*');

SELECT setval('#__guidedtour_steps_id_seq', 118, false);

-- --------------------------------------------------------

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
