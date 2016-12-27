-- Smart Search default dates.  Smart Search add terms_count column to links table.
ALTER TABLE [#__finder_links]
 CHANGE [indexdate] [indexdate] DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
 CHANGE [publish_start_date] [publish_start_date] DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
 CHANGE [publish_end_date] [publish_end_date] DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
 CHANGE [start_date] [start_date] DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
 CHANGE [end_date] [end_date] DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00';
ALTER TABLE [#__finder_links] ADD [terms_count] INT NOT NULL DEFAULT '0' AFTER [object];
