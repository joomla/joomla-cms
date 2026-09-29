DROP TABLE IF EXISTS "#__testtable";
CREATE TABLE IF NOT EXISTS "#__testtable" (
   "id" serial NOT NULL,
   "title" varchar(100) NOT NULL DEFAULT '',
   "asset_id" bigint NOT NULL DEFAULT 0,
   "hits" bigint NOT NULL DEFAULT 0,
   "checked_out" integer,
   "checked_out_time" timestamp without time zone,
   "published" smallint NOT NULL DEFAULT 0,
   "publish_up" timestamp without time zone,
   "publish_down" timestamp without time zone,
   "ordering" bigint NOT NULL DEFAULT 0,
   "params" text NOT NULL,
   PRIMARY KEY ("id")
);
