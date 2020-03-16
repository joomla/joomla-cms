DROP TABLE IF EXISTS "#__assets";
CREATE TABLE IF NOT EXISTS "#__assets" (
   "id" serial NOT NULL,
   "parent_id" bigint DEFAULT 0 NOT NULL,
   "lft" bigint DEFAULT 0 NOT NULL,
   "rgt" bigint DEFAULT 0 NOT NULL,
   "level" integer NOT NULL,
   "name" varchar(50) NOT NULL,
   "title" varchar(100) NOT NULL,
   "rules" varchar(5120) NOT NULL,
   PRIMARY KEY ("id"),
   CONSTRAINT "#__assets_idx_asset_name" UNIQUE ("name")
);
CREATE INDEX "#__assets_idx_lft_rgt" ON "#__assets" ("lft", "rgt");
CREATE INDEX "#__assets_idx_parent_id" ON "#__assets" ("parent_id");

COMMENT ON COLUMN "#__assets"."id" IS 'Primary Key';
COMMENT ON COLUMN "#__assets"."parent_id" IS 'Nested set parent.';
COMMENT ON COLUMN "#__assets"."lft" IS 'Nested set lft.';
COMMENT ON COLUMN "#__assets"."rgt" IS 'Nested set rgt.';
COMMENT ON COLUMN "#__assets"."level" IS 'The cached level in the nested tree.';
COMMENT ON COLUMN "#__assets"."name" IS 'The unique name for the asset.';
COMMENT ON COLUMN "#__assets"."title" IS 'The descriptive title for the asset.';
COMMENT ON COLUMN "#__assets"."rules" IS 'JSON encoded access control.';

--
-- Dumping data for table `#__assets`
--

INSERT INTO "#__assets" ("id", "parent_id", "lft", "rgt", "level", "name", "title", "rules") VALUES
(1, 0, 0, 1, 0, 'root.1', 'Root Asset', '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.api":{"8":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}');
