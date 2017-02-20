-- Add the Inheritable property to the #__menu table.
ALTER TABLE [#__menu] ADD [inheritable] [tinyint] NOT NULL DEFAULT 1;
-- Normalize contact_details table default values.
DECLARE @table AS nvarchar(32)
DECLARE @constraintName AS nvarchar(100)
DECLARE @constraintQuery AS nvarchar(1000)
SET QUOTED_IDENTIFIER OFF
SET @table = "#__contact_details"
SET QUOTED_IDENTIFIER ON
SELECT @constraintName = name FROM sys.default_constraints
WHERE parent_object_id = object_id(@table)
AND parent_column_id = columnproperty(object_id(@table), 'name', 'ColumnId')
SET @constraintQuery = 'ALTER TABLE [' + @table + '] DROP CONSTRAINT [' + @constraintName + ']'
EXECUTE sp_executesql @constraintQuery;

ALTER TABLE [#__contact_details] ADD DEFAULT (0) FOR [published];
ALTER TABLE [#__contact_details] ADD DEFAULT (0) FOR [checked_out];
ALTER TABLE [#__contact_details] ADD DEFAULT ('') FOR [created_by_alias];

ALTER TABLE [#__contact_details] ADD DEFAULT ('') FOR [sortname1];
ALTER TABLE [#__contact_details] ADD DEFAULT ('') FOR [sortname2];
ALTER TABLE [#__contact_details] ADD DEFAULT ('') FOR [sortname3];
ALTER TABLE [#__contact_details] ADD DEFAULT ('') FOR [xreference];
