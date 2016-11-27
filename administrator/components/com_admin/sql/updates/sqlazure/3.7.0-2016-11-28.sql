-- Add core field to extensions table.
ALTER TABLE [#__extensions] ADD [core] [smallint] NOT NULL DEFAULT 0;
