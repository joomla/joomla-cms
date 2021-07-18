--
-- Create enumerated types for "#__cronjobs"
--

DO
$$
    BEGIN
        CREATE TYPE job_type AS enum ('script', 'plugin');
    EXCEPTION
        WHEN duplicate_object THEN NULL;
    END
$$;

DO
$$
    BEGIN
        CREATE TYPE trigger_type AS enum ('pseudo_cron', 'cron', 'visit_count');
    EXCEPTION
        WHEN duplicate_object THEN NULL;
    END
$$;

-- --------------------------------------------------------

--
-- Table structure for table "#__cronjobs"
--

CREATE TABLE IF NOT EXISTS "#__cronjobs"
(
    "id" int GENERATED ALWAYS AS IDENTITY,
    "asset_id" bigint NOT NULL DEFAULT '0',
    "title" varchar(255) NOT NULL,
    "type" job_type,
    "execution_interval" interval NOT NULL,
    "trigger" trigger_type NOT NULL DEFAULT 'pseudo_cron',
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

-- --------------------------------------------------------

--
-- Table structure for table "#__cronjobs_scripts"
--

CREATE TABLE IF NOT EXISTS "#__cronjobs_scripts"
(
    "id" int GENERATED ALWAYS AS IDENTITY,
    "job_id" int, -- References "#__cronjobs"(id)
    "directory" varchar(256) NOT NULL,
    "file" varchar(128) NOT NULL
);

-- --------------------------------------------------------
