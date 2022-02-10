-- Add the modifier column to the finder links table. Set this to something bigger than 1 to position it further up in the search results.
ALTER TABLE "#__finder_links" ADD COLUMN "modifier" INTEGER NOT NULL DEFAULT 1;

-- Adding the above column invalidates the serialised objects in this table. Clearing the md5sum allows to re-index everything without having to clear the index first.
UPDATE "#__finder_links" SET "md5sum" = '';

INSERT INTO "#__postinstall_messages" ("extension_id", "title_key", "description_key", "language_extension", "language_client_id", "type", "version_introduced", "enabled")
SELECT "extension_id", 'COM_FINDER_POSTINSTALL_MODIFIER_REINDEX_TITLE', 'COM_FINDER_POSTINSTALL_MODIFIER_REINDEX_BODY', 'com_finder', 1, 'message', '4.2.0', 1 FROM "#__extensions" WHERE "name" = 'files_joomla';
