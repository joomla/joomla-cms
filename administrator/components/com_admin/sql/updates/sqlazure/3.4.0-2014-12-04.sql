SET IDENTITY_INSERT [#__extensions]  ON;

INSERT [#__extensions] ([extension_id], [name], [type], [element], [folder], [client_id], [enabled], [access], [protected], [manifest_cache], [params], [custom_data], [system_data], [checked_out], [checked_out_time], [ordering], [state])
SELECT 452, 'plg_captcha_nocaptcha', 'plugin', 'nocaptcha', 'captcha', 0, 0, 1, 0, '', '{"public_key":"","private_key":"","theme":"light"}', '', '', 0, '1900-01-01 00:00:00', 0, 0;

SET IDENTITY_INSERT [#__extensions]  OFF;
