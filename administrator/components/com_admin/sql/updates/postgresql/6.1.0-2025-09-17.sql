CREATE TABLE IF NOT EXISTS "#__magiclogin_tokens" (
  "id" SERIAL PRIMARY KEY,
  "user_id" INTEGER NOT NULL,
  "token" VARCHAR(255) NOT NULL UNIQUE,
  "expires" TIMESTAMP NOT NULL,
  "created" TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  "ip_address" VARCHAR(45),
  "user_agent" TEXT
);

CREATE INDEX IF NOT EXISTS "idx_magiclogin_tokens_user_id" ON "#__magiclogin_tokens" ("user_id");
CREATE INDEX IF NOT EXISTS "idx_magiclogin_tokens_expires" ON "#__magiclogin_tokens" ("expires");
CREATE INDEX IF NOT EXISTS "idx_magiclogin_tokens_ip_address" ON "#__magiclogin_tokens" ("ip_address");

INSERT INTO "#__mail_templates" ("template_id", "extension", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES
('plg_system_magiclogin.magiclink', 'plg_system_magiclogin', '', 'PLG_SYSTEM_MAGICLOGIN_EMAIL_SUBJECT', 'PLG_SYSTEM_MAGICLOGIN_EMAIL_BODY', 'PLG_SYSTEM_MAGICLOGIN_EMAIL_HTMLBODY', '', '{"tags":["sitename","username","magic_link","expiry_minutes"]}');