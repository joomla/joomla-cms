-- Drop default values
DECLARE @table AS nvarchar(100)
DECLARE @constraintName AS nvarchar(100)
DECLARE @constraintQuery AS nvarchar(1000)
SET QUOTED_IDENTIFIER OFF
SET @table = "#__privacy_requests"
SET QUOTED_IDENTIFIER ON

-- Drop default value from checked_out
SELECT @constraintName = name FROM sys.default_constraints
WHERE parent_object_id = object_id(@table)
AND parent_column_id = columnproperty(object_id(@table), 'checked_out', 'ColumnId')
SET @constraintQuery = 'ALTER TABLE [' + @table + '] DROP CONSTRAINT [' + @constraintName + ']'
EXECUTE sp_executesql @constraintQuery

-- Drop default value from checked_out_time
SELECT @constraintName = name FROM sys.default_constraints
WHERE parent_object_id = object_id(@table)
AND parent_column_id = columnproperty(object_id(@table), 'checked_out_time', 'ColumnId')
SET @constraintQuery = 'ALTER TABLE [' + @table + '] DROP CONSTRAINT [' + @constraintName + ']'
EXECUTE sp_executesql @constraintQuery;

DROP INDEX "idx_checkout" ON "#__privacy_requests";
ALTER TABLE "#__privacy_requests" DROP COLUMN "checked_out";
ALTER TABLE "#__privacy_requests" DROP COLUMN "checked_out_time";
