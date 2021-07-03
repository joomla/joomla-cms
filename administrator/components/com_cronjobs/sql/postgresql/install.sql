------- Create required enumerated types with exception handling ----------
DO
$$
    BEGIN
        CREATE TYPE job_type AS ENUM ('script', 'plugin');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END
$$;

DO
$$
    BEGIN
        CREATE TYPE trigger_type AS ENUM ('pseudoCron', 'cron', 'visit_count');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END
$$;
---------------------------------------------------------------------------

-- Table Structure "#__cronjobs" [Main] --

CREATE TABLE IF NOT EXISTS "#__cronjobs"
(
    "job_id"         INT GENERATED ALWAYS AS IDENTITY,
    "name"           varchar(255) NOT NULL,
    "type"           job_type,
    "trigger"        trigger_type NOT NULL DEFAULT 'pseudoCron',
    "enabled"        boolean      NOT NULL DEFAULT false,
    "last_exit_code" int          NOT NULL DEFAULT 0,
    "last_execution" timestamp    NOT NULL,
    "next_execution" timestamp    NOT NULL,
    "times_executed" timestamp    NOT NULL,
    "times_failed"   int                   DEFAULT 0,
    "note"           varchar(512)
);

-- Table Structure "#__cronjobs_scripts" --
CREATE TABLE IF NOT EXISTS "#cronjobs_scripts"
(
    "script_id" INT GENERATED ALWAYS AS IDENTITY,
    "job_id"    INT,
    "directory" varchar(256) NOT NULL,
    "file"      varchar(128) NOT NULL,
    CONSTRAINT "job_id"
        FOREIGN KEY (job_id)
            REFERENCES "#__cronjobs" (job_id)
);
