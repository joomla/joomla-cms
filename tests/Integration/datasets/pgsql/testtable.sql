DROP TABLE IF EXISTS "#__testtable";
CREATE TABLE IF NOT EXISTS "#__testtable" (
   "id" serial NOT NULL,
   "title" varchar(100) NOT NULL,
   "asset_id" bigint NOT NULL DEFAULT 0,
   "hits" bigint NOT NULL DEFAULT 0,
   "checked_out" bigint NOT NULL DEFAULT 0,
   "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
   "published" smallint DEFAULT 0 NOT NULL,
   "publish_up" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
   "publish_down" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
   "ordering" bigint NOT NULL DEFAULT 0,
   "params" text NOT NULL,
   PRIMARY KEY ("id")
);
