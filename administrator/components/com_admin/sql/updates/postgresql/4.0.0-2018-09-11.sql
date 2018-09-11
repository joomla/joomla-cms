CREATE TABLE IF NOT EXISTS "#__mail_templates" (
  "mail_id" varchar(127) NOT NULL DEFAULT '',
  "language" char(7) NOT NULL DEFAULT '',
  "subject" varchar(255) NOT NULL DEFAULT '',
  "body" TEXT NOT NULL,
  "htmlbody" TEXT NOT NULL,
  "attachments" TEXT NOT NULL,
  "params" TEXT NOT NULL,
  CONSTRAINT "#__mail_templates_idx_mail_id_language" UNIQUE ("mail_id", "language"),
);
CREATE INDEX "#__mail_templates_idx_mail_id" ON "#__mail_templates" ("mail_id");
CREATE INDEX "#__mail_templates_idx_language" ON "#__mail_templates" ("language");