ALTER TABLE [#__template_styles] ALTER COLUMN [home] nvarchar(7) NOT NULL;
ALTER TABLE [#__template_styles] ADD DEFAULT ('0') FOR [home];
