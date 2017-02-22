-- Add the Inheritable property to the #__menu table.
ALTER TABLE "#__menu" ADD "inheritable" smallint NOT NULL DEFAULT 1;