-- Replace datetime to datetime2(0) type for all columns.
DROP INDEX [idx_track_date] ON [#__banner_tracks];
ALTER TABLE [#__banner_tracks] DROP CONSTRAINT [PK_#__banner_tracks_track_date];
ALTER TABLE [#__banner_tracks] ALTER COLUMN [track_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__banner_tracks] ADD CONSTRAINT [PK_#__banner_tracks_track_date_type_id] PRIMARY KEY CLUSTERED
(
	[track_date] ASC,
	[track_type] ASC,
	[banner_id] ASC
) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY];
CREATE NONCLUSTERED INDEX [idx_track_date2] ON [#__banner_tracks]
(
	[track_date] ASC
) WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

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

EXECUTE "#removeDefault" "#__banner_clients", 'checked_out_time';
ALTER TABLE [#__banner_clients] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__banner_clients] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__banners", 'checked_out_time';
ALTER TABLE [#__banners] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__banners] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__banners", 'publish_up';
ALTER TABLE [#__banners] ALTER COLUMN [publish_up] [datetime2](0) NOT NULL;
ALTER TABLE [#__banners] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_up];

EXECUTE "#removeDefault" "#__banners", 'publish_down';
ALTER TABLE [#__banners] ALTER COLUMN [publish_down] [datetime2](0) NOT NULL;
ALTER TABLE [#__banners] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_down];

EXECUTE "#removeDefault" "#__banners", 'reset';
ALTER TABLE [#__banners] ALTER COLUMN [reset] [datetime2](0) NOT NULL;
ALTER TABLE [#__banners] ADD DEFAULT '1900-01-01 00:00:00' FOR [reset];

EXECUTE "#removeDefault" "#__banners", 'created';
ALTER TABLE [#__banners] ALTER COLUMN [created] [datetime2](0) NOT NULL;
ALTER TABLE [#__banners] ADD DEFAULT '1900-01-01 00:00:00' FOR [created];

EXECUTE "#removeDefault" "#__banners", 'modified';
ALTER TABLE [#__banners] ALTER COLUMN [modified] [datetime2](0) NOT NULL;
ALTER TABLE [#__banners] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified];

DROP INDEX [idx_checked_out_time] ON [#__categories];
EXECUTE "#removeDefault" "#__categories", 'checked_out_time';
ALTER TABLE [#__categories] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__categories] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];
CREATE NONCLUSTERED INDEX [idx_checked_out_time2] ON [#__categories](
	[checked_out_time] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

EXECUTE "#removeDefault" "#__categories", 'created_time';
ALTER TABLE [#__categories] ALTER COLUMN [created_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__categories] ADD DEFAULT '1900-01-01 00:00:00' FOR [created_time];

EXECUTE "#removeDefault" "#__categories", 'modified_time';
ALTER TABLE [#__categories] ALTER COLUMN [modified_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__categories] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified_time];

EXECUTE "#removeDefault" "#__contact_details", 'checked_out_time';
ALTER TABLE [#__contact_details] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__contact_details] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__contact_details", 'created';
ALTER TABLE [#__contact_details] ALTER COLUMN [created] [datetime2](0) NOT NULL;
ALTER TABLE [#__contact_details] ADD DEFAULT '1900-01-01 00:00:00' FOR [created];

EXECUTE "#removeDefault" "#__contact_details", 'modified';
ALTER TABLE [#__contact_details] ALTER COLUMN [modified] [datetime2](0) NOT NULL;
ALTER TABLE [#__contact_details] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified];

EXECUTE "#removeDefault" "#__contact_details", 'publish_up';
ALTER TABLE [#__contact_details] ALTER COLUMN [publish_up] [datetime2](0) NOT NULL;
ALTER TABLE [#__contact_details] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_up];

EXECUTE "#removeDefault" "#__contact_details", 'publish_down';
ALTER TABLE [#__contact_details] ALTER COLUMN [publish_down] [datetime2](0) NOT NULL;
ALTER TABLE [#__contact_details] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_down];

EXECUTE "#removeDefault" "#__content", 'created';
ALTER TABLE [#__content] ALTER COLUMN [created] [datetime2](0) NOT NULL;
ALTER TABLE [#__content] ADD DEFAULT '1900-01-01 00:00:00' FOR [created];

EXECUTE "#removeDefault" "#__content", 'modified';
ALTER TABLE [#__content] ALTER COLUMN [modified] [datetime2](0) NOT NULL;
ALTER TABLE [#__content] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified];

EXECUTE "#removeDefault" "#__content", 'checked_out_time';
ALTER TABLE [#__content] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__content] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__content", 'publish_up';
ALTER TABLE [#__content] ALTER COLUMN [publish_up] [datetime2](0) NOT NULL;
ALTER TABLE [#__content] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_up];

EXECUTE "#removeDefault" "#__content", 'publish_down';
ALTER TABLE [#__content] ALTER COLUMN [publish_down] [datetime2](0) NOT NULL;
ALTER TABLE [#__content] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_down];

DROP INDEX [idx_date_id] ON [#__contentitem_tag_map];
EXECUTE "#removeDefault" "#__contentitem_tag_map", 'tag_date';
ALTER TABLE [#__contentitem_tag_map] ALTER COLUMN [tag_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__contentitem_tag_map] ADD DEFAULT '1900-01-01 00:00:00' FOR [tag_date];
CREATE NONCLUSTERED INDEX [idx_date_id2] ON [#__contentitem_tag_map](
	[tag_date] ASC,
	[tag_id] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

EXECUTE "#removeDefault" "#__extensions", 'checked_out_time';
ALTER TABLE [#__extensions] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__extensions] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__fields", 'checked_out_time';
ALTER TABLE [#__fields] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__fields] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__fields", 'created_time';
ALTER TABLE [#__fields] ALTER COLUMN [created_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__fields] ADD DEFAULT '1900-01-01 00:00:00' FOR [created_time];

EXECUTE "#removeDefault" "#__fields", 'modified_time';
ALTER TABLE [#__fields] ALTER COLUMN [modified_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__fields] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified_time];

EXECUTE "#removeDefault" "#__fields_groups", 'checked_out_time';
ALTER TABLE [#__fields_groups] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__fields_groups] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__fields_groups", 'created';
ALTER TABLE [#__fields_groups] ALTER COLUMN [created] [datetime2](0) NOT NULL;
ALTER TABLE [#__fields_groups] ADD DEFAULT '1900-01-01 00:00:00' FOR [created];

EXECUTE "#removeDefault" "#__fields_groups", 'modified';
ALTER TABLE [#__fields_groups] ALTER COLUMN [modified] [datetime2](0) NOT NULL;
ALTER TABLE [#__fields_groups] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified];

EXECUTE "#removeDefault" "#__finder_filters", 'created';
ALTER TABLE [#__finder_filters] ALTER COLUMN [created] [datetime2](0) NOT NULL;
ALTER TABLE [#__finder_filters] ADD DEFAULT '1900-01-01 00:00:00' FOR [created];

EXECUTE "#removeDefault" "#__finder_filters", 'modified';
ALTER TABLE [#__finder_filters] ALTER COLUMN [modified] [datetime2](0) NOT NULL;
ALTER TABLE [#__finder_filters] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified];

EXECUTE "#removeDefault" "#__finder_filters", 'checked_out_time';
ALTER TABLE [#__finder_filters] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__finder_filters] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__finder_links", 'indexdate';
ALTER TABLE [#__finder_links] ALTER COLUMN [indexdate] [datetime2](0) NOT NULL;
ALTER TABLE [#__finder_links] ADD DEFAULT '1900-01-01 00:00:00' FOR [indexdate];

EXECUTE "#removeDefault" "#__finder_links", 'publish_start_date';
ALTER TABLE [#__finder_links] ALTER COLUMN [publish_start_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__finder_links] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_start_date];

EXECUTE "#removeDefault" "#__finder_links", 'publish_end_date';
ALTER TABLE [#__finder_links] ALTER COLUMN [publish_end_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__finder_links] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_end_date];

EXECUTE "#removeDefault" "#__finder_links", 'start_date';
ALTER TABLE [#__finder_links] ALTER COLUMN [start_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__finder_links] ADD DEFAULT '1900-01-01 00:00:00' FOR [start_date];

EXECUTE "#removeDefault" "#__finder_links", 'end_date';
ALTER TABLE [#__finder_links] ALTER COLUMN [end_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__finder_links] ADD DEFAULT '1900-01-01 00:00:00' FOR [end_date];

EXECUTE "#removeDefault" "#__menu", 'checked_out_time';
ALTER TABLE [#__menu] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__menu] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__messages", 'date_time';
ALTER TABLE [#__messages] ALTER COLUMN [date_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__messages] ADD DEFAULT '1900-01-01 00:00:00' FOR [date_time];

EXECUTE "#removeDefault" "#__modules", 'checked_out_time';
ALTER TABLE [#__modules] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__modules] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__modules", 'publish_up';
ALTER TABLE [#__modules] ALTER COLUMN [publish_up] [datetime2](0) NOT NULL;
ALTER TABLE [#__modules] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_up];

EXECUTE "#removeDefault" "#__modules", 'publish_down';
ALTER TABLE [#__modules] ALTER COLUMN [publish_down] [datetime2](0) NOT NULL;
ALTER TABLE [#__modules] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_down];

EXECUTE "#removeDefault" "#__newsfeeds", 'checked_out_time';
ALTER TABLE [#__newsfeeds] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__newsfeeds] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__newsfeeds", 'created';
ALTER TABLE [#__newsfeeds] ALTER COLUMN [created] [datetime2](0) NOT NULL;
ALTER TABLE [#__newsfeeds] ADD DEFAULT '1900-01-01 00:00:00' FOR [created];

EXECUTE "#removeDefault" "#__newsfeeds", 'modified';
ALTER TABLE [#__newsfeeds] ALTER COLUMN [modified] [datetime2](0) NOT NULL;
ALTER TABLE [#__newsfeeds] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified];

EXECUTE "#removeDefault" "#__newsfeeds", 'publish_up';
ALTER TABLE [#__newsfeeds] ALTER COLUMN [publish_up] [datetime2](0) NOT NULL;
ALTER TABLE [#__newsfeeds] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_up];

EXECUTE "#removeDefault" "#__newsfeeds", 'publish_down';
ALTER TABLE [#__newsfeeds] ALTER COLUMN [publish_down] [datetime2](0) NOT NULL;
ALTER TABLE [#__newsfeeds] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_down];

EXECUTE "#removeDefault" "#__redirect_links", 'created_date';
ALTER TABLE [#__redirect_links] ALTER COLUMN [created_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__redirect_links] ADD DEFAULT '1900-01-01 00:00:00' FOR [created_date];

DROP INDEX [idx_link_modifed] ON [#__redirect_links];
EXECUTE "#removeDefault" "#__redirect_links", 'modified_date';
ALTER TABLE [#__redirect_links] ALTER COLUMN [modified_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__redirect_links] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified_date];
CREATE NONCLUSTERED INDEX [idx_link_modifed2] ON [#__redirect_links](
	[modified_date] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

EXECUTE "#removeDefault" "#__tags", 'checked_out_time';
ALTER TABLE [#__tags] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__tags] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__tags", 'created_time';
ALTER TABLE [#__tags] ALTER COLUMN [created_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__tags] ADD DEFAULT '1900-01-01 00:00:00' FOR [created_time];

EXECUTE "#removeDefault" "#__tags", 'modified_time';
ALTER TABLE [#__tags] ALTER COLUMN [modified_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__tags] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified_time];

EXECUTE "#removeDefault" "#__tags", 'publish_up';
ALTER TABLE [#__tags] ALTER COLUMN [publish_up] [datetime2](0) NOT NULL;
ALTER TABLE [#__tags] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_up];

EXECUTE "#removeDefault" "#__tags", 'publish_down';
ALTER TABLE [#__tags] ALTER COLUMN [publish_down] [datetime2](0) NOT NULL;
ALTER TABLE [#__tags] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_down];

EXECUTE "#removeDefault" "#__ucm_content", 'core_checked_out_time';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__ucm_content] ADD DEFAULT '1900-01-01 00:00:00' FOR [core_checked_out_time];

DROP INDEX [idx_created_time] ON [#__ucm_content];
EXECUTE "#removeDefault" "#__ucm_content", 'core_created_time';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_created_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__ucm_content] ADD DEFAULT '1900-01-01 00:00:00' FOR [core_created_time];
CREATE NONCLUSTERED INDEX [idx_created_time2] ON [#__ucm_content](
	[core_created_time] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

DROP INDEX [idx_modified_time] ON [#__ucm_content];
EXECUTE "#removeDefault" "#__ucm_content", 'core_modified_time';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_modified_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__ucm_content] ADD DEFAULT '1900-01-01 00:00:00' FOR [core_modified_time];
CREATE NONCLUSTERED INDEX [idx_modified_time2] ON [#__ucm_content](
	[core_modified_time] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

EXECUTE "#removeDefault" "#__ucm_content", 'core_publish_up';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_publish_up] [datetime2](0) NOT NULL;
ALTER TABLE [#__ucm_content] ADD DEFAULT '1900-01-01 00:00:00' FOR [core_publish_up];

EXECUTE "#removeDefault" "#__ucm_content", 'core_publish_down';
ALTER TABLE [#__ucm_content] ALTER COLUMN [core_publish_down] [datetime2](0) NOT NULL;
ALTER TABLE [#__ucm_content] ADD DEFAULT '1900-01-01 00:00:00' FOR [core_publish_down];

DROP INDEX [idx_save_date] ON [#__ucm_history];
EXECUTE "#removeDefault" "#__ucm_history", 'save_date';
ALTER TABLE [#__ucm_history] ALTER COLUMN [save_date] [datetime2](0) NOT NULL;
ALTER TABLE [#__ucm_history] ADD DEFAULT '1900-01-01 00:00:00' FOR [save_date];
CREATE NONCLUSTERED INDEX [idx_save_date2] ON [#__ucm_history](
	[save_date] ASC
)WITH (STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, DROP_EXISTING = OFF, ONLINE = OFF);

EXECUTE "#removeDefault" "#__user_notes", 'checked_out_time';
ALTER TABLE [#__user_notes] ALTER COLUMN [checked_out_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__user_notes] ADD DEFAULT '1900-01-01 00:00:00' FOR [checked_out_time];

EXECUTE "#removeDefault" "#__user_notes", 'created_time';
ALTER TABLE [#__user_notes] ALTER COLUMN [created_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__user_notes] ADD DEFAULT '1900-01-01 00:00:00' FOR [created_time];

EXECUTE "#removeDefault" "#__user_notes", 'modified_time';
ALTER TABLE [#__user_notes] ALTER COLUMN [modified_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__user_notes] ADD DEFAULT '1900-01-01 00:00:00' FOR [modified_time];

EXECUTE "#removeDefault" "#__user_notes", 'review_time';
ALTER TABLE [#__user_notes] ALTER COLUMN [review_time] [datetime2](0) NOT NULL;
ALTER TABLE [#__user_notes] ADD DEFAULT '1900-01-01 00:00:00' FOR [review_time];

EXECUTE "#removeDefault" "#__user_notes", 'publish_up';
ALTER TABLE [#__user_notes] ALTER COLUMN [publish_up] [datetime2](0) NOT NULL;
ALTER TABLE [#__user_notes] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_up];

EXECUTE "#removeDefault" "#__user_notes", 'publish_down';
ALTER TABLE [#__user_notes] ALTER COLUMN [publish_down] [datetime2](0) NOT NULL;
ALTER TABLE [#__user_notes] ADD DEFAULT '1900-01-01 00:00:00' FOR [publish_down];

EXECUTE "#removeDefault" "#__users", 'registerDate';
ALTER TABLE [#__users] ALTER COLUMN [registerDate] [datetime2](0) NOT NULL;
ALTER TABLE [#__users] ADD DEFAULT '1900-01-01 00:00:00' FOR [registerDate];

EXECUTE "#removeDefault" "#__users", 'lastvisitDate';
ALTER TABLE [#__users] ALTER COLUMN [lastvisitDate] [datetime2](0) NOT NULL;
ALTER TABLE [#__users] ADD DEFAULT '1900-01-01 00:00:00' FOR [lastvisitDate];

EXECUTE "#removeDefault" "#__users", 'lastResetTime';
ALTER TABLE [#__users] ALTER COLUMN [lastResetTime] [datetime2](0) NOT NULL;
ALTER TABLE [#__users] ADD DEFAULT '1900-01-01 00:00:00' FOR [lastResetTime];

DROP PROCEDURE "#removeDefault";
