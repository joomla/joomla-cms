CREATE TABLE "#__session" (
  "session_id" VARCHAR(128) NOT NULL,
  "time" INTEGER NOT NULL,
  "data" BYTEA NOT NULL,
  PRIMARY KEY ("session_id")
);
