-- ⚠ This update causes data loss.

DROP TABLE IF EXISTS "#__cronjobs_scripts";
DROP TABLE IF EXISTS "#__cronjobs";

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
    "id"                 INT GENERATED ALWAYS AS IDENTITY,
    "asset_id"           BIGINT                   NOT NULL DEFAULT '0',
    "title"              VARCHAR(255)             NOT NULL,
    "type"               JOB_TYPE,
    "execution_interval" INT                      NOT NULL,
    "trigger"            TRIGGER_TYPE             NOT NULL DEFAULT 'pseudo_cron',
    "state"              SMALLINT                 NOT NULL DEFAULT '0',
    "last_exit_code"     INT                      NOT NULL DEFAULT '0',
    "last_execution"     TIMESTAMP                NOT NULL,
    "next_execution"     TIMESTAMP                NOT NULL,
    "times_executed"     TIMESTAMP                NOT NULL,
    "times_failed"       INT                               DEFAULT '0',
    "note"               TEXT                              DEFAULT '',
    "created"            TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT '0000-00-00 00:00:00',
    "created_by"         BIGINT                   NOT NULL DEFAULT '0'
);
----------------------------------------------------------------------------

------ Table Structure "#__cronjobs_scripts" ------

CREATE TABLE IF NOT EXISTS "#__cronjobs_scripts"
(
    "id"        INT GENERATED ALWAYS AS IDENTITY,
    "job_id"    INT, -- References "#__cronjobs"(id)
    "directory" VARCHAR(256) NOT NULL,
    "file"      VARCHAR(128) NOT NULL
);
---------------------------------------------------

