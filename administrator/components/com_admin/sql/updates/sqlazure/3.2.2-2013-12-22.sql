ALTER TABLE [#__update_sites] ADD [extra_query] [nvarchar](1000) NULL DEFAULT '';
ALTER TABLE [#__updates] ADD [extra_query] [nvarchar](1000) NULL DEFAULT '';

INSERT [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 106, 'PHPass', 'library', 'phpass', '', 0, 1, 1, 1, '', '', '', '', 0, '1900-01-01 00:00:00', 0, 0;
