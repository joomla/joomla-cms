CREATE TABLE IF NOT EXISTS "#__wf_profiles" (
    "id" serial NOT NULL,
    "name" character varying(255) NOT NULL,
    "description" text NOT NULL,
    "users" text NOT NULL,
    "types" text NOT NULL,
    "components" text NOT NULL,
    "area" smallint NOT NULL,
    "device" character varying(255) NOT NULL,
    "rows" text NOT NULL,
    "plugins" text NOT NULL,
    "published" smallint NOT NULL,
    "ordering" integer NOT NULL,
    "checked_out" integer NOT NULL,
    "checked_out_time" timestamp without time zone NOT NULL,
    "params" text NOT NULL,
    PRIMARY KEY ("id")
);
