-- Table #__assets

TRUNCATE TABLE [#__assets];

SET IDENTITY_INSERT  [#__assets] ON;

INSERT INTO [#__assets] ([id], [parent_id], [lft], [rgt], [level], [name], [title], [rules])
SELECT 1, 0, 1, 85, 0, 'root.1', 'Root Asset', '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'
UNION ALL
SELECT 2, 1, 1, 2, 1, 'com_admin', 'com_admin', '{}'
UNION ALL
SELECT 3, 1, 3, 6, 1, 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 4, 1, 7, 8, 1, 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 5, 1, 9, 10, 1, 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 6, 1, 11, 12, 1, 'com_config', 'com_config', '{}'
UNION ALL
SELECT 7, 1, 13, 16, 1, 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 8, 1, 17, 34, 1, 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.edit":{"4":1},"core.edit.state":{"5":1}}'
UNION ALL
SELECT 9, 1, 35, 36, 1, 'com_cpanel', 'com_cpanel', '{}'
UNION ALL
SELECT 10, 1, 37, 38, 1, 'com_installer', 'com_installer', '{"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'
UNION ALL
SELECT 11, 1, 39, 40, 1, 'com_languages', 'com_languages', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 12, 1, 41, 42, 1, 'com_login', 'com_login', '{}'
UNION ALL
SELECT 13, 1, 43, 44, 1, 'com_mailto', 'com_mailto', '{}'
UNION ALL
SELECT 14, 1, 45, 46, 1, 'com_massmail', 'com_massmail', '{}'
UNION ALL
SELECT 15, 1, 47, 48, 1, 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'
UNION ALL
SELECT 16, 1, 49, 50, 1, 'com_menus', 'com_menus', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 17, 1, 51, 52, 1, 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION ALL
SELECT 18, 1, 53, 54, 1, 'com_modules', 'com_modules', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 19, 1, 55, 58, 1, 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 20, 1, 59, 60, 1, 'com_plugins', 'com_plugins', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 21, 1, 61, 62, 1, 'com_redirect', 'com_redirect', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 22, 1, 63, 64, 1, 'com_search', 'com_search', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 23, 1, 65, 66, 1, 'com_templates', 'com_templates', '{"core.admin":{"7":1}}'
UNION ALL
SELECT 24, 1, 67, 70, 1, 'com_users', 'com_users', '{"core.admin":{"7":1},"core.edit.own":{"6":1}}'
UNION ALL
SELECT 26, 1, 75, 76, 1, 'com_wrapper', 'com_wrapper', '{}'
UNION ALL
SELECT 27, 8, 18, 25, 2, 'com_content.category.2', 'Uncaterised', '{}'
UNION ALL
SELECT 28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncaterised', '{}'
UNION ALL
SELECT 29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncaterised', '{}'
UNION ALL
SELECT 30, 19, 56, 57, 2, 'com_newsfeeds.category.5', 'Uncaterised', '{}'
UNION ALL
SELECT 32, 24, 68, 69, 2, 'com_users.category.7', 'Uncaterised', '{}'
UNION ALL
SELECT 33, 1, 77, 78, 1, 'com_finder', 'com_finder', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION ALL
SELECT 34, 27, 19, 20, 3, 'com_content.article.1', 'Home Page Title', '{}'
UNION ALL
SELECT 35, 27, 21, 22, 3, 'com_content.article.2', 'About Us', '{}'
UNION ALL
SELECT 36, 8, 26, 33, 2, 'com_content.category.8', 'News', '{}'
UNION ALL
SELECT 37, 36, 27, 28, 3, 'com_content.article.3', 'Article 1 Title', '{}'
UNION ALL
SELECT 38, 36, 29, 30, 3, 'com_content.article.4', 'Article 2 Title', '{}'
UNION ALL
SELECT 39, 36, 31, 32, 3, 'com_content.article.5', 'Article 3 Title ', '{}'
UNION ALL
SELECT 40, 27, 23, 24, 3, 'com_content.article.6', 'Creating Your Site', '{}'
UNION ALL
SELECT 41, 1, 79, 80, 1, 'com_joomlaupdate', 'com_joomlaupdate', '{}'
UNION ALL
SELECT 42, 1, 81, 82, 1, 'com_ajax', 'com_ajax', '{}'
UNION ALL
SELECT 43, 1, 83, 84, 1, 'com_postinstall', 'com_postinstall','{}';

SET IDENTITY_INSERT [#__assets] OFF;

-- Table #__categories

TRUNCATE TABLE [#__categories];

SET IDENTITY_INSERT [#__categories] ON;

INSERT INTO [#__categories] ( [id], [asset_id], [parent_id], [lft], [rgt], [level], [path], [extension], [title], [alias], [note], [description], [published], [checked_out], [checked_out_time], [access], [params], [metadesc], [metakey], [metadata], [created_user_id], [created_time], [modified_user_id], [modified_time], [hits], [language])
SELECT 1, 0, 0, 0, 15, 0, '', 'system', 'ROOT', 'root', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{}', '', '', '', 0, '2009-10-18 16:07:09', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 2, 27, 1, 1, 2, 1, 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:26:37', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 3, 28, 1, 3, 4, 1, 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":"","foobar":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:27:35', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 4, 29, 1, 5, 6, 1, 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:27:57', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 5, 30, 1, 7, 8, 1, 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:28:15', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 7, 32, 1, 11, 12, 1, 'uncategorised', 'com_users.notes', 'Uncategorised', 'uncategorised', '', '', 1, 0, '1900-01-01 00:00:00', 1, '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', 42, '2010-06-28 13:28:33', 0, '1900-01-01 00:00:00', 0, '*'
UNION ALL
SELECT 8, 36, 1, 13, 14, 1, 'news', 'com_content', 'News', 'news', '', '<p>This is the latest news from us.</p><p>You can edit this description in the Content Category Manager.</p><p>This will show the most recent article. You can easily change it to show more if you wish.</p><p>The module on the left shows a list of older articles.</p>', 1, 0, '1900-01-01 00:00:00', 1, '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', 42, '2012-01-04 03:09:08', 42, '2012-01-04 04:32:05', 0, '*';

SET IDENTITY_INSERT [#__categories] OFF;

-- Table #__contact_details

TRUNCATE TABLE [#__contact_details];

SET IDENTITY_INSERT [#__contact_details] ON;

INSERT  INTO [#__contact_details] ([id], [name], [alias], [con_position], [address], [suburb], [state], [country], [postcode], [telephone], [fax], [misc], [image], [email_to], [default_con], [published], [checked_out], [checked_out_time], [ordering], [params], [user_id], [catid], [access], [mobile], [webpage], [sortname1], [sortname2], [sortname3], [language], [created], [created_by], [created_by_alias], [modified], [modified_by], [metakey], [metadesc], [metadata], [featured], [xreference], [publish_up], [publish_down], [version], [hits])
SELECT 1, 'Contact Name Here', 'name', 'Position', 'Street Address', 'Suburb', 'State', 'Country', 'Zip Code', 'Telephone', 'Fax', '<p>Information about or by the contact.</p>', 'images/powered_by.png', 'email@example.com', 1, 1, 0, '1900-01-01 00:00:00', 1, '{"show_contact_category":"","show_contact_list":"","presentation_style":"","show_name":"","show_position":"","show_email":"","show_street_address":"","show_suburb":"","show_state":"","show_postcode":"","show_country":"","show_telephone":"","show_mobile":"","show_fax":"","show_webpage":"","show_misc":"","show_image":"","allow_vcard":"","show_articles":"","show_profile":"","show_links":"1","linka_name":"Twitter","linka":"https://twitter.com/joomla","linkb_name":"YouTube","linkb":"https://www.youtube.com/user/joomla","linkc_name":"Facebook","linkc":"https://www.facebook.com/joomla","linkd_name":"FriendFeed","linkd":"http://friendfeed.com/joomla","linke_name":"Scribd","linke":"https://www.scribd.com/user/504592/Joomla","contact_layout":"","show_email_form":"","show_email_copy":"","banned_email":"","banned_subject":"","banned_text":"","validate_session":"","custom_reply":"","redirect":""}', 0, 16, 1, '', '', 'last', 'first', 'middle', 'en-GB', '2011-01-01 00:00:01', 42, 'Joomla', '1900-01-01 00:00:00', 0, '', '', '{"robots":"","rights":""}', 1, '', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 0;

SET IDENTITY_INSERT [#__contact_details] OFF;

-- Table #__content

TRUNCATE TABLE [#__content];

SET IDENTITY_INSERT [#__content] ON;

INSERT INTO [#__content] ([id], [asset_id], [title], [alias],  [introtext], [fulltext], [state],  [catid], [created], [created_by], [created_by_alias], [modified], [modified_by], [checked_out], [checked_out_time], [publish_up], [publish_down], [images], [urls], [attribs], [version],  [ordering], [metakey], [metadesc], [access], [hits], [metadata], [featured], [language], [xreference])
SELECT 1, 89, 'Administrator Components', 'administrator-components', '<p>All components are also used in the administrator area of your website. In addition to the ones listed here, there are components in the administrator that do not have direct front end displays, but do help shape your site. The most important ones for most users are</p><ul><li>Media Manager</li><li>Extensions Manager</li><li>Menu Manager</li><li>Global Configuration</li><li>Banners</li><li>Redirect</li></ul><hr title="Media Manager" alt="Media Manager" class="system-pagebreak" style="color: gray; border: 1px dashed gray;" /><p> </p><h3>Media Manager</h3><p>The media manager component lets you upload and insert images into content throughout your site. Optionally, you can enable the flash uploader which will allow you to to upload multiple images. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Content_Media_Manager">Help</a></p><hr title="Extensions Manager" alt="Extensions Manager" class="system-pagebreak" style="color: gray; border: 1px dashed gray;" /><h3>Extensions Manager</h3><p>The extensions manager lets you install, update, uninstall and manage all of your extensions. The extensions manager has been extensively redesigned, although the core install and uninstall functionality remains the same as in Joomla! 1.5. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Extensions_Extension_Manager_Install">Help</a></p><hr title="Menu Manager" alt="Menu Manager" class="system-pagebreak" style="color: gray; border: 1px dashed gray;" /><h3>Menu Manager</h3><p>The menu manager lets you create the menus you see displayed on your site. It also allows you to assign modules and template styles to specific menu links. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Menus_Menu_Manager">Help</a></p><hr title="Global Configuration" alt="Global Configuration" class="system-pagebreak" style="color: gray; border: 1px dashed gray;" /><h3>Global Configuration</h3><p>The global configuration is where the site administrator configures things such as whether search engine friendly urls are enabled, the site meta data (descriptive text used by search engines and indexers) and other functions. For many beginning users simply leaving the settings on default is a good way to begin, although when your site is ready for the public you will want to change the meta data to match its content. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Site_Global_Configuration">Help</a></p><hr title="Banners" alt="Banners" class="system-pagebreak" style="color: gray; border: 1px dashed gray;" /><h3>Banners</h3><p>The banners component provides a simple way to display a rotating image in a module and, if you wish to have advertising, a way to track the number of times an image is viewed and clicked. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Components_Banners_Banners_Edit">Help</a></p><hr title="Redirect" class="system-pagebreak" /><h3><br />Redirect</h3><p>The redirect component is used to manage broken links that produce Page Not Found (404) errors. If enabled it will allow you to redirect broken links to specific pages. It can also be used to manage migration related URL changes. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Components_Redirect_Manager">Help</a></p>', '', 1, 21, '2011-01-01T00:00:01.000', 42, 'Joomla', '1900-01-01 00:00:00', 0, 0, '1900-01-01 00:00:00', '2011-01-01T00:00:01.000', '1900-01-01 00:00:00', '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}', '{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 1, 7, '', '', 1, 0, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', ''
UNION ALL
SELECT 2, 90, 'Archive Module', 'archive-module', '<p>This module shows a list of the calendar months containing archived articles. After you have changed the status of an article to archived, this list will be automatically generated. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Extensions_Module_Manager_Articles_Archive" title="Archive Module">Help</a></p><div class="sample-module">{loadmodule articles_archive,Archived Articles}</div>', '', 1, 64, '2011-01-01T00:00:01.000', 42, 'Joomla', '1900-01-01 00:00:00', 0, 0, '1900-01-01 00:00:00', '2011-01-01T00:00:01.000', '1900-01-01 00:00:00', '', '', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","page_title":"","alternative_readmore":"","layout":""}', 1, 5, 'modules, content', '', 1, 0, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', ''
UNION ALL
SELECT 3, 91, 'Article Categories Module', 'article-categories-module', '<p>This module displays a list of categories from one parent category. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Extensions_Module_Manager_Articles_Categories" title="Categories Module">Help</a></p><div class="sample-module">{loadmodule articles_categories,Articles Categories}</div><p> </p>', '', 1, 64, '2011-01-01T00:00:01.000', 42, 'Joomla', '1900-01-01 00:00:00', 0, 0, '1900-01-01 00:00:00', '2011-01-01T00:00:01.000', '1900-01-01 00:00:00', '', '', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","page_title":"","alternative_readmore":"","layout":""}', 1, 6, 'modules, content', '', 1, 0, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', ''
UNION ALL
SELECT 4, 92, 'Articles Category Module', 'articles-category-module', '<p>This module allows you to display the articles in a specific category. <a href="https://help.joomla.org/proxy/index.php?keyref=Help16:Extensions_Module_Manager_Articles_Category">Help</a></p><div class="sample-module">{loadmodule articles_category,Articles Category}</div>', '', 1, 64, '2011-01-01T00:00:01.000', 42, 'Joomla', '1900-01-01 00:00:00', 0, 0, '1900-01-01 00:00:00', '2011-01-01T00:00:01.000', '1900-01-01 00:00:00', '', '', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","page_title":"","alternative_readmore":"","layout":""}', 1, 7, '', 'articles,content', 1, 0, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', ''
UNION ALL
SELECT 5, 101, 'Authentication', 'authentication', '<p>The authentication plugins operate when users login to your site or administrator. The Joomla! authentication plugin is in operation by default but you can enable Gmail or LDAP or install a plugin for a different system. An example is included that may be used to create a new authentication plugin.</p><p>Default on:</p><ul><li>Joomla <a href="https://help.joomla.org/proxy/index.php?amp;keyref=Help17:Extensions_Plugin_Manager_Edit#Authentication_-_GMail">Help</a></li></ul><p>Default off:</p><ul><li>Gmail <a href="https://help.joomla.org/proxy/index.php?amp;keyref=Help17:Extensions_Plugin_Manager_Edit#Authentication_-_GMail">Help</a></li><li>LDAP <a href="https://help.joomla.org/proxy/index.php?amp;keyref=Help17:Extensions_Plugin_Manager_Edit#Authentication_-_LDAP">Help</a></li></ul>', '', 1, 25, '2011-01-01T00:00:01.000', 42, 'Joomla', '1900-01-01 00:00:00', 0, 0, '1900-01-01 00:00:00', '2011-01-01T00:00:01.000', '1900-01-01 00:00:00', '{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}', '{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}', 1, 3, '', '', 1, 0, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', ''
UNION ALL
SELECT 6, 102, 'Australian Parks ', 'australian-parks', '<p><img src="images/sampledata/parks/banner_cradle.jpg" border="0" alt="Cradle Park Banner" /></p><p>Welcome!</p><p>This is a basic site about the beautiful and fascinating parks of Australia.</p><p>On this site you can read all about my travels to different parks, see photos, and find links to park websites.</p><p><em>This sample site is an example of using the core of Joomla! to create a basic website, whether a "brochure site,"  a personal blog, or as a way to present information on a topic you are interested in.</em></p><p><em> Read more about the site in the About Parks module.</em></p><p> </p>', '', 1, 26, '2011-01-01T00:00:01.000', 42, 'Joomla', '1900-01-01 00:00:00', 0, 0, '1900-01-01 00:00:00', '2011-01-01T00:00:01.000', '1900-01-01 00:00:00', '', '', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":""}', 1, 1, '', '', 1, 0, '{"robots":"","author":"","rights":"","xreference":""}', 0, '*', '';

SET IDENTITY_INSERT [#__content] OFF;

-- Table #__menu

TRUNCATE TABLE [#__menu];

SET IDENTITY_INSERT [#__menu] ON;

INSERT INTO [#__menu] ( [id], [menutype], [title], [alias], [note], [path], [link], [type], [published], [parent_id], [level], [component_id],[checked_out], [checked_out_time], [browserNav], [access], [img], [template_style_id], [params], [lft], [rgt], [home], [language], [client_id])
SELECT 1, '', 'Menu_Item_Root', 'root', '', '', '', '', 1, 0, 0, 0, 0, '1900-01-01 00:00:00', 0, 0, '', 0, '', 0, 257, 0, '*', 0
UNION ALL
SELECT 2, 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', 0, 1, 1, 4, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners', 0, '', 5, 14, 0, '*', 1
UNION ALL
SELECT 3, 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', 0, 2, 2, 4, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners', 0, '', 6, 7, 0, '*', 1
UNION ALL
SELECT 4, 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', 0, 2, 2, 6, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-cat', 0, '', 8, 9, 0, '*', 1
UNION ALL
SELECT 5, 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', 0, 2, 2, 4, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-clients', 0, '', 10, 11, 0, '*', 1
UNION ALL
SELECT 6, 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', 0, 2, 2, 4, 0, '1900-01-01 00:00:00', 0, 0, 'class:banners-tracks', 0, '', 12, 13, 0, '*', 1
UNION ALL
SELECT 7, 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', 0, 1, 1, 8, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact', 0, '', 15, 20, 0, '*', 1
UNION ALL
SELECT 8, 'menu', 'com_contact', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', 0, 7, 2, 8, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact', 0, '', 16, 17, 0, '*', 1
UNION ALL
SELECT 9, 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', 0, 7, 2, 6, 0, '1900-01-01 00:00:00', 0, 0, 'class:contact-cat', 0, '', 18, 19, 0, '*', 1
UNION ALL
SELECT 10, 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', 0, 1, 1, 15, 0, '1900-01-01 00:00:00', 0, 0, 'class:messages', 0, '', 21, 26, 0, '*', 1
UNION ALL
SELECT 11, 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', 0, 10, 2, 15, 0, '1900-01-01 00:00:00', 0, 0, 'class:messages-add', 0, '', 22, 23, 0, '*', 1
UNION ALL
SELECT 13, 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 1, 1, 17, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 27, 32, 0, '*', 1
UNION ALL
SELECT 14, 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', 0, 13, 2, 17, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds', 0, '', 28, 29, 0, '*', 1
UNION ALL
SELECT 15, 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', 0, 13, 2, 6, 0, '1900-01-01 00:00:00', 0, 0, 'class:newsfeeds-cat', 0, '', 30, 31, 0, '*', 1
UNION ALL
SELECT 16, 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', 0, 1, 1, 24, 0, '1900-01-01 00:00:00', 0, 0, 'class:redirect', 0, '', 45, 46, 0, '*', 1
UNION ALL
SELECT 17, 'menu', 'com_search', 'Basic Search', '', 'Basic Search', 'index.php?option=com_search', 'component', 0, 1, 1, 19, 0, '1900-01-01 00:00:00', 0, 0, 'class:search', 0, '', 35, 36, 0, '*', 1
UNION ALL
SELECT 21, 'menu', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', 0, 1, 1, 27, 0, '1900-01-01 00:00:00', 0, 0, 'class:finder', 0, '', 33, 34, 0, '*', 1
UNION ALL
SELECT 22, 'menu', 'com_joomlaupdate', 'Joomla! Update', '', 'Joomla! Update', 'index.php?option=com_joomlaupdate', 'component', 1, 1, 1, 32, 0, '1900-01-01 00:00:00', 0, 0, 'class:joomlaupdate', 0, '', 41, 42, 0, '*', 1
UNION ALL
SELECT 23, 'menu', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', 0, 1, 1, 29, 0, '1900-01-01 00:00:00', 0, 0, 'class:tags', 0, '', 43, 44, 0, '*', 1
UNION ALL
SELECT 24, 'menu', 'com_postinstall', 'com_postinstall', '', 'Post-installation messages', 'index.php?option=com_postinstall', 'component', 0, 1, 1, 32, 0, '1900-01-01 00:00:00', 0, 0, 'class:postinstall', 0, '', 45, 46, 0, '*', 1
UNION ALL
SELECT 101, 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=article&id=1', 'component', 1, 1, 1, 22, 0, '1900-01-01 00:00:00', 0, 1, '', 0, '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 1, 2, 1, '*', 0
UNION ALL
SELECT 102, 'mainmenu', 'About Us', 'about-us', '', 'about-us', 'index.php?option=com_content&view=article&id=2', 'component', 1, 1, 1, 22, 0, '1900-01-01 00:00:00', 0, 1, '', 0, '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 3, 4, 0, '*', 0
UNION ALL
SELECT 103, 'mainmenu', 'News', 'news', '', 'news', 'index.php?option=com_content&view=category&layout=blog&id=8', 'component', 1, 1, 1, 22, 0, '1900-01-01 00:00:00', 0, 1, '', 0, '{"layout_type":"blog","show_category_title":"","show_description":"1","show_description_image":"","maxLevel":"","show_empty_categories":"","show_no_articles":"","show_subcat_desc":"","show_cat_num_articles":"","page_subheading":"","num_leading_articles":"1","num_intro_articles":"0","num_columns":"1","num_links":"0","multi_column_order":"","show_subcategory_content":"","orderby_pri":"","orderby_sec":"","order_date":"published","show_pagination":"0","show_pagination_results":"0","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 5, 6, 0, '*', 0
UNION ALL
SELECT 104, 'mainmenu', 'Login', 'login', '', 'login', 'index.php?option=com_users&view=login', 'component', 1, 1, 1, 25, 0, '1900-01-01 00:00:00', 0, 4, '', 0, '{"login_redirect_url":"","logindescription_show":"1","login_description":"","login_image":"","logout_redirect_url":"","logoutdescription_show":"1","logout_description":"","logout_image":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 9, 10, 0, '*', 0
UNION ALL
SELECT 105, 'mainmenu', 'Edit Profile', 'edit-profile', '', 'edit-profile', 'index.php?option=com_users&view=profile&layout=edit', 'component', 1, 1, 1, 25, 0, '1900-01-01 00:00:00', 0, 2, '', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 11, 12, 0, '*', 0
UNION ALL
SELECT 106, 'mainmenu', 'Contact Us', 'contact-us', '', 'contact-us', 'index.php?option=com_contact&view=contact&id=1', 'component', 1, 1, 1, 8, 0, '1900-01-01 00:00:00', 0, 1, '', 0, '{"presentation_style":"","show_contact_category":"","show_contact_list":"","show_name":"","show_position":"","show_email":"","show_street_address":"","show_suburb":"","show_state":"","show_postcode":"","show_country":"","show_telephone":"","show_mobile":"","show_fax":"","show_webpage":"","show_misc":"","show_image":"","allow_vcard":"","show_articles":"","show_links":"","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","show_email_form":"","show_email_copy":"","banned_email":"","banned_subject":"","banned_text":"","validate_session":"","custom_reply":"","redirect":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 7, 8, 0, '*', 0
UNION ALL
SELECT 107, 'mainmenu', 'Administrator', '2012-01-04-04-05-24', '', '2012-01-04-04-05-24', 'administrator', 'url', 1, 1, 1, 0, 0, '1900-01-01 00:00:00', 1, 3, '', 0, '{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1}', 55, 56, 0, '*', 0
UNION ALL
SELECT 108, 'mainmenu', 'Creating Your Site', 'creating-your-site', '', 'creating-your-site', 'index.php?option=com_content&view=article&id=6', 'component', 1, 1, 1, 22, 0, '1900-01-01 00:00:00', 0, 3, '', 0, '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 57, 58, 0, '*', 0
UNION ALL
SELECT 109, 'mainmenu', 'Create an Article', 'create-an-article', '', 'create-an-article', 'index.php?option=com_content&view=form&layout=edit', 'component', 1, 1, 1, 22, 0, '1900-01-01 00:00:00', 0, 3, '', 0, '{"enable_category":"0","catid":"2","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', 13, 14, 0, '*', 0;

SET IDENTITY_INSERT [#__menu] OFF;

-- Table #__menu_types

TRUNCATE TABLE [#__menu_types];

SET IDENTITY_INSERT [#__menu_types] ON;

INSERT INTO[#__menu_types] ([id], [menutype], [title], [description])
SELECT 1, 'mainmenu', 'Main Menu', 'The main menu for the site';

SET IDENTITY_INSERT [#__menu_types] OFF;

-- Table #__modules

TRUNCATE TABLE [#__modules];

SET IDENTITY_INSERT [#__modules] ON;

INSERT INTO [#__modules] ( [id], [title], [note], [content], [ordering], [position], [checked_out], [checked_out_time], [publish_up], [publish_down], [published], [module], [access], [showtitle], [params], [client_id], [language])
SELECT 1, 'Main Menu', '', '', 1, 'position-1', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_menu', 1, 1, '{"menutype":"mainmenu","startLevel":"1","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"_:default","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'
UNION ALL
SELECT 2, 'Login', '', '', 1, 'login', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_login', 1, 1, '', 1, '*'
UNION ALL
SELECT 3 'Popular Articles', '', '', 3, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_popular', 3, 1, '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'
UNION ALL
SELECT 4, 'Recently Added Articles', '', '', 4, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_latest', 3, 1, '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'
UNION ALL
SELECT 8, 'Toolbar', '', '', 1, 'toolbar', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_toolbar', 3, 1, '', 1, '*'
UNION ALL
SELECT 9, 'Quick Icons', '', '', 1, 'icon', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_quickicon', 3, 1, '', 1, '*'
UNION ALL
SELECT 10, 'Logged-in Users', '', '', 2, 'cpanel', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_logged', 3, 1, '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'
UNION ALL
SELECT 12, 'Admin Menu', '', '', 1, 'menu', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_menu', 3, 1, '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', 1, '*'
UNION ALL
SELECT 13, 'Admin Submenu', '', '', 1, 'submenu', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_submenu', 3, 1, '', 1, '*'
UNION ALL
SELECT 14, 'User Status', '', '', 2, 'status', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_status', 3, 1, '', 1, '*'
UNION ALL
SELECT 15, 'Title', '', '', 1, 'title', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_title', 3, 1, '', 1, '*'
UNION ALL
SELECT 16, 'Login Form', '', '', 7, 'position-7', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 0, 'mod_login', 1, 1, '{"greeting":"1","name":"0"}', 0, '*'
UNION ALL
SELECT 17, 'Breadcrumbs', '', '', 1, 'position-2', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_breadcrumbs', 1, 1, '{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"0","cache_time":"0","cachemode":"itemid"}', 0, '*'
UNION ALL
SELECT 79, 'Multilanguage status', '', '', 1, 'status', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 0, 'mod_multilangstatus', 3, 1, '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', 1, '*'
UNION ALL
SELECT 80, 'Search', '', '', 0, 'position-0', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_search', 1, 0, '{"label":"","width":"20","text":"","button":"","button_pos":"right","imagebutton":"","button_text":"","opensearch":"1","opensearch_title":"","set_itemid":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'
UNION ALL
SELECT 81, 'Header Image', '', '<div style="margin-left: 10px;"><p>This is the Header module. You can edit in the the Module Manager in your Administrator.</p><p>Put a large image here. if you make an image that is about 1050 px wide by 180 px high it will fit nicely. You could put text or a mix of images and text if you prefer.</p></div>', 1, 'position-15', 42, '2012-01-17 15:07:56', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_custom', 1, 1, '{"prepare_content":"1","backgroundimage":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}', 0, '*'
UNION ALL
SELECT 82, 'Other News', '', '', 0, 'position-7', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_articles_news', 1, 1, '{"catid":["8"],"image":"0","item_title":"1","link_titles":"1","item_heading":"h4","showLastSeparator":"0","readmore":"0","count":"5","ordering":"a.publish_up","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}', 0, '*'
UNION ALL
SELECT 83, 'Side Module', '', '<p>This is a module where you might want to add some more information or an image, a link to your social media presence, or whatever makes sense for your site.</p><p>You can edit this module in the module manager. Look for the Side Module.</p>', 1, 'position-7', 0, '1900-01-01 00:00:00', '1900-01-01 00:00:00', '1900-01-01 00:00:00', 1, 'mod_custom', 1, 1, '{"prepare_content":"1","backgroundimage":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}', 0, '*';

SET IDENTITY_INSERT [#__modules] OFF;

-- Table #__modules_menu

TRUNCATE TABLE [#__modules_menu];

INSERT INTO [#__modules_menu] ([moduleid], [menuid])
SELECT 1, 0
UNION ALL
SELECT 2, 0
UNION ALL
SELECT 3, 0
UNION ALL
SELECT 4, 0
UNION ALL
SELECT 6, 0
UNION ALL
SELECT 7, 0
UNION ALL
SELECT 8, 0
UNION ALL
SELECT 9, 0
UNION ALL
SELECT 10, 0
UNION ALL
SELECT 12, 0
UNION ALL
SELECT 13, 0
UNION ALL
SELECT 14, 0
UNION ALL
SELECT 15, 0
UNION ALL
SELECT 16, 0
UNION ALL
SELECT 17, 0
UNION ALL
SELECT 79, 0
UNION ALL
SELECT 80, 0
UNION ALL
SELECT 81, 0
UNION ALL
SELECT 82, 103
UNION ALL
SELECT 83, 0
UNION ALL
SELECT 85, 0;

-- Table #__template_styles

TRUNCATE TABLE [#__template_styles];

SET IDENTITY_INSERT [#__template_styles] ON;

INSERT INTO [#__template_styles] ([id], [template], [client_id], [home], [title], [params])
SELECT 4, 'beez3', 0, 0, 'Beez3 - Default', '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\\/joomla_black.png","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","templatecolor":"personal","html5":"0"}'
UNION ALL
SELECT 5, 'hathor', 1, 0, 'Hathor - Default', '{"showSiteName":"0","colourChoice":"","boldText":"0"}'
UNION ALL
SELECT 7, 'protostar', 0, '1', 'My Default Style (Protostar)', '{"templateColor":"#696969","templateBackgroundColor":"#E3E3E3","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'
UNION ALL
SELECT 8, 'isis', 1, '1', 'isis - Default', '{"templateColor":"#000000","logoFile":"","admin_menus":1,"displayHeader":1,"statusFixed":1,"stickyToolbar":1}';

SET IDENTITY_INSERT [#__template_styles] OFF;
