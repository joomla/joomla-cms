--
-- Table structure for table "#__cronjobs"
--

CREATE TABLE IF NOT EXISTS "#__cronjobs"
(
    "id" int GENERATED ALWAYS AS IDENTITY,
    "asset_id" bigint NOT NULL DEFAULT '0',
    "title" varchar(255) NOT NULL,
    "type" varchar(6),
    "execution_interval" interval NOT NULL,
    "trigger" varchar(12) NOT NULL DEFAULT 'pseudo_cron',
    "state" smallint NOT NULL DEFAULT '0',
    "last_exit_code" int NOT NULL DEFAULT '0',
    "last_execution" timestamp without time zone,
    "next_execution" timestamp without time zone,
    "times_executed" int NOT NULL DEFAULT '0',
    "times_failed" int DEFAULT '0',
    "note" text DEFAULT '',
    "created" timestamp without time zone NOT NULL,
    "created_by" bigint NOT NULL DEFAULT '0'
);
