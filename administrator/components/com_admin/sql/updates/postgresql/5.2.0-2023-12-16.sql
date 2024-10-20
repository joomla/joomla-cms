--
-- Table structure for table "#__scheduler_logs"
--

CREATE TABLE IF NOT EXISTS "#__scheduler_logs" (
  "id" serial NOT NULL,
  "taskname" varchar(255) DEFAULT '' NOT NULL,
  "tasktype" varchar(128) NOT NULL,
  "duration" NUMERIC(5,3) NOT NULL,
  "jobid" integer NOT NULL,
  "taskid" integer NOT NULL,
  "exitcode" integer NOT NULL,
  "lastdate" timestamp without time zone,
  "nextdate" timestamp without time zone,
  PRIMARY KEY (id)
);
CREATE INDEX "#__scheduler_logs_idx_taskname" ON "#__scheduler_logs" ("taskname");
CREATE INDEX "#__scheduler_logs_idx_tasktype" ON "#__scheduler_logs" ("tasktype");
CREATE INDEX "#__scheduler_logs_idx_lastdate" ON "#__scheduler_logs" ("lastdate");
CREATE INDEX "#__scheduler_logs_idx_nextdate" ON "#__scheduler_logs" ("nextdate");
