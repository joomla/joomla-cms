-- Change default value for enabled column.
DECLARE @table AS nvarchar(100)
DECLARE @constraintName AS nvarchar(100)
DECLARE @constraintQuery AS nvarchar(1000)
SET QUOTED_IDENTIFIER OFF
SET @table = "#__extensions"
SET QUOTED_IDENTIFIER ON
SELECT @constraintName = name FROM sys.default_constraints
WHERE parent_object_id = object_id(@table)
AND parent_column_id = columnproperty(object_id(@table), 'enabled', 'ColumnId')
SET @constraintQuery = 'ALTER TABLE [' + @table + '] DROP CONSTRAINT [' + @constraintName
+ ']; ALTER TABLE [' + @table + '] ADD CONSTRAINT [' + @constraintName + '] DEFAULT 0 FOR [enabled]'
EXECUTE sp_executesql @constraintQuery;
