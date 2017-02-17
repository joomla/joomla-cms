-- Add the Inheritable property to the #__menu table.
ALTER TABLE [#__menu] ADD [inheritable] [tinyint] NOT NULL DEFAULT 1;