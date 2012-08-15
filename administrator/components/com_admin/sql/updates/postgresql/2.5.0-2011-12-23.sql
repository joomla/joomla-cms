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
CREATE INDEX "#__finder_links_idx_url" on "#__finder_links" (left(url,75));
CREATE INDEX "#__finder_links_idx_published_list" on "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "list_price");
CREATE INDEX "#__finder_links_idx_published_sale" on "#__finder_links" ("published", "state", "access", "publish_start_date", "publish_end_date", "sale_price");