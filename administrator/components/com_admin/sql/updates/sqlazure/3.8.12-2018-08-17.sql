ALTER TABLE "#__finder_terms_common" DROP CONSTRAINT "PK_#__finder_terms_common";
ALTER TABLE "#__finder_terms_common" DROP CONSTRAINT "idx_word_lang";
ALTER TABLE "#__finder_terms_common" ADD CONSTRAINT "PK_#__finder_terms_common" PRIMARY KEY ("term", "language");
