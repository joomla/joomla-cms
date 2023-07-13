INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
    (0, 'plg_editors-xtd_guidedtour', 'plugin', 'compat', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 0, 0);

--
-- Table structure for table `#__guidedtours`
--

CREATE TABLE IF NOT EXISTS "#__guidedtour_user_steps" (
  "tour_id" bigint DEFAULT 0 NOT NULL,
  "step_id" bigint DEFAULT 0 NOT NULL,
  "user_id" bigint DEFAULT 0 NOT NULL,
  "viewed" timestamp without time zone NOT NULL
);

CREATE INDEX "#__guidedtours_idx_tour" ON "#__guidedtours" ("tour_id");
CREATE INDEX "#__guidedtours_idx_step" ON "#__guidedtours" ("step_id");
CREATE INDEX "#__guidedtours_idx_user" ON "#__guidedtours" ("user_id");
CREATE INDEX "#__guidedtours_idx_viewed" ON "#__guidedtours" ("viewed");

ALTER TABLE "#__guidedtours" ADD COLUMN "params" text NOT NULL /** CAN FAIL **/;