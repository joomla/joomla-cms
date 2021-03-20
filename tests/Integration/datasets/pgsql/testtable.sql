DROP TABLE IF EXISTS "#__testtable";
CREATE TABLE IF NOT EXISTS "#__testtable" (
   "id" serial NOT NULL,
   "title" varchar(100) NOT NULL,
   "asset_id" bigint NOT NULL DEFAULT 0,
   "hits" bigint NOT NULL DEFAULT 0,
   "checked_out" integer,
   "checked_out_time" timestamp without time zone,
   "published" smallint DEFAULT 0 NOT NULL,
   "publish_up" timestamp without time zone,
   "publish_down" timestamp without time zone,
   "ordering" bigint NOT NULL DEFAULT 0,
   "params" text NOT NULL,
   PRIMARY KEY ("id")
);
