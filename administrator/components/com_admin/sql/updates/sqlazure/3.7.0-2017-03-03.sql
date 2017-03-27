CREATE PROCEDURE "#removeDefault"
(
	@table NVARCHAR(100),
	@column NVARCHAR(100)
)
AS
BEGIN
	DECLARE @constraintName AS nvarchar(100)
	DECLARE @constraintQuery AS nvarchar(1000)
	SELECT @constraintName = name FROM sys.default_constraints
		WHERE parent_object_id = object_id(@table)
		AND parent_column_id = columnproperty(object_id(@table), @column, 'ColumnId')
	SET @constraintQuery = 'ALTER TABLE [' + @table + '] DROP CONSTRAINT [' + @constraintName + ']'
	EXECUTE sp_executesql @constraintQuery
END;

EXECUTE "#removeDefault" "#__extensions", 'system_data';
EXECUTE "#removeDefault" "#__updates", 'data';

ALTER TABLE "#__content" ADD DEFAULT ('') FOR "xreference";
ALTER TABLE "#__newsfeeds" ADD DEFAULT ('') FOR "xreference";

-- Delete wrong unique index
DROP INDEX "idx_access" ON "#__languages";

-- Add missing unique index
ALTER TABLE "#__languages" ADD CONSTRAINT "#__languages$idx_langcode" UNIQUE ("lang_code") ON [PRIMARY];

-- Add missing index keys
CREATE INDEX "idx_access" ON "#__languages" ("access");
CREATE INDEX "idx_ordering" ON "#__languages" ("ordering");
