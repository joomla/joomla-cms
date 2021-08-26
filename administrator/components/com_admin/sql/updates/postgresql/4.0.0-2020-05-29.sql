-- From 4.0.0-2020-04-11.sql
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "checked_out", "checked_out_time", "ordering", "state") VALUES
(0, 'plg_quickicon_downloadkey', 'plugin', 'downloadkey', 'quickicon', 0, 1, 1, 0, 1, '', '', '', 0, NULL, 0, 0);

-- From 4.0.0-2020-04-16.sql
INSERT INTO "#__mail_templates" ("template_id", "language", "subject", "body", "htmlbody", "attachments", "params") VALUES
('com_contact.mail', '', 'COM_CONTACT_ENQUIRY_SUBJECT', 'COM_CONTACT_ENQUIRY_TEXT', '', '', '{"tags":["sitename","name","email","subject","body","url","customfields"]}'),
('com_contact.mail.copy', '', 'COM_CONTACT_COPYSUBJECT_OF', 'COM_CONTACT_COPYTEXT_OF', '', '', '{"tags":["sitename","name","email","subject","body","url","customfields"]}'),
('com_users.massmail.mail', '', 'COM_USERS_MASSMAIL_MAIL_SUBJECT', 'COM_USERS_MASSMAIL_MAIL_BODY', '', '', '{"tags":["subject","body","subjectprefix","bodysuffix"]}'),
('com_users.password_reset', '', 'COM_USERS_EMAIL_PASSWORD_RESET_SUBJECT', 'COM_USERS_EMAIL_PASSWORD_RESET_BODY', '', '', '{"tags":["name","email","sitename","link_text","link_html","token"]}'),
('com_users.reminder', '', 'COM_USERS_EMAIL_USERNAME_REMINDER_SUBJECT', 'COM_USERS_EMAIL_USERNAME_REMINDER_BODY', '', '', '{"tags":["name","username","sitename","email","link_text","link_html"]}'),
('plg_system_updatenotification.mail', '', 'PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_SUBJECT', 'PLG_SYSTEM_UPDATENOTIFICATION_EMAIL_BODY', '', '', '{"tags":["newversion","curversion","sitename","url","link","releasenews"]}'),
('plg_user_joomla.mail', '', 'PLG_USER_JOOMLA_NEW_USER_EMAIL_SUBJECT', 'PLG_USER_JOOMLA_NEW_USER_EMAIL_BODY', '', '', '{"tags":["name","sitename","url","username","password","email"]}');

-- From 4.0.0-2020-05-21.sql
-- Renaming table
ALTER TABLE "#__ucm_history" RENAME TO "#__history";
-- Rename ucm_item_id to item_id as the new primary identifier for the original content item
ALTER TABLE "#__history" RENAME "ucm_item_id" TO "item_id";
ALTER TABLE "#__history" ALTER COLUMN "item_id" TYPE character varying(50);
ALTER TABLE "#__history" ALTER COLUMN "item_id" SET NOT NULL;
ALTER TABLE "#__history" ALTER COLUMN "item_id" DROP DEFAULT;

-- Extend the original field content with the alias of the content type
UPDATE "#__history" AS h SET "item_id" = CONCAT(c."type_alias", '.', "item_id") FROM "#__content_types" AS c WHERE h."ucm_type_id" = c."type_id";

-- Now we don't need the ucm_type_id anymore and drop it.
ALTER TABLE "#__history" DROP COLUMN "ucm_type_id";
ALTER TABLE "#__history" ALTER COLUMN "save_date" DROP DEFAULT;

-- From 4.0.0-2020-05-29.sql
ALTER TABLE "#__extensions" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__extensions" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__menu" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__menu" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__modules" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__modules" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__tags" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__tags" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__update_sites" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__update_sites" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__user_notes" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__user_notes" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__workflows" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__workflows" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__workflow_stages" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__workflow_stages" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__workflow_transitions" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__workflow_transitions" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__banners" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__banners" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__banner_clients" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__banner_clients" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__contact_details" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__contact_details" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__content" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__content" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__finder_filters" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__finder_filters" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__newsfeeds" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__categories" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__categories" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__fields" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__fields" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__fields_groups" ALTER COLUMN "checked_out" DROP DEFAULT;
ALTER TABLE "#__fields_groups" ALTER COLUMN "checked_out" DROP NOT NULL;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_checked_out_user_id" DROP DEFAULT;
ALTER TABLE "#__ucm_content" ALTER COLUMN "core_checked_out_user_id" DROP NOT NULL;

UPDATE "#__extensions" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__menu" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__modules" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__tags" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__update_sites" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__user_notes" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__workflows" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__workflow_stages" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__workflow_transitions" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__banners" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__banner_clients" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__contact_details" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__content" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__finder_filters" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__newsfeeds" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__categories" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__fields" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__fields_groups" SET "checked_out" = null WHERE "checked_out" = 0;
UPDATE "#__ucm_content" SET "core_checked_out_user_id" = null WHERE "core_checked_out_user_id" = 0;
