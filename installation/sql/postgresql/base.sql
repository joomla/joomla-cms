--
-- Table structure for table `#__assets`
--

CREATE TABLE IF NOT EXISTS "#__assets" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer NOT NULL,
  "name" varchar(50) NOT NULL,
  "title" varchar(100) NOT NULL,
  "rules" varchar(5120) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__assets_idx_asset_name" UNIQUE ("name")
);
CREATE INDEX "#__assets_idx_lft_rgt" ON "#__assets" ("lft", "rgt");
CREATE INDEX "#__assets_idx_parent_id" ON "#__assets" ("parent_id");

COMMENT ON COLUMN "#__assets"."id" IS 'Primary Key';
COMMENT ON COLUMN "#__assets"."parent_id" IS 'Nested set parent.';
COMMENT ON COLUMN "#__assets"."lft" IS 'Nested set lft.';
COMMENT ON COLUMN "#__assets"."rgt" IS 'Nested set rgt.';
COMMENT ON COLUMN "#__assets"."level" IS 'The cached level in the nested tree.';
COMMENT ON COLUMN "#__assets"."name" IS 'The unique name for the asset.';
COMMENT ON COLUMN "#__assets"."title" IS 'The descriptive title for the asset.';
COMMENT ON COLUMN "#__assets"."rules" IS 'JSON encoded access control.';

--
-- Dumping data for table `#__assets`
--

INSERT INTO "#__assets" ("id", "parent_id", "lft", "rgt", "level", "name", "title", "rules") VALUES
(1, 0, 0, 183, 0, 'root.1', 'Root Asset', '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.api":{"8":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'),
(2, 1, 1, 2, 1, 'com_admin', 'com_admin', '{}'),
(3, 1, 3, 6, 1, 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(4, 1, 7, 8, 1, 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(5, 1, 9, 10, 1, 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(6, 1, 11, 12, 1, 'com_config', 'com_config', '{}'),
(7, 1, 13, 16, 1, 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(8, 1, 17, 38, 1, 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.edit":{"4":1},"core.edit.state":{"5":1},"core.execute.transition":{"6":1,"5":1}}'),
(9, 1, 39, 40, 1, 'com_cpanel', 'com_cpanel', '{}'),
(10, 1, 41, 42, 1, 'com_installer', 'com_installer', '{"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'),
(11, 1, 43, 46, 1, 'com_languages', 'com_languages', '{"core.admin":{"7":1}}'),
(12, 11, 44, 45, 2, 'com_languages.language.1', 'English (en-GB)', '{}'),
(13, 1, 47, 48, 1, 'com_login', 'com_login', '{}'),
(14, 1, 49, 50, 1, 'com_mails', 'com_mails', '{}'),
(15, 1, 51, 52, 1, 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'),
(16, 1, 53, 56, 1, 'com_menus', 'com_menus', '{"core.admin":{"7":1}}'),
(17, 1, 57, 58, 1, 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(18, 1, 59, 132, 1, 'com_modules', 'com_modules', '{"core.admin":{"7":1}}'),
(19, 1, 133, 136, 1, 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(20, 1, 137, 138, 1, 'com_plugins', 'com_plugins', '{"core.admin":{"7":1}}'),
(21, 1, 139, 140, 1, 'com_redirect', 'com_redirect', '{"core.admin":{"7":1}}'),
(23, 1, 141, 142, 1, 'com_templates', 'com_templates', '{"core.admin":{"7":1}}'),
(24, 1, 147, 150, 1, 'com_users', 'com_users', '{"core.admin":{"7":1}}'),
(26, 1, 151, 152, 1, 'com_wrapper', 'com_wrapper', '{}'),
(27, 8, 18, 19, 2, 'com_content.category.2', 'Uncategorised', '{}'),
(28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncategorised', '{}'),
(29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncategorised', '{}'),
(30, 19, 134, 135, 2, 'com_newsfeeds.category.5', 'Uncategorised', '{}'),
(32, 24, 148, 149, 2, 'com_users.category.7', 'Uncategorised', '{}'),
(33, 1, 153, 154, 1, 'com_finder', 'com_finder', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(34, 1, 155, 156, 1, 'com_joomlaupdate', 'com_joomlaupdate', '{}'),
(35, 1, 157, 158, 1, 'com_tags', 'com_tags', '{}'),
(36, 1, 159, 160, 1, 'com_contenthistory', 'com_contenthistory', '{}'),
(37, 1, 161, 162, 1, 'com_ajax', 'com_ajax', '{}'),
(38, 1, 163, 164, 1, 'com_postinstall', 'com_postinstall', '{}'),
(39, 18, 60, 61, 2, 'com_modules.module.1', 'Main Menu', '{}'),
(40, 18, 62, 63, 2, 'com_modules.module.2', 'Login', '{}'),
(41, 18, 64, 65, 2, 'com_modules.module.3', 'Popular Articles', '{}'),
(42, 18, 66, 67, 2, 'com_modules.module.4', 'Recently Added Articles', '{}'),
(43, 18, 68, 69, 2, 'com_modules.module.8', 'Toolbar', '{}'),
(44, 18, 70, 71, 2, 'com_modules.module.9', 'Notifications', '{}'),
(45, 18, 72, 73, 2, 'com_modules.module.10', 'Logged-in Users', '{}'),
(46, 18, 74, 75, 2, 'com_modules.module.12', 'Admin Menu', '{}'),
(49, 18, 80, 81, 2, 'com_modules.module.15', 'Title', '{}'),
(50, 18, 82, 83, 2, 'com_modules.module.16', 'Login Form', '{}'),
(51, 18, 84, 85, 2, 'com_modules.module.17', 'Breadcrumbs', '{}'),
(52, 18, 86, 87, 2, 'com_modules.module.79', 'Multilanguage status', '{}'),
(53, 18, 90, 91, 2, 'com_modules.module.86', 'Joomla Version', '{}'),
(54, 16, 54, 55, 2, 'com_menus.menu.1', 'Main Menu', '{}'),
(55, 18, 94, 95, 2, 'com_modules.module.87', 'Sample Data', '{}'),
(56, 8, 20, 37, 2, 'com_content.workflow.1', 'COM_WORKFLOW_BASIC_WORKFLOW', '{}'),
(57, 56, 21, 22, 3, 'com_content.stage.1', 'COM_WORKFLOW_BASIC_STAGE', '{}'),
(58, 56, 23, 24, 3, 'com_content.transition.1', 'Unpublish', '{}'),
(59, 56, 25, 26, 3, 'com_content.transition.2', 'Publish', '{}'),
(60, 56, 27, 28, 3, 'com_content.transition.3', 'Trash', '{}'),
(61, 56, 29, 30, 3, 'com_content.transition.4', 'Archive', '{}'),
(62, 56, 31, 32, 3, 'com_content.transition.5', 'Feature', '{}'),
(63, 56, 33, 34, 3, 'com_content.transition.6', 'Unfeature', '{}'),
(64, 56, 35, 36, 3, 'com_content.transition.7', 'Publish & Feature', '{}'),
(65, 1, 143, 144, 1, 'com_privacy', 'com_privacy', '{}'),
(66, 1, 145, 146, 1, 'com_actionlogs', 'com_actionlogs', '{}'),
(67, 18, 76, 77, 2, 'com_modules.module.88', 'Latest Actions', '{}'),
(68, 18, 78, 79, 2, 'com_modules.module.89', 'Privacy Dashboard', '{}'),
(70, 18, 88, 89, 2, 'com_modules.module.103', 'Site', '{}'),
(71, 18, 92, 93, 2, 'com_modules.module.104', 'System', '{}'),
(72, 18, 96, 97, 2, 'com_modules.module.91', 'System Dashboard', '{}'),
(73, 18, 98, 99, 2, 'com_modules.module.92', 'Content Dashboard', '{}'),
(74, 18, 100, 101, 2, 'com_modules.module.93', 'Menus Dashboard', '{}'),
(75, 18, 102, 103, 2, 'com_modules.module.94', 'Components Dashboard', '{}'),
(76, 18, 104, 105, 2, 'com_modules.module.95', 'Users Dashboard', '{}'),
(77, 18, 106, 107, 2, 'com_modules.module.99', 'Frontend Link', '{}'),
(78, 18, 108, 109, 2, 'com_modules.module.100', 'Messages', '{}'),
(79, 18, 110, 111, 2, 'com_modules.module.101', 'Post Install Messages', '{}'),
(80, 18, 112, 113, 2, 'com_modules.module.102', 'User Status', '{}'),
(82, 18, 114, 115, 2, 'com_modules.module.105', '3rd Party', '{}'),
(83, 18, 116, 117, 2, 'com_modules.module.106', 'Help Dashboard', '{}'),
(84, 18, 118, 119, 2, 'com_modules.module.107', 'Privacy Requests', '{}'),
(85, 18, 120, 121, 2, 'com_modules.module.108', 'Privacy Status', '{}'),
(86, 18, 122, 123, 2, 'com_modules.module.96', 'Popular Articles', '{}'),
(87, 18, 124, 125, 2, 'com_modules.module.97', 'Recently Added Articles', '{}'),
(88, 18, 126, 127, 2, 'com_modules.module.98', 'Logged-in Users', '{}'),
(89, 18, 128, 129, 2, 'com_modules.module.90', 'Login Support', '{}'),
(90, 1, 165, 166, 1, 'com_scheduler', 'com_scheduler', '{}'),
(91, 1, 167, 168, 1, 'com_associations', 'com_associations', '{}'),
(92, 1, 169, 170, 1, 'com_categories', 'com_categories', '{}'),
(93, 1, 171, 172, 1, 'com_fields', 'com_fields', '{}'),
(94, 1, 173, 174, 1, 'com_workflow', 'com_workflow', '{}'),
(95, 1, 175, 176, 1, 'com_guidedtours', 'com_guidedtours', '{}'),
(96, 18, 130, 131, 2, 'com_modules.module.109', 'Guided Tours', '{}'),
(97, 1, 177, 178, 1, 'com_scheduler.task.1', 'com_scheduler.task.1', '{}'),
(98, 1, 179, 180, 1, 'com_scheduler.task.2', 'com_scheduler.task.2', '{}'),
(99, 1, 181, 182, 1, 'com_scheduler.task.3', 'com_scheduler.task.3', '{}');

SELECT setval('#__assets_id_seq', 100, false);

--
-- Table structure for table `#__extensions`
--

CREATE TABLE IF NOT EXISTS "#__extensions" (
  "extension_id" serial NOT NULL,
  "package_id" bigint DEFAULT 0 NOT NULL,
  "name" varchar(100) NOT NULL,
  "type" varchar(20) NOT NULL,
  "element" varchar(100) NOT NULL,
  "changelogurl" text,
  "folder" varchar(100) NOT NULL,
  "client_id" smallint NOT NULL,
  "enabled" smallint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 1 NOT NULL,
  "protected" smallint DEFAULT 0 NOT NULL,
  "locked" smallint DEFAULT 0 NOT NULL,
  "manifest_cache" text NOT NULL,
  "params" text NOT NULL,
  "custom_data" text NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "ordering" bigint DEFAULT 0,
  "state" bigint DEFAULT 0,
  "note" varchar(255),
  PRIMARY KEY ("extension_id")
);
CREATE INDEX "#__extensions_element_clientid" ON "#__extensions" ("element", "client_id");
CREATE INDEX "#__extensions_element_folder_clientid" ON "#__extensions" ("element", "folder", "client_id");
CREATE INDEX "#__extensions_extension" ON "#__extensions" ("type", "element", "folder", "client_id");

COMMENT ON COLUMN "#__extensions"."protected" IS 'Flag to indicate if the extension is protected. Protected extensions cannot be disabled.';
COMMENT ON COLUMN "#__extensions"."locked" IS 'Flag to indicate if the extension is locked. Locked extensions cannot be uninstalled.';
COMMENT ON COLUMN "#__extensions"."package_id" IS 'Parent package ID for extensions installed as a package.';

-- Components
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'com_wrapper', 'component', 'com_wrapper', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'com_admin', 'component', 'com_admin', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_banners', 'component', 'com_banners', '', 1, 1, 1, 0, 1, '', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":"","save_history":"1","history_limit":10}', '', 0, 0),
(0, 'com_cache', 'component', 'com_cache', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_categories', 'component', 'com_categories', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_checkin', 'component', 'com_checkin', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_contact', 'component', 'com_contact', '', 1, 1, 1, 0, 1, '', '{"contact_layout":"_:default","show_contact_category":"hide","save_history":"1","history_limit":10,"show_contact_list":"0","presentation_style":"sliders","show_tags":"1","show_info":"1","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_image":"1","show_misc":"1","image":"","allow_vcard":"0","show_articles":"0","articles_display_num":"10","show_profile":"0","show_user_custom_fields":["-1"],"show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"0","maxLevel":"-1","show_subcat_desc":"1","show_empty_categories":"0","show_cat_items":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_subcat_desc_cat":"1","show_empty_categories_cat":"0","show_cat_items_cat":"1","filter_field":"0","show_pagination_limit":"0","show_headings":"1","show_image_heading":"0","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_pagination":"2","show_pagination_results":"1","initial_sort":"ordering","captcha":"","show_email_form":"1","show_email_copy":"0","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_feed_link":"1","sef_ids":1,"custom_fields_enable":"1"}', '', 0, 0),
(0, 'com_cpanel', 'component', 'com_cpanel', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_installer', 'component', 'com_installer', '', 1, 1, 1, 1, 1, '', '{"cachetimeout":"6","minimum_stability":"4"}', '', 0, 0),
(0, 'com_languages', 'component', 'com_languages', '', 1, 1, 1, 1, 1, '', '{"administrator":"en-GB","site":"en-GB"}', '', 0, 0),
(0, 'com_login', 'component', 'com_login', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_media', 'component', 'com_media', '', 1, 1, 0, 1, 1, '', '{"upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","restrict_uploads_extensions":"bmp,gif,jpg,jpeg,png,ico,webp,avif,mp3,m4a,mp4a,ogg,mp4,mp4v,mpeg,mov,odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv","check_mime":"1","image_extensions":"bmp,gif,jpg,png,jpeg,webp,avif","audio_extensions":"mp3,m4a,mp4a,ogg","video_extensions":"mp4,mp4v,mpeg,mov,webm","doc_extensions":"odg,odp,ods,odt,pdf,ppt,txt,xcf,xls,csv","ignore_extensions":"","upload_mime":"image\\/jpeg,image\\/gif,image\\/png,image\\/bmp,image\\/webp,image\\/avif,audio\\/ogg,audio\\/mpeg,audio\\/mp4,video\\/mp4,video\\/webm,video\\/mpeg,video\\/quicktime,application\\/msword,application\\/excel,application\\/pdf,application\\/powerpoint,text\\/plain,application\\/x-zip"}', '', 0, 0),
(0, 'com_menus', 'component', 'com_menus', '', 1, 1, 1, 1, 1, '', '{"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":""}', '', 0, 0),
(0, 'com_messages', 'component', 'com_messages', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_modules', 'component', 'com_modules', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_newsfeeds', 'component', 'com_newsfeeds', '', 1, 1, 1, 0, 1, '', '{"newsfeed_layout":"_:default","save_history":"1","history_limit":5,"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_character_count":"0","feed_display_order":"des","float_first":"right","float_second":"right","show_tags":"1","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"1","maxLevel":"-1","show_empty_categories":"0","show_subcat_desc":"1","show_cat_items":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_items_cat":"1","filter_field":"1","show_pagination_limit":"1","show_headings":"1","show_articles":"0","show_link":"1","show_pagination":"1","show_pagination_results":"1","sef_ids":1}', '', 0, 0),
(0, 'com_plugins', 'component', 'com_plugins', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_templates', 'component', 'com_templates', '', 1, 1, 1, 1, 1, '', '{"template_positions_display":"0","upload_limit":"10","image_formats":"gif,bmp,jpg,jpeg,png,webp","source_formats":"txt,less,ini,xml,js,php,css,scss,sass,json","font_formats":"woff,woff2,ttf,otf","compressed_formats":"zip","difference":"SideBySide"}', '', 0, 0),
(0, 'com_content', 'component', 'com_content', '', 1, 1, 0, 1, 1, '', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","info_block_position":"0","info_block_show_title":"1","show_category":"1","link_category":"1","show_parent_category":"0","link_parent_category":"0","show_associations":"0","flags":"1","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_readmore":"1","show_readmore_title":"1","readmore_limit":100,"show_tags":"1","record_hits":"1","show_hits":"1","show_noauth":"0","urls_position":0,"captcha":"","show_publishing_options":"1","show_article_options":"1","show_configure_edit_options":"1","show_permissions":"1","show_associations_edit":"1","save_history":"1","history_limit":10,"show_urls_images_frontend":"0","show_urls_images_backend":"1","targeta":0,"targetb":0,"targetc":0,"float_intro":"left","float_fulltext":"left","category_layout":"_:blog","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_category_heading_title_text":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":1,"blog_class_leading":"","num_intro_articles":4,"blog_class":"","num_columns":1,"multi_column_order":"0","num_links":4,"show_subcategory_content":"0","link_intro_image":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","display_num":"10","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_featured":"show","show_feed_link":"1","feed_summary":"0","feed_show_readmore":"0","sef_ids":1,"custom_fields_enable":"1","workflow_enabled":"0"}', '', 0, 0),
(0, 'com_config', 'component', 'com_config', '', 1, 1, 0, 1, 1, '', '{"filters":{"1":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"9":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"2":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"NONE","filter_tags":"","filter_attributes":""}}}', '', 0, 0),
(0, 'com_redirect', 'component', 'com_redirect', '', 1, 1, 0, 0, 1, '', '', '', 0, 0),
(0, 'com_users', 'component', 'com_users', '', 1, 1, 0, 1, 1, '', '{"allowUserRegistration":"0","new_usertype":"2","guest_usergroup":"9","sendpassword":"0","useractivation":"2","mail_to_admin":"1","captcha":"","frontend_userparams":"1","site_language":"0","change_login_name":"0","reset_count":"10","reset_time":"1","minimum_length":"12","minimum_integers":"0","minimum_symbols":"0","minimum_uppercase":"0","save_history":"1","history_limit":5,"mailSubjectPrefix":"","mailBodySuffix":""}', '', 0, 0),
(0, 'com_finder', 'component', 'com_finder', '', 1, 1, 0, 0, 1, '', '{"enabled":"0","show_description":"1","description_length":255,"allow_empty_query":"0","show_url":"1","show_autosuggest":"1","show_suggested_query":"1","show_explained_query":"1","show_advanced":"1","show_advanced_tips":"1","expand_advanced":"0","show_date_filters":"0","sort_order":"relevance","sort_direction":"desc","highlight_terms":"1","opensearch_name":"","opensearch_description":"","batch_size":"50","title_multiplier":"1.7","text_multiplier":"0.7","meta_multiplier":"1.2","path_multiplier":"2.0","misc_multiplier":"0.3","stem":"1","stemmer":"snowball","enable_logging":"0"}', '', 0, 0),
(0, 'com_joomlaupdate', 'component', 'com_joomlaupdate', '', 1, 1, 0, 1, 1, '', '{"updatesource":"default","customurl":""}', '', 0, 0),
(0, 'com_tags', 'component', 'com_tags', '', 1, 1, 1, 0, 1, '', '{"tag_layout":"_:default","save_history":"1","history_limit":5,"show_tag_title":"0","tag_list_show_tag_image":"0","tag_list_show_tag_description":"0","tag_list_image":"","tag_list_orderby":"title","tag_list_orderby_direction":"ASC","show_headings":"0","tag_list_show_date":"0","tag_list_show_item_image":"0","tag_list_show_item_description":"0","tag_list_item_maximum_characters":0,"return_any_or_all":"1","include_children":"0","maximum":200,"tag_list_language_filter":"all","tags_layout":"_:default","all_tags_orderby":"title","all_tags_orderby_direction":"ASC","all_tags_show_tag_image":"0","all_tags_show_tag_description":"0","all_tags_tag_maximum_characters":20,"all_tags_show_tag_hits":"0","filter_field":"1","show_pagination_limit":"1","show_pagination":"2","show_pagination_results":"1","tag_field_ajax_mode":"1","show_feed_link":"1"}', '', 0, 0),
(0, 'com_contenthistory', 'component', 'com_contenthistory', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'com_ajax', 'component', 'com_ajax', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_postinstall', 'component', 'com_postinstall', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_fields', 'component', 'com_fields', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'com_associations', 'component', 'com_associations', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'com_privacy', 'component', 'com_privacy', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'com_actionlogs', 'component', 'com_actionlogs', '', 1, 1, 1, 0, 1, '', '{"ip_logging":0,"csv_delimiter":",","loggable_extensions":["com_banners","com_cache","com_categories","com_checkin","com_config","com_contact","com_content","com_fields","com_installer","com_media","com_menus","com_messages","com_modules","com_newsfeeds","com_plugins","com_redirect","com_scheduler","com_tags","com_templates","com_users"]}', '', 0, 0),
(0, 'com_workflow', 'component', 'com_workflow', '', 1, 1, 0, 1, 1, '', '{}', '', 0, 0),
(0, 'com_mails', 'component', 'com_mails', '', 1, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'com_scheduler', 'component', 'com_scheduler', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0),
(0, 'com_guidedtours', 'component', 'com_guidedtours', '', 1, 1, 0, 0, 1, '', '{}', '', 0, 0);

-- Libraries
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'Joomla! Platform', 'library', 'joomla', '', 0, 1, 1, 1, 1, '', '', '', 0, 0),
(0, 'PHPass', 'library', 'phpass', '', 0, 1, 1, 1, 1, '', '', '', 0, 0);

-- Modules: Site
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'mod_articles_archive', 'module', 'mod_articles_archive', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_articles_latest', 'module', 'mod_articles_latest', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_articles_popular', 'module', 'mod_articles_popular', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_banners', 'module', 'mod_banners', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_custom', 'module', 'mod_custom', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_feed', 'module', 'mod_feed', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_footer', 'module', 'mod_footer', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_login', 'module', 'mod_login', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_menu', 'module', 'mod_menu', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_articles_news', 'module', 'mod_articles_news', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_random_image', 'module', 'mod_random_image', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_related_items', 'module', 'mod_related_items', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_stats', 'module', 'mod_stats', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_syndicate', 'module', 'mod_syndicate', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_users_latest', 'module', 'mod_users_latest', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_whosonline', 'module', 'mod_whosonline', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_wrapper', 'module', 'mod_wrapper', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_articles_category', 'module', 'mod_articles_category', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_articles_categories', 'module', 'mod_articles_categories', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_languages', 'module', 'mod_languages', '', 0, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_finder', 'module', 'mod_finder', '', 0, 1, 0, 0, 1, '', '', '', 0, 0);

-- Modules: Administrator
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'mod_custom', 'module', 'mod_custom', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_feed', 'module', 'mod_feed', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_latest', 'module', 'mod_latest', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_logged', 'module', 'mod_logged', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_login', 'module', 'mod_login', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_loginsupport', 'module', 'mod_loginsupport', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_menu', 'module', 'mod_menu', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_popular', 'module', 'mod_popular', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_quickicon', 'module', 'mod_quickicon', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_frontend', 'module', 'mod_frontend', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_messages', 'module', 'mod_messages', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_post_installation_messages', 'module', 'mod_post_installation_messages', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_user', 'module', 'mod_user', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_title', 'module', 'mod_title', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_toolbar', 'module', 'mod_toolbar', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', 1, 1, 1, 0, 1, '', '{"cache":"0"}', '', 0, 0),
(0, 'mod_version', 'module', 'mod_version', '', 1, 1, 1, 0, 1, '', '{"cache":"0"}', '', 0, 0),
(0, 'mod_stats_admin', 'module', 'mod_stats_admin', '', 1, 1, 1, 0, 1, '', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', 0, 0),
(0, 'mod_tags_popular', 'module', 'mod_tags_popular', '', 0, 1, 1, 0, 1, '', '{"maximum":"5","timeframe":"alltime","owncache":"1"}', '', 0, 0),
(0, 'mod_tags_similar', 'module', 'mod_tags_similar', '', 0, 1, 1, 0, 1, '', '{"maximum":"5","matchtype":"any","owncache":"1"}', '', 0, 0),
(0, 'mod_sampledata', 'module', 'mod_sampledata', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0),
(0, 'mod_latestactions', 'module', 'mod_latestactions', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0),
(0, 'mod_privacy_dashboard', 'module', 'mod_privacy_dashboard', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0),
(0, 'mod_submenu', 'module', 'mod_submenu', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0),
(0, 'mod_privacy_status', 'module', 'mod_privacy_status', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0),
(0, 'mod_guidedtours', 'module', 'mod_guidedtours', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0);

-- Plugins
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'plg_actionlog_joomla', 'plugin', 'joomla', 'actionlog', 0, 1, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_api-authentication_basic', 'plugin', 'basic', 'api-authentication', 0, 0, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_api-authentication_token', 'plugin', 'token', 'api-authentication', 0, 1, 1, 0, 1, '', '{}', '', 2, 0),
(0, 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', 0, 1, 1, 1, 1, '', '', '', 2, 0),
(0, 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', 0, 0, 1, 0, 1, '', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', 3, 0),
(0, 'plg_behaviour_compat', 'plugin', 'compat', 'behaviour', 0, 1, 1, 0, 1, '', '{"classes_aliases":"1","es5_assets":"1"}', '', 1, 0),
(0, 'plg_behaviour_taggable', 'plugin', 'taggable', 'behaviour', 0, 1, 1, 0, 1, '', '{}', '', 2, 0),
(0, 'plg_behaviour_versionable', 'plugin', 'versionable', 'behaviour', 0, 1, 1, 0, 1, '', '{}', '', 3, 0),
(0, 'plg_content_confirmconsent', 'plugin', 'confirmconsent', 'content', 0, 0, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_content_contact', 'plugin', 'contact', 'content', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', 0, 1, 1, 0, 1, '', '{"mode":"1"}', '', 3, 0),
(0, 'plg_content_fields', 'plugin', 'fields', 'content', 0, 1, 1, 0, 1, '', '', '', 4, 0),
(0, 'plg_content_finder', 'plugin', 'finder', 'content', 0, 1, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_content_joomla', 'plugin', 'joomla', 'content', 0, 1, 1, 0, 1, '', '', '', 6, 0),
(0, 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', 0, 1, 1, 0, 1, '', '{"style":"xhtml"}', '', 7, 0),
(0, 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', 0, 1, 1, 0, 1, '', '{"title":"1","multipage_toc":"1","showall":"1"}', '', 8, 0),
(0, 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', 0, 1, 1, 0, 1, '', '{"position":"1"}', '', 9, 0),
(0, 'plg_content_vote', 'plugin', 'vote', 'content', 0, 0, 1, 0, 1, '', '', '', 10, 0),
(0, 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_editors-xtd_contact', 'plugin', 'contact', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_editors-xtd_fields', 'plugin', 'fields', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 4, 0),
(0, 'plg_editors-xtd_menu', 'plugin', 'menu', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_editors-xtd_module', 'plugin', 'module', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 6, 0),
(0, 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 7, 0),
(0, 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', 0, 1, 1, 0, 1, '', '', '', 8, 0),
(0, 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', 0, 1, 1, 0, 1, '', '{"lineNumbers":"1","lineWrapping":"1","matchTags":"1","matchBrackets":"1","marker-gutter":"1","autoCloseTags":"1","autoCloseBrackets":"1","autoFocus":"1","theme":"default","tabmode":"indent"}', '', 1, 0),
(0, 'plg_editors_none', 'plugin', 'none', 'editors', 0, 1, 1, 1, 1, '', '', '', 2, 0),
(0, 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', 0, 1, 1, 0, 1, '', '{"configuration":{"toolbars":{"2":{"toolbar1":["bold","underline","strikethrough","|","undo","redo","|","bullist","numlist","|","pastetext"]},"1":{"menu":["edit","insert","view","format","table","tools"],"toolbar1":["bold","italic","underline","strikethrough","|","alignleft","aligncenter","alignright","alignjustify","|","blocks","|","bullist","numlist","|","outdent","indent","|","undo","redo","|","link","unlink","anchor","code","|","hr","table","|","subscript","superscript","|","charmap","pastetext","preview"]},"0":{"menu":["edit","insert","view","format","table","tools"],"toolbar1":["bold","italic","underline","strikethrough","|","alignleft","aligncenter","alignright","alignjustify","|","styles","|","blocks","fontfamily","fontsize","|","searchreplace","|","bullist","numlist","|","outdent","indent","|","undo","redo","|","link","unlink","anchor","image","|","code","|","forecolor","backcolor","|","fullscreen","|","table","|","subscript","superscript","|","charmap","emoticons","media","hr","ltr","rtl","|","cut","copy","paste","pastetext","|","visualchars","visualblocks","nonbreaking","blockquote","jtemplate","|","print","preview","codesample","insertdatetime","removeformat"]}},"setoptions":{"2":{"access":["1"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"0","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""},"1":{"access":["6","2"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"0","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""},"0":{"access":["7","4","8"],"skin":"0","skin_admin":"0","mobile":"0","drag_drop":"1","path":"","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","use_config_textfilters":"0","invalid_elements":"script,applet,iframe","valid_elements":"","extended_elements":"","resizing":"1","resize_horizontal":"1","element_path":"1","wordcount":"1","image_advtab":"1","advlist":"1","autosave":"1","contextmenu":"1","custom_plugin":"","custom_button":""}}},"sets_amount":3,"html_height":"550","html_width":"750"}', '', 3, 0),
(0, 'plg_extension_finder', 'plugin', 'finder', 'extension', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_extension_joomla', 'plugin', 'joomla', 'extension', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_extension_namespacemap', 'plugin', 'namespacemap', 'extension', 0, 1, 1, 1, 1, '', '{}', '', 3, 0),
(0, 'plg_fields_calendar', 'plugin', 'calendar', 'fields', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_fields_checkboxes', 'plugin', 'checkboxes', 'fields', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_fields_color', 'plugin', 'color', 'fields', 0, 1, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_fields_editor', 'plugin', 'editor', 'fields', 0, 1, 1, 0, 1, '', '{"buttons":0,"width":"100%","height":"250px","filter":"\\\\Joomla\\\\CMS\\\\Component\\\\ComponentHelper::filterText"}', '', 4, 0),
(0, 'plg_fields_imagelist', 'plugin', 'imagelist', 'fields', 0, 1, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_fields_integer', 'plugin', 'integer', 'fields', 0, 1, 1, 0, 1, '', '{"multiple":"0","first":"1","last":"100","step":"1"}', '', 6, 0),
(0, 'plg_fields_list', 'plugin', 'list', 'fields', 0, 1, 1, 0, 1, '', '', '', 7, 0),
(0, 'plg_fields_media', 'plugin', 'media', 'fields', 0, 1, 1, 0, 1, '', '', '', 8, 0),
(0, 'plg_fields_radio', 'plugin', 'radio', 'fields', 0, 1, 1, 0, 1, '', '', '', 9, 0),
(0, 'plg_fields_sql', 'plugin', 'sql', 'fields', 0, 1, 1, 0, 1, '', '', '', 10, 0),
(0, 'plg_fields_subform', 'plugin', 'subform', 'fields', 0, 1, 1, 0, 1, '', '', '', 11, 0),
(0, 'plg_fields_text', 'plugin', 'text', 'fields', 0, 1, 1, 0, 1, '', '', '', 12, 0),
(0, 'plg_fields_textarea', 'plugin', 'textarea', 'fields', 0, 1, 1, 0, 1, '', '{"rows":10,"cols":10,"maxlength":"","filter":"\\\\Joomla\\\\CMS\\\\Component\\\\ComponentHelper::filterText"}', '', 13, 0),
(0, 'plg_fields_url', 'plugin', 'url', 'fields', 0, 1, 1, 0, 1, '', '', '', 14, 0),
(0, 'plg_fields_user', 'plugin', 'user', 'fields', 0, 1, 1, 0, 1, '', '', '', 15, 0),
(0, 'plg_fields_usergrouplist', 'plugin', 'usergrouplist', 'fields', 0, 1, 1, 0, 1, '', '', '', 16, 0),
(0, 'plg_filesystem_local', 'plugin', 'local', 'filesystem', 0, 1, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_finder_categories', 'plugin', 'categories', 'finder', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_finder_contacts', 'plugin', 'contacts', 'finder', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_finder_content', 'plugin', 'content', 'finder', 0, 1, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_finder_newsfeeds', 'plugin', 'newsfeeds', 'finder', 0, 1, 1, 0, 1, '', '', '', 4, 0),
(0, 'plg_finder_tags', 'plugin', 'tags', 'finder', 0, 1, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_installer_folderinstaller', 'plugin', 'folderinstaller', 'installer', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_installer_override', 'plugin', 'override', 'installer', 0, 1, 1, 0, 1, '', '', '', 4, 0),
(0, 'plg_installer_packageinstaller', 'plugin', 'packageinstaller', 'installer', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_installer_urlinstaller', 'plugin', 'urlinstaller', 'installer', 0, 1, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_installer_webinstaller', 'plugin', 'webinstaller', 'installer', 0, 1, 1, 0, 1, '', '{"tab_position":"1"}', '', 5, 0),
(0, 'plg_media-action_crop', 'plugin', 'crop', 'media-action', 0, 1, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_media-action_resize', 'plugin', 'resize', 'media-action', 0, 1, 1, 0, 1, '', '{}', '', 2, 0),
(0, 'plg_media-action_rotate', 'plugin', 'rotate', 'media-action', 0, 1, 1, 0, 1, '', '{}', '', 3, 0),
(0, 'plg_privacy_actionlogs', 'plugin', 'actionlogs', 'privacy', 0, 1, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_privacy_consents', 'plugin', 'consents', 'privacy', 0, 1, 1, 0, 1, '', '{}', '', 2, 0),
(0, 'plg_privacy_contact', 'plugin', 'contact', 'privacy', 0, 1, 1, 0, 1, '', '{}', '', 3, 0),
(0, 'plg_privacy_content', 'plugin', 'content', 'privacy', 0, 1, 1, 0, 1, '', '{}', '', 4, 0),
(0, 'plg_privacy_message', 'plugin', 'message', 'privacy', 0, 1, 1, 0, 1, '', '{}', '', 5, 0),
(0, 'plg_privacy_user', 'plugin', 'user', 'privacy', 0, 1, 1, 0, 1, '', '{}', '', 6, 0),
(0, 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_quickicon_overridecheck', 'plugin', 'overridecheck', 'quickicon', 0, 1, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_quickicon_downloadkey', 'plugin', 'downloadkey', 'quickicon', 0, 1, 1, 0, 1, '', '', '', 4, 0),
(0, 'plg_quickicon_privacycheck', 'plugin', 'privacycheck', 'quickicon', 0, 1, 1, 0, 1, '', '{}', '', 5, 0),
(0, 'plg_quickicon_phpversioncheck', 'plugin', 'phpversioncheck', 'quickicon', 0, 1, 1, 0, 1, '', '', '', 6, 0),
(0, 'plg_quickicon_eos', 'plugin', 'eos', 'quickicon', 0, 1, 1, 0, 1, '', '', '', 7, 0),
(0, 'plg_sampledata_blog', 'plugin', 'blog', 'sampledata', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_sampledata_multilang', 'plugin', 'multilang', 'sampledata', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_schemaorg_article', 'plugin', 'article', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 1, 0),
(0, 'plg_schemaorg_blogposting', 'plugin', 'blogposting', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 2, 0),
(0, 'plg_schemaorg_book', 'plugin', 'book', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 3, 0),
(0, 'plg_schemaorg_event', 'plugin', 'event', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 4, 0),
(0, 'plg_schemaorg_jobposting', 'plugin', 'jobposting', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 5, 0),
(0, 'plg_schemaorg_organization', 'plugin', 'organization', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 6, 0),
(0, 'plg_schemaorg_person', 'plugin', 'person', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 7, 0),
(0, 'plg_schemaorg_recipe', 'plugin', 'recipe', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 8, 0),
(0, 'plg_schemaorg_custom', 'plugin', 'custom', 'schemaorg', 0, 1, 1, 0, 0, '', '{}', '', 9, 0),
(0, 'plg_system_accessibility', 'plugin', 'accessibility', 'system', 0, 0, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_system_actionlogs', 'plugin', 'actionlogs', 'system', 0, 1, 1, 0, 1, '', '{}', '', 2, 0),
(0, 'plg_system_cache', 'plugin', 'cache', 'system', 0, 0, 1, 0, 1, '', '{"browsercache":"0","cachetime":"15"}', '', 3, 0),
(0, 'plg_system_debug', 'plugin', 'debug', 'system', 0, 1, 1, 0, 1, '', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', 4, 0),
(0, 'plg_system_fields', 'plugin', 'fields', 'system', 0, 1, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_system_highlight', 'plugin', 'highlight', 'system', 0, 1, 1, 0, 1, '', '', '', 6, 0),
(0, 'plg_system_httpheaders', 'plugin', 'httpheaders', 'system', 0, 1, 1, 0, 1, '', '{}', '', 7, 0),
(0, 'plg_system_jooa11y', 'plugin', 'jooa11y', 'system', 0, 1, 1, 0, 1, '', '', '', 8, 0),
(0, 'plg_system_languagecode', 'plugin', 'languagecode', 'system', 0, 0, 1, 0, 1, '', '', '', 9, 0),
(0, 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', 0, 0, 1, 0, 1, '', '', '', 10, 0),
(0, 'plg_system_log', 'plugin', 'log', 'system', 0, 1, 1, 0, 1, '', '', '', 11, 0),
(0, 'plg_system_logout', 'plugin', 'logout', 'system', 0, 1, 1, 0, 1, '', '', '', 12, 0),
(0, 'plg_system_privacyconsent', 'plugin', 'privacyconsent', 'system', 0, 0, 1, 0, 1, '', '{}', '', 14, 0),
(0, 'plg_system_redirect', 'plugin', 'redirect', 'system', 0, 0, 1, 0, 1, '', '', '', 15, 0),
(0, 'plg_system_remember', 'plugin', 'remember', 'system', 0, 1, 1, 0, 1, '', '', '', 16, 0),
(0, 'plg_system_schedulerunner', 'plugin', 'schedulerunner', 'system', 0, 1, 1, 0, 1, '', '{}', '', 17, 0),
(0, 'plg_system_schemaorg', 'plugin', 'schemaorg', 'system', 0, 1, 1, 0, 0, '', '{}', '', 18, 0),
(0, 'plg_system_sef', 'plugin', 'sef', 'system', 0, 1, 1, 0, 1, '', '{"domain":"","indexphp":"1","trailingslash":"0","enforcesuffix":"1","strictrouting":"1"}', '', 19, 0),
(0, 'plg_system_shortcut', 'plugin', 'shortcut', 'system', 0, 1, 1, 0, 1, '', '{}', '', 21, 0),
(0, 'plg_system_skipto', 'plugin', 'skipto', 'system', 0, 1, 1, 0, 1, '', '{}', '', 22, 0),
(0, 'plg_system_stats', 'plugin', 'stats', 'system', 0, 1, 1, 0, 1, '', '', '', 23, 0),
(0, 'plg_system_tasknotification', 'plugin', 'tasknotification', 'system', 0, 1, 1, 0, 1, '', '', '', 24, 0),
(0, 'plg_system_webauthn', 'plugin', 'webauthn', 'system', 0, 1, 1, 0, 1, '', '{}', '', 26, 0),
(0, 'plg_task_checkfiles', 'plugin', 'checkfiles', 'task', 0, 1, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_task_deleteactionlogs', 'plugin', 'deleteactionlogs', 'task', 0, 1, 1, 0, 1, '', '{}', '', 2, 0),
(0, 'plg_task_globalcheckin', 'plugin', 'globalcheckin', 'task', 0, 1, 1, 0, 0, '', '{}', '', 3, 0),
(0, 'plg_task_requests', 'plugin', 'requests', 'task', 0, 1, 1, 0, 1, '', '{}', '', 4, 0),
(0, 'plg_task_privacyconsent', 'plugin', 'privacyconsent', 'task', 0, 1, 1, 0, 1, '', '{}', '', 5, 0),
(0, 'plg_task_rotatelogs', 'plugin', 'rotatelogs', 'task', 0, 1, 1, 0, 1, '', '{}', '', 6, 0),
(0, 'plg_task_sessiongc', 'plugin', 'sessiongc', 'task', 0, 1, 1, 0, 1, '', '{}', '', 7, 0),
(0, 'plg_task_sitestatus', 'plugin', 'sitestatus', 'task', 0, 1, 1, 0, 1, '', '{}', '', 8, 0),
(0, 'plg_task_updatenotification', 'plugin', 'updatenotification', 'task', 0, 1, 1, 0, 1, '', '{}', '', 9, 0),
(0, 'plg_multifactorauth_totp', 'plugin', 'totp', 'multifactorauth', 0, 1, 1, 0, 1, '', '', '', 1, 0),
(0, 'plg_multifactorauth_yubikey', 'plugin', 'yubikey', 'multifactorauth', 0, 1, 1, 0, 1, '', '', '', 2, 0),
(0, 'plg_multifactorauth_webauthn', 'plugin', 'webauthn', 'multifactorauth', 0, 1, 1, 0, 1, '', '', '', 3, 0),
(0, 'plg_multifactorauth_email', 'plugin', 'email', 'multifactorauth', 0, 1, 1, 0, 1, '', '', '', 4, 0),
(0, 'plg_multifactorauth_fixed', 'plugin', 'fixed', 'multifactorauth', 0, 0, 1, 0, 1, '', '', '', 5, 0),
(0, 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', 0, 0, 1, 0, 1, '', '{"autowebpage":"","category":"4","autopublish":"0"}', '', 1, 0),
(0, 'plg_user_joomla', 'plugin', 'joomla', 'user', 0, 1, 1, 0, 1, '', '{"autoregister":"1","mail_to_user":"1","forceLogout":"1"}', '', 2, 0),
(0, 'plg_user_profile', 'plugin', 'profile', 'user', 0, 0, 1, 0, 1, '', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', 3, 0),
(0, 'plg_user_terms', 'plugin', 'terms', 'user', 0, 0, 1, 0, 1, '', '{}', '', 4, 0),
(0, 'plg_user_token', 'plugin', 'token', 'user', 0, 1, 1, 0, 1, '', '{}', '', 5, 0),
(0, 'plg_webservices_banners', 'plugin', 'banners', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_webservices_config', 'plugin', 'config', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 2, 0),
(0, 'plg_webservices_contact', 'plugin', 'contact', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 3, 0),
(0, 'plg_webservices_content', 'plugin', 'content', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 4, 0),
(0, 'plg_webservices_installer', 'plugin', 'installer', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 5, 0),
(0, 'plg_webservices_languages', 'plugin', 'languages', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 6, 0),
(0, 'plg_webservices_media', 'plugin', 'media', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 7, 0),
(0, 'plg_webservices_menus', 'plugin', 'menus', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 7, 0),
(0, 'plg_webservices_messages', 'plugin', 'messages', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 8, 0),
(0, 'plg_webservices_modules', 'plugin', 'modules', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 9, 0),
(0, 'plg_webservices_newsfeeds', 'plugin', 'newsfeeds', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 10, 0),
(0, 'plg_webservices_plugins', 'plugin', 'plugins', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 11, 0),
(0, 'plg_webservices_privacy', 'plugin', 'privacy', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 12, 0),
(0, 'plg_webservices_redirect', 'plugin', 'redirect', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 13, 0),
(0, 'plg_webservices_tags', 'plugin', 'tags', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 14, 0),
(0, 'plg_webservices_templates', 'plugin', 'templates', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 15, 0),
(0, 'plg_webservices_users', 'plugin', 'users', 'webservices', 0, 1, 1, 0, 1, '', '{}', '', 16, 0),
(0, 'plg_workflow_featuring', 'plugin', 'featuring', 'workflow', 0, 1, 1, 0, 1, '', '{}', '', 1, 0),
(0, 'plg_workflow_notification', 'plugin', 'notification', 'workflow', 0, 1, 1, 0, 1, '', '{}', '', 2, 0),
(0, 'plg_workflow_publishing', 'plugin', 'publishing', 'workflow', 0, 1, 1, 0, 1, '', '{}', '', 3, 0),
(0, 'plg_system_guidedtours', 'plugin', 'guidedtours', 'system', 0, 1, 1, 0, 1, '', '{}', '', 15, 0);

-- Templates
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'atum', 'template', 'atum', '', 1, 1, 1, 0, 1, '', '', '', 0, 0),
(0, 'cassiopeia', 'template', 'cassiopeia', '', 0, 1, 1, 0, 1, '', '{"brand":"1","logoFile":"","siteTitle":"","siteDescription":"","useFontScheme":"0","colorName":"colors_standard","fluidContainer":"0","stickyHeader":0,"backTop":0}', '', 0, 0);

-- Files Extensions
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'files_joomla', 'file', 'joomla', '', 0, 1, 1, 1, 1, '', '', '', 0, 0);

-- Languages
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'English (en-GB) Language Pack', 'package', 'pkg_en-GB', '', 0, 1, 1, 1, 1, '', '', '', 0, 0);


INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT "extension_id", 'English (en-GB)', 'language', 'en-GB', '', 0, 1, 1, 1, 1, '', '', '', 0, 0 FROM "#__extensions" WHERE "name" = 'English (en-GB) Language Pack';
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT "extension_id", 'English (en-GB)', 'language', 'en-GB', '', 1, 1, 1, 1, 1, '', '', '', 0, 0 FROM "#__extensions" WHERE "name" = 'English (en-GB) Language Pack';
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT "extension_id", 'English (en-GB)', 'language', 'en-GB', '', 3, 1, 1, 1, 1, '', '', '', 0, 0 FROM "#__extensions" WHERE "name" = 'English (en-GB) Language Pack';

--
-- Table structure for table `#__languages`
--

CREATE TABLE IF NOT EXISTS "#__languages" (
  "lang_id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "lang_code" varchar(7) NOT NULL,
  "title" varchar(50) NOT NULL,
  "title_native" varchar(50) NOT NULL,
  "sef" varchar(50) NOT NULL,
  "image" varchar(50) NOT NULL,
  "description" varchar(512) NOT NULL,
  "metakey" text,
  "metadesc" text NOT NULL,
  "sitename" varchar(1024) DEFAULT '' NOT NULL,
  "published" bigint DEFAULT 0 NOT NULL,
  "access" integer DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("lang_id"),
  CONSTRAINT "#__languages_idx_sef" UNIQUE ("sef"),
  CONSTRAINT "#__languages_idx_langcode" UNIQUE ("lang_code")
);
CREATE INDEX "#__languages_idx_ordering" ON "#__languages" ("ordering");
CREATE INDEX "#__languages_idx_access" ON "#__languages" ("access");

--
-- Dumping data for table `#__languages`
--

INSERT INTO "#__languages" ("lang_id", "lang_code", "title", "title_native", "sef", "image", "description", "metakey", "metadesc", "sitename", "published", "access", "ordering") VALUES
(1, 'en-GB', 'English (en-GB)', 'English (United Kingdom)', 'en', 'en_gb', '', '', '', '', 1, 1, 1);

SELECT setval('#__languages_lang_id_seq', 2, false);

--
-- Table structure for table `#__menu`
--

CREATE TABLE IF NOT EXISTS "#__menu" (
  "id" serial NOT NULL,
  "menutype" varchar(24) NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(255) NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "path" varchar(1024) DEFAULT '' NOT NULL,
  "link" varchar(1024) NOT NULL,
  "type" varchar(16) NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "parent_id" integer DEFAULT 1 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "component_id" integer DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "browserNav" smallint DEFAULT 0 NOT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "img" varchar(255) DEFAULT '' NOT NULL,
  "template_style_id" integer DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "home" smallint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "client_id" smallint DEFAULT 0 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__menu_idx_client_id_parent_id_alias_language" UNIQUE ("client_id", "parent_id", "alias", "language")
);
CREATE INDEX "#__menu_idx_componentid" ON "#__menu" ("component_id", "menutype", "published", "access");
CREATE INDEX "#__menu_idx_menutype" ON "#__menu" ("menutype");
CREATE INDEX "#__menu_idx_left_right" ON "#__menu" ("lft", "rgt");
CREATE INDEX "#__menu_idx_alias" ON "#__menu" ("alias");
CREATE INDEX "#__menu_idx_path" ON "#__menu" ("path");
CREATE INDEX "#__menu_idx_language" ON "#__menu" ("language");

COMMENT ON COLUMN "#__menu"."menutype" IS 'The type of menu this item belongs to. FK to #__menu_types.menutype';
COMMENT ON COLUMN "#__menu"."title" IS 'The display title of the menu item.';
COMMENT ON COLUMN "#__menu"."alias" IS 'The SEF alias of the menu item.';
COMMENT ON COLUMN "#__menu"."path" IS 'The computed path of the menu item based on the alias field.';
COMMENT ON COLUMN "#__menu"."link" IS 'The actually link the menu item refers to.';
COMMENT ON COLUMN "#__menu"."type" IS 'The type of link: Component, URL, Alias, Separator';
COMMENT ON COLUMN "#__menu"."published" IS 'The published state of the menu link.';
COMMENT ON COLUMN "#__menu"."parent_id" IS 'The parent menu item in the menu tree.';
COMMENT ON COLUMN "#__menu"."level" IS 'The relative level in the tree.';
COMMENT ON COLUMN "#__menu"."component_id" IS 'FK to #__extensions.id';
COMMENT ON COLUMN "#__menu"."checked_out" IS 'FK to #__users.id';
COMMENT ON COLUMN "#__menu"."checked_out_time" IS 'The time the menu item was checked out.';
COMMENT ON COLUMN "#__menu"."browserNav" IS 'The click behaviour of the link.';
COMMENT ON COLUMN "#__menu"."access" IS 'The access level required to view the menu item.';
COMMENT ON COLUMN "#__menu"."img" IS 'The image of the menu item.';
COMMENT ON COLUMN "#__menu"."params" IS 'JSON encoded data for the menu item.';
COMMENT ON COLUMN "#__menu"."lft" IS 'Nested set lft.';
COMMENT ON COLUMN "#__menu"."rgt" IS 'Nested set rgt.';
COMMENT ON COLUMN "#__menu"."home" IS 'Indicates if this menu item is the home or default page.';

--
-- Dumping data for table `#__menu`
--

INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down") VALUES
(1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, 0, '', 0, '', 0, 43, 0, '*', 0, NULL, NULL);
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 2, 'main', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 1, 1, 1, "extension_id", 0, 0, 'class:bookmark', 0, '', 1, 10, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_banners';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 3, 'main', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners&view=banners', 'component', 1, 2, 2, "extension_id", 0, 0, 'class:banners', 0, '', 2, 3, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_banners';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 4, 'main', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&view=categories&extension=com_banners', 'component', 1, 2, 2, "extension_id", 0, 0, 'class:banners-cat', 0, '', 4, 5, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_categories';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 5, 'main', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 1, 2, 2, "extension_id", 0, 0, 'class:banners-clients', 0, '', 6, 7, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_banners';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 6, 'main', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 1, 2, 2, "extension_id", 0, 0, 'class:banners-tracks', 0, '', 8, 9, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_banners';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 7, 'main', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 1, 1, 1, "extension_id", 0, 0, 'class:address-book', 0, '', 11, 20, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_contact';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 8, 'main', 'com_contact_contacts', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact&view=contacts', 'component', 1, 7, 2, "extension_id", 0, 0, 'class:contact', 0, '', 12, 13, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_contact';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 9, 'main', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&view=categories&extension=com_contact', 'component', 1, 7, 2, "extension_id", 0, 0, 'class:contact-cat', 0, '', 14, 15, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_categories';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 10, 'main', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 1, 1, 1, "extension_id", 0, 0, 'class:rss', 0, '', 23, 28, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_newsfeeds';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 11, 'main', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds&view=newsfeeds', 'component', 1, 10, 2, "extension_id", 0, 0, 'class:newsfeeds', 0, '', 24, 25, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_newsfeeds';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 12, 'main', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&view=categories&extension=com_newsfeeds', 'component', 1, 10, 2, "extension_id", 0, 0, 'class:newsfeeds-cat', 0, '', 26, 27, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_categories';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 13, 'main', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', 1, 1, 1, "extension_id", 0, 0, 'class:search-plus', 0, '', 29, 38, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_finder';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 14, 'main', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags&view=tags', 'component', 1, 1, 1, "extension_id", 0, 1, 'class:tags', 0, '', 39, 40, 0, '', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_tags';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 15, 'main', 'com_associations', 'Multilingual Associations', '', 'Multilingual Associations', 'index.php?option=com_associations&view=associations', 'component', 1, 1, 1, "extension_id", 0, 0, 'class:language', 0, '', 21, 22, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_associations';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 16, 'main', 'mod_menu_fields', 'Contact Custom Fields', '', 'contact/Custom Fields', 'index.php?option=com_fields&context=com_contact.contact', 'component', 1, 7, 2, "extension_id", 0, 0, 'class:messages-add', 0, '', 16, 17, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_fields';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 17, 'main', 'mod_menu_fields_group', 'Contact Custom Fields Group', '', 'contact/Custom Fields Group', 'index.php?option=com_fields&view=groups&context=com_contact.contact', 'component', 1, 7, 2, "extension_id", 0, 0, 'class:messages-add', 0, '', 18, 19, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_fields';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 18, 'main', 'com_finder_index', 'Smart-Search-Index', '', 'Smart Search/Index', 'index.php?option=com_finder&view=index', 'component', 1, 13, 2, "extension_id", 0, 0, 'class:finder', 0, '', 30, 31, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_finder';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 19, 'main', 'com_finder_maps', 'Smart-Search-Maps', '', 'Smart Search/Maps', 'index.php?option=com_finder&view=maps', 'component', 1, 13, 2, "extension_id", 0, 0, 'class:finder-maps', 0, '', 32, 33, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_finder';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 20, 'main', 'com_finder_filters', 'Smart-Search-Filters', '', 'Smart Search/Filters', 'index.php?option=com_finder&view=filters', 'component', 1, 13, 2, "extension_id", 0, 0, 'class:finder-filters', 0, '', 34, 35, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_finder';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 21, 'main', 'com_finder_searches', 'Smart-Search-Searches', '', 'Smart Search/Searches', 'index.php?option=com_finder&view=searches', 'component', 1, 13, 2, "extension_id", 0, 0, 'class:finder-searches', 0, '', 36, 37, 0, '*', 1, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_finder';
INSERT INTO "#__menu" ("id", "menutype", "title", "alias", "note", "path", "link", "type", "published", "parent_id", "level", "component_id", "browserNav", "access", "img", "template_style_id", "params", "lft", "rgt", "home", "language", "client_id", "publish_up", "publish_down")
SELECT 101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', 1, 1, 1, "extension_id", 0, 1, '', 0, '{"featured_categories":[""],"layout_type":"blog","blog_class_leading":"","blog_class":"","num_leading_articles":"1","num_intro_articles":"3","num_links":"0","link_intro_image":"","orderby_pri":"","orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1","show_title":"","link_titles":"","show_intro":"","info_block_position":"","info_block_show_title":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_associations":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_hits":"","show_tags":"","show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"1","page_heading":"","pageclass_sfx":"","menu-meta_description":"","robots":""}', 41, 42, 1, '*', 0, NULL, NULL FROM "#__extensions" WHERE "name" = 'com_content';
SELECT setval('#__menu_id_seq', 102, false);

--
-- Table structure for table `#__menu_types`
--

CREATE TABLE IF NOT EXISTS "#__menu_types" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "menutype" varchar(24) NOT NULL,
  "title" varchar(48) NOT NULL,
  "description" varchar(255) DEFAULT '' NOT NULL,
  "client_id" int DEFAULT 0 NOT NULL,
  "ordering" int NOT NULL DEFAULT 0,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__menu_types_idx_menutype" UNIQUE ("menutype")
);

--
-- Dumping data for table `#__menu_types`
--

INSERT INTO "#__menu_types" ("id", "asset_id", "menutype", "title", "description", "client_id", "ordering") VALUES
(1, 0, 'mainmenu', 'Main Menu', 'The main menu for the site', 0, 1);

SELECT setval('#__menu_types_id_seq', 2, false);

--
-- Table structure for table `#__modules`
--

CREATE TABLE IF NOT EXISTS "#__modules" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(100) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "content" text,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "position" varchar(50) DEFAULT '' NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  "published" smallint DEFAULT 0 NOT NULL,
  "module" varchar(50) DEFAULT NULL,
  "access" bigint DEFAULT 0 NOT NULL,
  "showtitle" smallint DEFAULT 1 NOT NULL,
  "params" text NOT NULL,
  "client_id" smallint DEFAULT 0 NOT NULL,
  "language" varchar(7) NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__modules_published" ON "#__modules" ("published", "access");
CREATE INDEX "#__modules_newsfeeds" ON "#__modules" ("module", "published");
CREATE INDEX "#__modules_idx_language" ON "#__modules" ("language");

--
-- Dumping data for table `#__modules`
--

INSERT INTO "#__modules" ("id", "asset_id", "title", "note", "content", "ordering", "position", "publish_up", "publish_down", "published", "module", "access", "showtitle", "params", "client_id", "language") VALUES
(1, 39, 'Main Menu', '', '', 1, 'sidebar-right', NULL, NULL, 1, 'mod_menu', 1, 1, '{"menutype":"mainmenu","startLevel":"0","endLevel":"0","showAllChildren":"1","tag_id":"","class_sfx":"","window_open":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'),
(2, 40, 'Login', '', '', 1, 'login', NULL, NULL, 1, 'mod_login', 1, 1, '', 1, '*'),
(3, 41, 'Popular Articles', '', '', 6, 'cpanel', NULL, NULL, 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(4, 42, 'Recently Added Articles', '', '', 4, 'cpanel', NULL, NULL, 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(8, 43, 'Toolbar', '', '', 1, 'toolbar', NULL, NULL, 1, 'mod_toolbar', 3, 1, '', 1, '*'),
(9, 44, 'Notifications', '', '', 3, 'icon', NULL, NULL, 1, 'mod_quickicon', 3, 1, '{"context":"update_quickicon","header_icon":"icon-sync","show_jupdate":"1","show_eupdate":"1","show_oupdate":"1","show_privacy":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":""}', 1, '*'),
(10, 45, 'Logged-in Users', '', '', 2, 'cpanel', NULL, NULL, 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(12, 46, 'Admin Menu', '', '', 1, 'menu', NULL, NULL, 1, 'mod_menu', 3, 1, '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', 1, '*'),
(15, 49, 'Title', '', '', 1, 'title', NULL, NULL, 1, 'mod_title', 3, 1, '', 1, '*'),
(16, 50, 'Login Form', '', '', 7, 'sidebar-right', NULL, NULL, 1, 'mod_login', 1, 1, '{"greeting":"1","name":"0"}', 0, '*'),
(17, 51, 'Breadcrumbs', '', '', 1, 'breadcrumbs', NULL, NULL, 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"0","cache_time":"0","cachemode":"itemid"}', 0, '*'),
(79, 52, 'Multilanguage status', '', '', 2, 'status', NULL, NULL, 1, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(86, 53, 'Joomla Version', '', '', 1, 'status', NULL, NULL, 1, 'mod_version', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'),
(87, 55, 'Sample Data', '', '', 1, 'cpanel', NULL, NULL, 1, 'mod_sampledata', 6, 1, '{"bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(88, 67, 'Latest Actions', '', '', 3, 'cpanel', NULL, NULL, 1, 'mod_latestactions', 6, 1, '{"bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(89, 68, 'Privacy Dashboard', '', '', 5, 'cpanel', NULL, NULL, 1, 'mod_privacy_dashboard', 6, 1, '{"bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(90, 89, 'Login Support', '', '', 1, 'sidebar', NULL, NULL, 1, 'mod_loginsupport', 1, 1, '{"forum_url":"https://forum.joomla.org/","documentation_url":"https://docs.joomla.org/","news_url":"https://www.joomla.org/announcements.html","automatic_title":1,"prepare_content":1,"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}', 1, '*'),
(91, 72, 'System Dashboard', '', '', 1, 'cpanel-system', NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"system","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":"","style":"System-none"}', 1, '*'),
(92, 73, 'Content Dashboard', '', '', 1, 'cpanel-content', NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"content","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":"","style":"System-none"}', 1, '*'),
(93, 74, 'Menus Dashboard', '', '', 1, 'cpanel-menus', NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"menus","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":"","style":"System-none"}', 1, '*'),
(94, 75, 'Components Dashboard', '', '', 1, 'cpanel-components', NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"components","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":"","style":"System-none"}', 1, '*'),
(95, 76, 'Users Dashboard', '', '', 1, 'cpanel-users', NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"users","layout":"_:default","moduleclass_sfx":"","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":"","style":"System-none"}', 1, '*'),
(96, 86, 'Popular Articles', '', '', 3, 'cpanel-content', NULL, NULL, 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(97, 87, 'Recently Added Articles', '', '', 4, 'cpanel-content', NULL, NULL, 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(98, 88, 'Logged-in Users', '', '', 2, 'cpanel-users', NULL, NULL, 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0", "bootstrap_size": "12","header_tag":"h2"}', 1, '*'),
(99, 77, 'Frontend Link', '', '', 5, 'status', NULL, NULL, 1, 'mod_frontend', 1, 1, '', 1, '*'),
(100, 78, 'Messages', '', '', 4, 'status', NULL, NULL, 1, 'mod_messages', 3, 1, '', 1, '*'),
(101, 79, 'Post Install Messages', '', '', 3, 'status', NULL, NULL, 1, 'mod_post_installation_messages', 3, 1, '', 1, '*'),
(102, 80, 'User Status', '', '', 6, 'status', NULL, NULL, 1, 'mod_user', 3, 1, '', 1, '*'),
(103, 70, 'Site', '', '', 1, 'icon', NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"site_quickicon","header_icon":"icon-desktop","show_users":"1","show_articles":"1","show_categories":"1","show_media":"1","show_menuItems":"1","show_modules":"1","show_plugins":"1","show_templates":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":""}', 1, '*'),
(104, 71, 'System', '', '', 2, 'icon', NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"system_quickicon","header_icon":"icon-wrench","show_global":"1","show_checkin":"1","show_cache":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":""}', 1, '*'),
(105, 82, '3rd Party', '', '', 4, 'icon', NULL, NULL, 1, 'mod_quickicon', 1, 1, '{"context":"mod_quickicon","header_icon":"icon-boxes","load_plugins":"1","layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"style":"0","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":""}', 1, '*'),
(106, 83, 'Help Dashboard', '', '', 1, 'cpanel-help', NULL, NULL, 1, 'mod_submenu', 1, 0, '{"menutype":"*","preset":"help","layout":"_:default","moduleclass_sfx":"","style":"System-none","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":""}', 1, '*'),
(107, 84, 'Privacy Requests', '', '', 1, 'cpanel-privacy', NULL, NULL, 1, 'mod_privacy_dashboard', 1, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"cachemode":"static","style":"0","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":""}', 1, '*'),
(108, 85, 'Privacy Status', '', '', 1, 'cpanel-privacy', NULL, NULL, 1, 'mod_privacy_status', 1, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":1,"cache_time":900,"cachemode":"static","style":"0","module_tag":"div","bootstrap_size":"12","header_tag":"h2","header_class":""}', 1, '*'),
(109, 96, 'Guided Tours', '', '', 1, 'status', NULL, NULL, 1, 'mod_guidedtours', 1, 1, '', 1, '*');

SELECT setval('#__modules_id_seq', 110, false);

--
-- Table structure for table `#__modules_menu`
--

CREATE TABLE IF NOT EXISTS "#__modules_menu" (
  "moduleid" bigint DEFAULT 0 NOT NULL,
  "menuid" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("moduleid", "menuid")
);

--
-- Dumping data for table `#__modules_menu`
--

INSERT INTO "#__modules_menu" ("moduleid", "menuid") VALUES
(1, 0),
(2, 0),
(3, 0),
(4, 0),
(6, 0),
(7, 0),
(8, 0),
(9, 0),
(10, 0),
(12, 0),
(14, 0),
(15, 0),
(16, 0),
(17, 0),
(79, 0),
(86, 0),
(87, 0),
(88, 0),
(89, 0),
(90, 0),
(91, 0),
(92, 0),
(93, 0),
(94, 0),
(95, 0),
(96, 0),
(97, 0),
(98, 0),
(99, 0),
(100, 0),
(101, 0),
(102, 0),
(103, 0),
(104, 0),
(105, 0),
(106, 0),
(107, 0),
(108, 0),
(109, 0);

--
-- Table structure for table `#__schemas`
--

CREATE TABLE IF NOT EXISTS "#__schemas" (
  "extension_id" bigint NOT NULL,
  "version_id" varchar(20) NOT NULL,
  PRIMARY KEY ("extension_id", "version_id")
);

--
-- Table structure for table `#__session`
--

CREATE TABLE IF NOT EXISTS "#__session" (
  "session_id" bytea NOT NULL,
  "client_id" smallint DEFAULT NULL,
  "guest" smallint DEFAULT 1,
  "time" integer DEFAULT 0 NOT NULL,
  "data" text,
  "userid" bigint DEFAULT 0,
  "username" varchar(150) DEFAULT '',
  PRIMARY KEY ("session_id")
);
CREATE INDEX "#__session_userid" ON "#__session" ("userid");
CREATE INDEX "#__session_time" ON "#__session" ("time");
CREATE INDEX "#__session_idx_client_id_guest" ON "#__session" ("client_id", "guest");

--
-- Table structure for table `#__tags`
--

CREATE TABLE IF NOT EXISTS "#__tags" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "level" integer DEFAULT 0 NOT NULL,
  "path" varchar(255) DEFAULT '' NOT NULL,
  "title" varchar(255) NOT NULL,
  "alias" varchar(255) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  "description" text NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "access" bigint DEFAULT 0 NOT NULL,
  "params" text NOT NULL,
  "metadesc" varchar(1024) NOT NULL,
  "metakey" varchar(1024) DEFAULT '' NOT NULL,
  "metadata" varchar(2048) NOT NULL,
  "created_user_id" integer DEFAULT 0 NOT NULL,
  "created_time" timestamp without time zone NOT NULL,
  "created_by_alias" varchar(255) DEFAULT '' NOT NULL,
  "modified_user_id" integer DEFAULT 0 NOT NULL,
  "modified_time" timestamp without time zone NOT NULL,
  "images" text NOT NULL,
  "urls" text NOT NULL,
  "hits" integer DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "version" bigint DEFAULT 1 NOT NULL,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__tags_cat_idx" ON "#__tags" ("published", "access");
CREATE INDEX "#__tags_idx_access" ON "#__tags" ("access");
CREATE INDEX "#__tags_idx_checkout" ON "#__tags" ("checked_out");
CREATE INDEX "#__tags_idx_path" ON "#__tags" ("path");
CREATE INDEX "#__tags_idx_left_right" ON "#__tags" ("lft", "rgt");
CREATE INDEX "#__tags_idx_alias" ON "#__tags" ("alias");
CREATE INDEX "#__tags_idx_language" ON "#__tags" ("language");

--
-- Dumping data for table `#__tags`
--

INSERT INTO "#__tags" ("id", "parent_id", "lft", "rgt", "level", "path", "title", "alias", "note", "description", "published", "access", "params", "metadesc", "metakey", "metadata", "created_user_id", "created_time", "created_by_alias", "modified_user_id", "modified_time", "images", "urls", "hits", "language", "version") VALUES
(1, 0, 0, 1, 0, '', 'ROOT', 'root', '', '', 1, 1, '', '', '', '', 42, CURRENT_TIMESTAMP, '', 42, CURRENT_TIMESTAMP, '', '',  0, '*', 1);

SELECT setval('#__tags_id_seq', 2, false);

--
-- Table structure for table `#__template_overrides`
--

CREATE TABLE IF NOT EXISTS "#__template_overrides" (
  "id" serial NOT NULL,
  "template" varchar(50) DEFAULT '' NOT NULL,
  "hash_id" varchar(255) DEFAULT '' NOT NULL,
  "extension_id" bigint DEFAULT 0,
  "state" smallint DEFAULT 0 NOT NULL,
  "action" varchar(50) DEFAULT '' NOT NULL,
  "client_id" smallint DEFAULT 0 NOT NULL,
  "created_date" timestamp without time zone NOT NULL,
  "modified_date" timestamp without time zone,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__template_overrides_idx_template" ON "#__template_overrides" ("template");
CREATE INDEX "#__template_overrides_idx_extension_id" ON "#__template_overrides" ("extension_id");

--
-- Table structure for table `#__template_styles`
--

CREATE TABLE IF NOT EXISTS "#__template_styles" (
  "id" serial NOT NULL,
  "template" varchar(50) DEFAULT '' NOT NULL,
  "client_id" smallint DEFAULT 0 NOT NULL,
  "home" varchar(7) DEFAULT '0' NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "inheritable" smallint DEFAULT 0 NOT NULL,
  "parent" varchar(50) DEFAULT '',
  "params" text NOT NULL,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__template_styles_idx_template" ON "#__template_styles" ("template");
CREATE INDEX "#__template_styles_idx_client_id" ON "#__template_styles" ("client_id");
CREATE INDEX "#__template_styles_idx_client_id_home" ON "#__template_styles" ("client_id", "home");

--
-- Dumping data for table `#__template_styles`
--
INSERT INTO "#__template_styles" ("id", "template", "client_id", "home", "title", "inheritable", "parent", "params") VALUES
(10, 'atum', 1, '1', 'Atum - Default', 1, '', '{"hue":"hsl(214, 63%, 20%)","bg-light":"#f0f4fb","text-dark":"#495057","text-light":"#ffffff","link-color":"#2a69b8","special-color":"#001b4c","colorScheme":"os","monochrome":"0","loginLogo":"","loginLogoAlt":"","logoBrandLarge":"","logoBrandLargeAlt":"","logoBrandSmall":"","logoBrandSmallAlt":""}'),
(11, 'cassiopeia', 0, '1', 'Cassiopeia - Default', 1, '', '{"brand":"1","logoFile":"","siteTitle":"","siteDescription":"","useFontScheme":"0","colorName":"colors_standard","fluidContainer":"0","stickyHeader":0,"backTop":0}');

SELECT setval('#__template_styles_id_seq', 12, false);

--
-- Table structure for table `#__updates`
--

CREATE TABLE IF NOT EXISTS "#__updates" (
  "update_id" serial NOT NULL,
  "update_site_id" bigint DEFAULT 0,
  "extension_id" bigint DEFAULT 0,
  "name" varchar(100) DEFAULT '',
  "description" text NOT NULL,
  "element" varchar(100) DEFAULT '',
  "type" varchar(20) DEFAULT '',
  "folder" varchar(20) DEFAULT '',
  "client_id" smallint DEFAULT 0,
  "version" varchar(32) DEFAULT '',
  "data" text NOT NULL,
  "detailsurl" text NOT NULL,
  "infourl" text NOT NULL,
  "changelogurl" text,
  "extra_query" varchar(1000) DEFAULT '',
  PRIMARY KEY ("update_id")
);

COMMENT ON TABLE "#__updates" IS 'Available Updates';

--
-- Table structure for table "#__tuf_metadata"
--

CREATE TABLE IF NOT EXISTS "#__tuf_metadata" (
"id" serial NOT NULL,
"update_site_id" bigint DEFAULT 0 NOT NULL,
"root" text DEFAULT NULL,
"targets" text DEFAULT NULL,
"snapshot" text DEFAULT NULL,
"timestamp" text DEFAULT NULL,
"mirrors" text DEFAULT NULL,
PRIMARY KEY ("id")
);

COMMENT ON TABLE "#__tuf_metadata" IS 'Secure TUF Updates';

--
-- Dumping data for table "#__tuf_metadata"
--

INSERT INTO "#__tuf_metadata" ("update_site_id", "root")
VALUES (1, '{"signed":{"_type":"root","spec_version":"1.0","version":2,"expires":"2025-03-02T11:22:17Z","keys":{"07eb082f367c034a95878687f6648aa76d93652b6ee73e58817053d89af6c44f":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"9b2af2d9b9727227735253d795bd27ea8f0e294a5f3603e822dc5052b44802b9"}},"1b1b1dd55b2c1c7258714cf1c1ae06f23e4607b28c762d016a9d81c48ffe5669":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"a18e5ebabc19d5d5984b601a292ece61ba3662ab2d071dc520da5bd4f8948799"}},"2dcaf3d0e552f150792f7c636d45429246dcfa34ac35b46a44f5c87cd17d457e":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"cb0a7a131961a20edea051d6dc2b091fb650bd399bd8514adb67b3c60db9f8f9"}},"31dd7c7290d664c9b88c0dead2697175293ea7df81b7f24153a37370fd3901c3":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"589d029a68b470deff1ca16dbf3eea6b5b3fcba0ae7bb52c468abc7fb058b2a2"}},"9e41a9d62d94c6a1c8a304f62c5bd72d84a9f286f27e8327cedeacb09e5156cc":{"keytype":"ed25519","scheme":"ed25519","keyid_hash_algorithms":["sha256","sha512"],"keyval":{"public":"6043c8bacc76ac5c9750f45454dd865c6ca1fc57d69e14cc192cfd420f6a66a9"}}},"roles":{"root":{"keyids":["1b1b1dd55b2c1c7258714cf1c1ae06f23e4607b28c762d016a9d81c48ffe5669","2dcaf3d0e552f150792f7c636d45429246dcfa34ac35b46a44f5c87cd17d457e"],"threshold":1},"snapshot":{"keyids":["07eb082f367c034a95878687f6648aa76d93652b6ee73e58817053d89af6c44f","2dcaf3d0e552f150792f7c636d45429246dcfa34ac35b46a44f5c87cd17d457e"],"threshold":1},"targets":{"keyids":["31dd7c7290d664c9b88c0dead2697175293ea7df81b7f24153a37370fd3901c3"],"threshold":1},"timestamp":{"keyids":["9e41a9d62d94c6a1c8a304f62c5bd72d84a9f286f27e8327cedeacb09e5156cc"],"threshold":1}},"consistent_snapshot":true},"signatures":[{"keyid":"2dcaf3d0e552f150792f7c636d45429246dcfa34ac35b46a44f5c87cd17d457e","sig":"2a225a560ec0837b721d4c5e379fedbd3c7c9079a94e6b31e47e0184c8b95421b6036b4286c5d90f29ab4c468d79a712fdb65e96511394ceb3aa8e2b3983a501"},{"keyid":"1b1b1dd55b2c1c7258714cf1c1ae06f23e4607b28c762d016a9d81c48ffe5669","sig":"8ce0b2a7bdc1e6dcba12081f440510df0a593c072dcf591631c2dd0f456844a7da63be8e8ac31ffbddf42641fde84dc733a336031d182c2163b4c1eaf2117005"}]}');

--
-- Table structure for table `#__update_sites`
--

CREATE TABLE IF NOT EXISTS "#__update_sites" (
  "update_site_id" serial NOT NULL,
  "name" varchar(100) DEFAULT '',
  "type" varchar(20) DEFAULT '',
  "location" text NOT NULL,
  "enabled" bigint DEFAULT 0,
  "last_check_timestamp" bigint DEFAULT 0,
  "extra_query" varchar(1000) DEFAULT '',
  "checked_out" integer,
  "checked_out_time" timestamp without time zone DEFAULT NULL,
  PRIMARY KEY ("update_site_id")
);

COMMENT ON TABLE "#__update_sites" IS 'Update Sites';

--
-- Dumping data for table `#__update_sites`
--

INSERT INTO "#__update_sites" ("update_site_id", "name", "type", "location", "enabled", "last_check_timestamp") VALUES
(1, 'Joomla! Core', 'tuf', 'https://update.joomla.org/cms/', 1, 0),
(2, 'Accredited Joomla! Translations', 'collection', 'https://update.joomla.org/language/translationlist_5.xml', 1, 0),
(3, 'Joomla! Update Component', 'extension', 'https://update.joomla.org/core/extensions/com_joomlaupdate.xml', 1, 0);

SELECT setval('#__update_sites_update_site_id_seq', 4, false);

--
-- Table structure for table `#__update_sites_extensions`
--

CREATE TABLE IF NOT EXISTS "#__update_sites_extensions" (
  "update_site_id" bigint DEFAULT 0 NOT NULL,
  "extension_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("update_site_id", "extension_id")
);

COMMENT ON TABLE "#__update_sites_extensions" IS 'Links extensions to update sites';

--
-- Dumping data for table `#__update_sites_extensions`
--

INSERT INTO "#__update_sites_extensions" ("update_site_id", "extension_id")
SELECT 1, "extension_id" FROM "#__extensions" WHERE "name" = 'files_joomla';
INSERT INTO "#__update_sites_extensions" ("update_site_id", "extension_id")
SELECT 2, "extension_id" FROM "#__extensions" WHERE "name" = 'English (en-GB) Language Pack';
INSERT INTO "#__update_sites_extensions" ("update_site_id", "extension_id")
SELECT 3, "extension_id" FROM "#__extensions" WHERE "name" = 'com_joomlaupdate';

--
-- Table structure for table `#__usergroups`
--

CREATE TABLE IF NOT EXISTS "#__usergroups" (
  "id" serial NOT NULL,
  "parent_id" bigint DEFAULT 0 NOT NULL,
  "lft" bigint DEFAULT 0 NOT NULL,
  "rgt" bigint DEFAULT 0 NOT NULL,
  "title" varchar(100) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__usergroups_idx_usergroup_parent_title_lookup" UNIQUE ("parent_id", "title")
);
CREATE INDEX "#__usergroups_idx_usergroup_title_lookup" ON "#__usergroups" ("title");
CREATE INDEX "#__usergroups_idx_usergroup_adjacency_lookup" ON "#__usergroups" ("parent_id");
CREATE INDEX "#__usergroups_idx_usergroup_nested_set_lookup" ON "#__usergroups" ("lft", "rgt");

COMMENT ON COLUMN "#__usergroups"."id" IS 'Primary Key';
COMMENT ON COLUMN "#__usergroups"."parent_id" IS 'Adjacency List Reference Id';
COMMENT ON COLUMN "#__usergroups"."lft" IS 'Nested set lft.';
COMMENT ON COLUMN "#__usergroups"."rgt" IS 'Nested set rgt.';

--
-- Dumping data for table `#__usergroups`
--

INSERT INTO "#__usergroups" ("id", "parent_id", "lft", "rgt", "title") VALUES
(1, 0, 1, 18, 'Public'),
(2, 1, 8, 15, 'Registered'),
(3, 2, 9, 14, 'Author'),
(4, 3, 10, 13, 'Editor'),
(5, 4, 11, 12, 'Publisher'),
(6, 1, 4, 7, 'Manager'),
(7, 6, 5, 6, 'Administrator'),
(8, 1, 16, 17, 'Super Users'),
(9, 1, 2, 3, 'Guest');

SELECT setval('#__usergroups_id_seq', 10, false);

--
-- Table structure for table `#__users`
--

CREATE TABLE IF NOT EXISTS "#__users" (
  "id" serial NOT NULL,
  "name" varchar(255) DEFAULT '' NOT NULL,
  "username" varchar(150) DEFAULT '' NOT NULL,
  "email" varchar(100) DEFAULT '' NOT NULL,
  "password" varchar(100) DEFAULT '' NOT NULL,
  "block" smallint DEFAULT 0 NOT NULL,
  "sendEmail" smallint DEFAULT 0,
  "registerDate" timestamp without time zone NOT NULL,
  "lastvisitDate" timestamp without time zone,
  "activation" varchar(100) DEFAULT '' NOT NULL,
  "params" text NOT NULL,
  "lastResetTime" timestamp without time zone,
  "resetCount" bigint DEFAULT 0 NOT NULL,
  "otpKey" varchar(1000) DEFAULT '' NOT NULL,
  "otep" varchar(1000) DEFAULT '' NOT NULL,
  "requireReset" smallint DEFAULT 0,
  "authProvider" varchar(100) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__users_idx_username" UNIQUE ("username")
);
CREATE INDEX "#__users_idx_name" ON "#__users" ("name");
CREATE INDEX "#__users_idx_block" ON "#__users" ("block");
CREATE INDEX "#__users_email" ON "#__users" ("email");
CREATE INDEX "#__users_email_lower" ON "#__users" (lower("email"));

COMMENT ON COLUMN "#__users"."lastResetTime" IS 'Date of last password reset';
COMMENT ON COLUMN "#__users"."resetCount" IS 'Count of password resets since lastResetTime';
COMMENT ON COLUMN "#__users"."requireReset" IS 'Require user to reset password on next login';

--
-- Table structure for table `#__user_keys`
--

CREATE TABLE IF NOT EXISTS "#__user_keys" (
  "id" serial NOT NULL,
  "user_id" varchar(255) NOT NULL,
  "token" varchar(255) NOT NULL,
  "series" varchar(255) NOT NULL,
  "time" varchar(200) NOT NULL,
  "uastring" varchar(255) NOT NULL,
  PRIMARY KEY ("id"),
	CONSTRAINT "#__user_keys_series" UNIQUE ("series")
);
CREATE INDEX "#__user_keys_idx_user_id" ON "#__user_keys" ("user_id");

--
-- Table structure for table `#__user_notes`
--

CREATE TABLE IF NOT EXISTS "#__user_notes" (
  "id" serial NOT NULL,
  "user_id" integer DEFAULT 0 NOT NULL,
  "catid" integer DEFAULT 0 NOT NULL,
  "subject" varchar(100) DEFAULT '' NOT NULL,
  "body" text NOT NULL,
  "state" smallint DEFAULT 0 NOT NULL,
  "checked_out" integer,
  "checked_out_time" timestamp without time zone,
  "created_user_id" integer DEFAULT 0 NOT NULL,
  "created_time" timestamp without time zone NOT NULL,
  "modified_user_id" integer DEFAULT 0 NOT NULL,
  "modified_time" timestamp without time zone NOT NULL,
  "review_time" timestamp without time zone,
  "publish_up" timestamp without time zone,
  "publish_down" timestamp without time zone,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__user_notes_idx_user_id" ON "#__user_notes" ("user_id");
CREATE INDEX "#__user_notes_idx_category_id" ON "#__user_notes" ("catid");

--
-- Table structure for table `#__user_profiles`
--

CREATE TABLE IF NOT EXISTS "#__user_profiles" (
  "user_id" bigint NOT NULL,
  "profile_key" varchar(100) NOT NULL,
  "profile_value" text NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  CONSTRAINT "#__user_profiles_idx_user_id_profile_key" UNIQUE ("user_id", "profile_key")
);

COMMENT ON TABLE "#__user_profiles" IS 'Simple user profile storage table';

--
-- Table structure for table `#__user_mfa`
--

CREATE TABLE IF NOT EXISTS "#__user_mfa" (
  "id" serial NOT NULL,
  "user_id" bigint NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "method" varchar(100) NOT NULL,
  "default" smallint DEFAULT 0 NOT NULL,
  "options" text NOT NULL,
  "created_on" timestamp without time zone NOT NULL,
  "last_used" timestamp without time zone,
  "tries" bigint DEFAULT 0 NOT NULL,
  "last_try" timestamp without time zone,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__user_mfa_idx_user_id" ON "#__user_mfa" ("user_id");

COMMENT ON TABLE "#__user_mfa" IS 'Multi-factor Authentication settings';

--
-- Table structure for table `#__user_usergroup_map`
--

CREATE TABLE IF NOT EXISTS "#__user_usergroup_map" (
  "user_id" bigint DEFAULT 0 NOT NULL,
  "group_id" bigint DEFAULT 0 NOT NULL,
  PRIMARY KEY ("user_id", "group_id")
);

COMMENT ON COLUMN "#__user_usergroup_map"."user_id" IS 'Foreign Key to #__users.id';
COMMENT ON COLUMN "#__user_usergroup_map"."group_id" IS 'Foreign Key to #__usergroups.id';

--
-- Table structure for table `#__viewlevels`
--

CREATE TABLE IF NOT EXISTS "#__viewlevels" (
  "id" serial NOT NULL,
  "title" varchar(100) DEFAULT '' NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "rules" varchar(5120) NOT NULL,
  PRIMARY KEY ("id"),
  CONSTRAINT "#__viewlevels_idx_assetgroup_title_lookup" UNIQUE ("title")
);

COMMENT ON COLUMN "#__viewlevels"."id" IS 'Primary Key';
COMMENT ON COLUMN "#__viewlevels"."rules" IS 'JSON encoded access control.';

--
-- Dumping data for table `#__viewlevels`
--

INSERT INTO "#__viewlevels" ("id", "title", "ordering", "rules") VALUES
(1, 'Public', 0, '[1]'),
(2, 'Registered', 2, '[6,2,8]'),
(3, 'Special', 3, '[6,3,8]'),
(5, 'Guest', 1, '[9]'),
(6, 'Super Users', 4, '[8]');

SELECT setval('#__viewlevels_id_seq', 7, false);

--
-- Table structure for table `#__workflows`
--

CREATE TABLE IF NOT EXISTS "#__workflows" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "title" varchar(255) DEFAULT '' NOT NULL,
  "description" text NOT NULL,
  "extension" varchar(50) NOT NULL,
  "default" smallint NOT NULL  DEFAULT 0,
  "ordering" bigint NOT NULL DEFAULT 0,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone,
  "checked_out" integer,
  PRIMARY KEY ("id")
 );

CREATE INDEX "#__workflows_idx_asset_id" ON "#__workflows" ("asset_id");
CREATE INDEX "#__workflows_idx_title" ON "#__workflows" ("title");
CREATE INDEX "#__workflows_idx_extension" ON "#__workflows" ("extension");
CREATE INDEX "#__workflows_idx_default" ON "#__workflows" ("default");
CREATE INDEX "#__workflows_idx_created" ON "#__workflows" ("created");
CREATE INDEX "#__workflows_idx_created_by" ON "#__workflows" ("created_by");
CREATE INDEX "#__workflows_idx_modified" ON "#__workflows" ("modified");
CREATE INDEX "#__workflows_idx_modified_by" ON "#__workflows" ("modified_by");
CREATE INDEX "#__workflows_idx_checked_out" ON "#__workflows" ("checked_out");

INSERT INTO "#__workflows" ("id", "asset_id", "published", "title", "description", "extension", "default", "ordering", "created", "created_by", "modified", "modified_by") VALUES
(1, 56, 1, 'COM_WORKFLOW_BASIC_WORKFLOW', '', 'com_content.article', 1, 1, CURRENT_TIMESTAMP, 42, CURRENT_TIMESTAMP, 42);

SELECT setval('#__workflows_id_seq', 2, false);

--
-- Table structure for table `#__workflow_associations`
--

CREATE TABLE IF NOT EXISTS "#__workflow_associations" (
  "item_id" bigint DEFAULT 0 NOT NULL,
  "stage_id" bigint DEFAULT 0 NOT NULL,
  "extension" varchar(50) NOT NULL,
  PRIMARY KEY ("item_id", "extension")
);
CREATE INDEX "#__workflow_associations_idx_item_stage_extension" ON "#__workflow_associations" ("item_id", "stage_id", "extension");
CREATE INDEX "#__workflow_associations_idx_item_id" ON "#__workflow_associations" ("item_id");
CREATE INDEX "#__workflow_associations_idx_stage_id" ON "#__workflow_associations" ("stage_id");
CREATE INDEX "#__workflow_associations_idx_extension" ON "#__workflow_associations" ("extension");

COMMENT ON COLUMN "#__workflow_associations"."item_id" IS 'Extension table id value';
COMMENT ON COLUMN "#__workflow_associations"."stage_id" IS 'Foreign Key to #__workflow_stages.id';

--
-- Table structure for table `#__workflow_stages`
--

CREATE TABLE IF NOT EXISTS "#__workflow_stages" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "workflow_id" bigint DEFAULT 0 NOT NULL,
  "published" smallint NOT NULL  DEFAULT 0,
  "title" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "default" smallint NOT NULL  DEFAULT 0,
  "checked_out_time" timestamp without time zone,
  "checked_out" integer,
  PRIMARY KEY ("id")
);
CREATE INDEX "#__workflow_stages_idx_workflow_id" ON "#__workflow_stages" ("workflow_id");
CREATE INDEX "#__workflow_stages_idx_title" ON "#__workflow_stages" ("title");
CREATE INDEX "#__workflow_stages_idx_asset_id" ON "#__workflow_stages" ("asset_id");
CREATE INDEX "#__workflow_stages_idx_default" ON "#__workflow_stages" ("default");
CREATE INDEX "#__workflow_stages_idx_checked_out" ON "#__workflow_stages" ("checked_out");

--
-- Dumping data for table `#__workflow_stages`
--

INSERT INTO "#__workflow_stages" ("id", "asset_id", "ordering", "workflow_id", "published", "title", "description", "default") VALUES
(1, 57, 1, 1, 1, 'COM_WORKFLOW_BASIC_STAGE', '', 1);

SELECT setval('#__workflow_stages_id_seq', 2, false);

--
-- Table structure for table `#__workflow_transitions`
--

CREATE TABLE IF NOT EXISTS "#__workflow_transitions" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "workflow_id" bigint DEFAULT 0 NOT NULL,
  "published" smallint NOT NULL  DEFAULT 0,
  "title" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "from_stage_id" bigint DEFAULT 0 NOT NULL,
  "to_stage_id" bigint DEFAULT 0 NOT NULL,
  "options" text NOT NULL,
  "checked_out_time" timestamp without time zone,
  "checked_out" integer,
  PRIMARY KEY ("id")
 );
CREATE INDEX "#__workflow_transitions_idx_title" ON "#__workflow_transitions" ("title");
CREATE INDEX "#__workflow_transitions_idx_asset_id" ON "#__workflow_transitions" ("asset_id");
CREATE INDEX "#__workflow_transitions_idx_from_stage_id" ON "#__workflow_transitions" ("from_stage_id");
CREATE INDEX "#__workflow_transitions_idx_to_stage_id" ON "#__workflow_transitions" ("to_stage_id");
CREATE INDEX "#__workflow_transitions_idx_workflow_id" ON "#__workflow_transitions" ("workflow_id");
CREATE INDEX "#__workflow_transitions_idx_checked_out" ON "#__workflow_transitions" ("checked_out");

INSERT INTO "#__workflow_transitions" ("id", "asset_id", "published", "ordering", "workflow_id", "title", "description", "from_stage_id", "to_stage_id", "options") VALUES
(1, 58, 1, 1, 1, 'UNPUBLISH', '', -1, 1, '{"publishing":"0"}'),
(2, 59, 1, 2, 1, 'PUBLISH', '', -1, 1, '{"publishing":"1"}'),
(3, 60, 1, 3, 1, 'TRASH', '', -1, 1, '{"publishing":"-2"}'),
(4, 61, 1, 4, 1, 'ARCHIVE', '', -1, 1, '{"publishing":"2"}'),
(5, 62, 1, 5, 1, 'FEATURE', '', -1, 1, '{"featuring":"1"}'),
(6, 63, 1, 6, 1, 'UNFEATURE', '', -1, 1, '{"featuring":"0"}'),
(7, 64, 1, 7, 1, 'PUBLISH_AND_FEATURE', '', -1, 1, '{"publishing":"1","featuring":"1"}');

SELECT setval('#__workflow_transitions_id_seq', 8, false);
