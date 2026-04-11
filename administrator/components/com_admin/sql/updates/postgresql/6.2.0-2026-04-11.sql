CREATE TABLE IF NOT EXISTS "#__content_preview_tokens" (
  "id" serial NOT NULL,
  "token" varchar(64) NOT NULL DEFAULT '',
  "user_id" integer NOT NULL DEFAULT 0,
  "article_id" integer NOT NULL DEFAULT 0,
  "expires" timestamp without time zone NOT NULL,
  PRIMARY KEY ("id")
);

CREATE UNIQUE INDEX IF NOT EXISTS "#__content_preview_tokens_idx_token" ON "#__content_preview_tokens" ("token");
CREATE INDEX IF NOT EXISTS "#__content_preview_tokens_idx_article_id" ON "#__content_preview_tokens" ("article_id");
CREATE INDEX IF NOT EXISTS "#__content_preview_tokens_idx_expires" ON "#__content_preview_tokens" ("expires");
