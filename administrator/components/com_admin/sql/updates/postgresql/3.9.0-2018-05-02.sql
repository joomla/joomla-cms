INSERT INTO "#__extensions" ("extension_id", "package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "manifest_cache", "params", "custom_data", "system_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(35, 0, 'com_privacy', 'component', 'com_privacy', '', 1, 1, 1, 1, '', '', '', '', 0, '1970-01-01 00:00:00', 0, 0);

CREATE TABLE "#__privacy_requests" (
  "id" serial NOT NULL,
  "email" varchar(100) DEFAULT '' NOT NULL,
  "requested_at" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "status" smallint DEFAULT 0 NOT NULL,
  "request_type" varchar(25) DEFAULT '' NOT NULL,
  "confirm_token" varchar(100) DEFAULT '' NOT NULL,
  "confirm_token_created_at" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  "checked_out" integer DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone DEFAULT '1970-01-01 00:00:00' NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__privacy_requests_idx_checked_out" ON "#__privacy_requests" ("checked_out");
