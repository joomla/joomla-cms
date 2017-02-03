-- Sync menutype for admin menu and set client_id correct
ALTER TABLE [#__menu] ADD [inheritable] [tinyint] NOT NULL DEFAULT 1;
