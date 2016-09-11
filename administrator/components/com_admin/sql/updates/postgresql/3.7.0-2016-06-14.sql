CREATE TABLE "#__content_draft" (
  "id"         SERIAL                                                     NOT NULL,
  "articleId"  INTEGER                                                    NOT NULL,
  "created"    TIMESTAMP WITHOUT TIME ZONE DEFAULT '1970-01-01 00:00:00'  NOT NULL,
  "sharetoken" VARCHAR(16) DEFAULT ''                                     NOT NULL,
  PRIMARY KEY ("id")
);