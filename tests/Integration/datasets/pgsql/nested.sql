DROP TABLE IF EXISTS "#__testnested";
CREATE TABLE IF NOT EXISTS "#__testnested" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" varchar(400) DEFAULT '' NOT NULL,
  "alias" varchar(400) DEFAULT '' NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__testnested_idx_left_right" ON "#__testnested" ("lft", "rgt");
CREATE INDEX "#__testnested_idx_parent_id" ON "#__testnested" ("parent_id");

DROP TABLE IF EXISTS "#__testnestedplain";
CREATE TABLE IF NOT EXISTS "#__testnestedplain" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" varchar(400) DEFAULT '' NOT NULL,
  "alias" varchar(400) DEFAULT '' NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__testnestedplain_idx_left_right" ON "#__testnestedplain" ("lft", "rgt");
CREATE INDEX "#__testnestedplain_idx_parent_id" ON "#__testnestedplain" ("parent_id");

DROP TABLE IF EXISTS "#__testnestedminimal";
CREATE TABLE IF NOT EXISTS "#__testnestedminimal" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__testnestedminimal_idx_left_right" ON "#__testnestedminimal" ("lft", "rgt");
CREATE INDEX "#__testnestedminimal_idx_parent_id" ON "#__testnestedminimal" ("parent_id");
