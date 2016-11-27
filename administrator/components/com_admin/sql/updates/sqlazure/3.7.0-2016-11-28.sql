-- Add core field to extensions table.
ALTER TABLE [#__extensions] ADD [core] [smallint] NOT NULL DEFAULT 0;

UPDATE [#__extensions]
SET [core] = 1
WHERE [element] IN ();
