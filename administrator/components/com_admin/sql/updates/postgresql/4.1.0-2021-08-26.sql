-- after 4.0.0 RC1
 -- SQLINES LICENSE FOR EVALUATION USE ONLY
 CREATE TABLE IF NOT EXISTS "#__draft" (
  article_id int check (article_id > 0) NOT NULL,
  version_id int check (version_id > 0) NOT NULL,
  state smallint NOT NULL DEFAULT '0',
  hashval varchar(2083) NOT NULL DEFAULT '',
  shared_date timestamp(0) DEFAULT NULL,
  PRIMARY KEY(article_id, version_id)
) ;

ALTER TABLE "#__content"  ADD COLUMN shared tinyint(3) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE "#__content" ADD COLUMN draft tinyint(3) UNSIGNED NOT NULL DEFAULT '0';