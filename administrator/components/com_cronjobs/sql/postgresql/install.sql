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
        CREATE TYPE trigger_type AS ENUM ('pseudo_cron', 'cron', 'visit_count');
    EXCEPTION
        WHEN duplicate_object THEN null;
    END
$$;
---------------------------------------------------------------------------

------------------- Table Structure "#__cronjobs" [Main] -------------------
CREATE TABLE IF NOT EXISTS "#__cronjobs"
(
    "job_id"             INT GENERATED ALWAYS AS IDENTITY,
    "name"               VARCHAR(255) NOT NULL,
    "asset_id"           INT          NOT NULL UNIQUE DEFAULT '0',
    "created"            TIMESTAMP    NOT NULL        DEFAULT '0000-00-00 00:00:00',
    "created_by"         INT          NOT NULL        DEFAULT '0',
    "type"               JOB_TYPE,
    "execution_interval" INT          NOT NULL,
    "trigger"            TRIGGER_TYPE NOT NULL        DEFAULT 'pseudo_cron',
    "enabled"            BOOLEAN      NOT NULL        DEFAULT false,
    "last_exit_code"     INT          NOT NULL        DEFAULT 0,
    "last_execution"     TIMESTAMP    NOT NULL,
    "next_execution"     TIMESTAMP    NOT NULL,
    "times_executed"     TIMESTAMP    NOT NULL,
    "times_failed"       int                          DEFAULT 0,
    "note"               varchar(512)
);
----------------------------------------------------------------------------

------ Table Structure "#__cronjobs_scripts" ------

CREATE TABLE IF NOT EXISTS "#cronjobs_scripts"
(
    "script_id" INT GENERATED ALWAYS AS IDENTITY,
    "job_id"    INT,
    "directory" VARCHAR(256) NOT NULL,
    "file"      VARCHAR(128) NOT NULL,
    CONSTRAINT "job_id"
        FOREIGN KEY (job_id)
            REFERENCES "#__cronjobs" (job_id)
);
---------------------------------------------------
