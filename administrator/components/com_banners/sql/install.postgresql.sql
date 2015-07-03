
--
-- Table: #__banners
--
CREATE TABLE "#__banners" (
  "id" serial NOT NULL,
  "cid" bigint DEFAULT 0 NOT NULL,
  "type" bigint DEFAULT 0 NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "imptotal" bigint DEFAULT 0 NOT NULL,
  "impmade" bigint DEFAULT 0 NOT NULL,
  "clicks" bigint DEFAULT 0 NOT NULL,
  "clickurl" varchar(200) DEFAULT '' NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "catid" bigint DEFAULT 0 NOT NULL,
  "description" text NOT NULL,
  "custombannercode" varchar(2048) NOT NULL,
  "sticky" smallint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "metakey" text NOT NULL,
  "params" text NOT NULL,
  "own_prefix" smallint DEFAULT 0 NOT NULL,
  "metakey_prefix" varchar(255) DEFAULT '' NOT NULL,
  "purchase_type" smallint DEFAULT -1 NOT NULL,
  "track_clicks" smallint DEFAULT -1 NOT NULL,
  "track_impressions" smallint DEFAULT -1 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "reset" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "created" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
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
-- Table: #__banner_clients
--
CREATE TABLE "#__banner_clients" (
  "id" serial NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "contact" varchar(255) DEFAULT '' NOT NULL,
  "email" varchar(255) DEFAULT '' NOT NULL,
  "extrainfo" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "checked_out" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "metakey" text NOT NULL,
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
-- Table: #__banner_tracks
--
CREATE TABLE "#__banner_tracks" (
  "track_date" timestamp without time zone NOT NULL,
  "track_type" bigint NOT NULL,
  "banner_id" bigint NOT NULL,
  "count" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("track_date", "track_type", "banner_id")
);
CREATE INDEX "#__banner_tracks_idx_track_date" ON "#__banner_tracks" ("track_date");
CREATE INDEX "#__banner_tracks_idx_track_type" ON "#__banner_tracks" ("track_type");
CREATE INDEX "#__banner_tracks_idx_banner_id" ON "#__banner_tracks" ("banner_id");
