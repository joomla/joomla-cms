sp_rename "#__session", "#__session_old";

SELECT cast("session_id" AS varbinary) AS "session_id", "client_id", "guest", cast("time" AS int) AS "time", "data", "userid", "username"
INTO "#__session"
FROM "#__session_old";

DROP TABLE "#__session_old";

ALTER TABLE "#__session" ALTER COLUMN "session_id" varbinary(192) NOT NULL;
ALTER TABLE "#__session" ADD CONSTRAINT "PK_#__session_session_id" PRIMARY KEY CLUSTERED ("session_id") ON [PRIMARY];
ALTER TABLE "#__session" ALTER COLUMN "time" int NOT NULL;
ALTER TABLE "#__session" ADD DEFAULT (0) FOR "time";

CREATE NONCLUSTERED INDEX "time" ON "#__session" ("time");
CREATE NONCLUSTERED INDEX "userid" ON "#__session" ("userid");
