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
  "last_date" timestamp without time zone,
  "next_date" timestamp without time zone,
  PRIMARY KEY (id),
);
CREATE INDEX "#__scheduler_tasks_idx_taskname" ON "#__scheduler_tasks" ("taskname");
CREATE INDEX "#__scheduler_tasks_idx_tasktype" ON "#__scheduler_tasks" ("tasktype");
CREATE INDEX "#__scheduler_tasks_idx_last_date" ON "#__scheduler_tasks" ("last_date");
CREATE INDEX "#__scheduler_tasks_idx_next_date" ON "#__scheduler_tasks" ("next_date");