ALTER TABLE "#__finder_terms"
	DROP CONSTRAINT "#__finder_terms_idx_term",
	ADD CONSTRAINT "#__finder_terms_idx_term_language" UNIQUE ("term", "language");
CREATE INDEX "#__finder_terms_idx_language" on "#__finder_terms" ("language");