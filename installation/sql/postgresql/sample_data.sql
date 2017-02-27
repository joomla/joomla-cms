TRUNCATE "#__assets" RESTART IDENTITY;
TRUNCATE "#__menu" RESTART IDENTITY;
TRUNCATE "#__menu_types" RESTART IDENTITY;
TRUNCATE "#__modules" RESTART IDENTITY;
TRUNCATE "#__modules_menu" RESTART IDENTITY;

--
-- Dumping data for table #__assets
--
INSERT INTO "#__assets" VALUES
(1, 0, 0, 117, 0, 'root.1', 'Root Asset', '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'),
(2, 1, 1, 2, 1, 'com_admin', 'com_admin', '{}'),
(3, 1, 3, 6, 1, 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(4, 1, 7, 8, 1, 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(5, 1, 9, 10, 1, 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(6, 1, 11, 12, 1, 'com_config', 'com_config', '{}'),
(7, 1, 13, 16, 1, 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(8, 1, 17, 22, 1, 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.edit":{"4":1},"core.edit.state":{"5":1}}'),
(9, 1, 23, 24, 1, 'com_cpanel', 'com_cpanel', '{}'),
(10, 1, 25, 26, 1, 'com_installer', 'com_installer', '{"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'),
(11, 1, 27, 28, 1, 'com_languages', 'com_languages', '{"core.admin":{"7":1}}'),
(12, 1, 29, 30, 1, 'com_login', 'com_login', '{}'),
(13, 1, 31, 32, 1, 'com_mailto', 'com_mailto', '{}'),
(15, 1, 33, 34, 1, 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'),
(16, 1, 35, 36, 1, 'com_menus', 'com_menus', '{"core.admin":{"7":1}}'),
(17, 1, 37, 38, 1, 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(18, 1, 39, 84, 1, 'com_modules', 'com_modules', '{"core.admin":{"7":1}}'),
(19, 1, 85, 88, 1, 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(20, 1, 89, 90, 1, 'com_plugins', 'com_plugins', '{"core.admin":{"7":1}}'),
(21, 1, 91, 92, 1, 'com_redirect', 'com_redirect', '{"core.admin":{"7":1}}'),
(22, 1, 93, 94, 1, 'com_search', 'com_search', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(23, 1, 95, 96, 1, 'com_templates', 'com_templates', '{"core.admin":{"7":1}}'),
(24, 1, 97, 100, 1, 'com_users', 'com_users', '{"core.admin":{"7":1}}'),
(26, 1, 101, 102, 1, 'com_wrapper', 'com_wrapper', '{}'),
(27, 8, 18, 21, 2, 'com_content.category.2', 'Uncategorised', '{}'),
(28, 3, 4, 5, 2, 'com_banners.category.3', 'Uncategorised', '{}'),
(29, 7, 14, 15, 2, 'com_contact.category.4', 'Uncategorised', '{}'),
(30, 19, 86, 87, 2, 'com_newsfeeds.category.5', 'Uncategorised', '{}'),
(32, 24, 98, 99, 2, 'com_users.category.7', 'Uncategorised', '{}'),
(33, 1, 103, 104, 1, 'com_finder', 'com_finder', '{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(34, 1, 105, 106, 1, 'com_joomlaupdate', 'com_joomlaupdate', '{}'),
(35, 1, 107, 108, 1, 'com_tags', 'com_tags', '{}'),
(36, 1, 109, 110, 1, 'com_contenthistory', 'com_contenthistory', '{}'),
(37, 1, 111, 112, 1, 'com_ajax', 'com_ajax', '{}'),
(38, 1, 113, 114, 1, 'com_postinstall', 'com_postinstall', '{}'),
(39, 18, 40, 41, 2, 'com_modules.module.1', 'Main Menu', '{}'),
(40, 18, 42, 43, 2, 'com_modules.module.2', 'Login', '{}'),
(41, 18, 44, 45, 2, 'com_modules.module.3', 'Popular Articles', '{}'),
(42, 18, 46, 47, 2, 'com_modules.module.4', 'Recently Added Articles', '{}'),
(43, 18, 48, 49, 2, 'com_modules.module.8', 'Toolbar', '{}'),
(44, 18, 50, 51, 2, 'com_modules.module.9', 'Quick Icons', '{}'),
(45, 18, 52, 53, 2, 'com_modules.module.10', 'Logged-in Users', '{}'),
(46, 18, 54, 55, 2, 'com_modules.module.12', 'Admin Menu', '{}'),
(47, 18, 56, 57, 2, 'com_modules.module.13', 'Admin Submenu', '{}'),
(48, 18, 58, 59, 2, 'com_modules.module.14', 'User Status', '{}'),
(49, 18, 60, 61, 2, 'com_modules.module.15', 'Title', '{}'),
(50, 18, 62, 63, 2, 'com_modules.module.16', 'Login Form', '{}'),
(51, 18, 64, 65, 2, 'com_modules.module.17', 'Breadcrumbs', '{}'),
(52, 18, 66, 67, 2, 'com_modules.module.79', 'Multilanguage status', '{}'),
(53, 18, 68, 69, 2, 'com_modules.module.86', 'Joomla Version', '{}'),
(54, 18, 70, 71, 2, 'com_modules.module.87', 'Popular Tags', '{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(55, 18, 72, 73, 2, 'com_modules.module.88', 'Site Information', '{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(56, 18, 74, 75, 2, 'com_modules.module.89', 'Release News', '{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(57, 18, 76, 77, 2, 'com_modules.module.90', 'Latest Articles', '{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(58, 18, 78, 79, 2, 'com_modules.module.91', 'User Menu', '{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(59, 18, 80, 81, 2, 'com_modules.module.92', 'Image Module', '{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(60, 18, 82, 83, 2, 'com_modules.module.93', 'Search', '{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(61, 27, 19, 20, 3, 'com_content.article.1', 'Getting Started', '{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(62, 1, 115, 116, 1, '#__ucm_content.1', '#__ucm_content.1', '{}');

SELECT setval('#__assets_id_seq', max(id)) FROM #__assets;

--
-- Dumping data for table #__content
--
INSERT INTO  "#__content" VALUES
(1,35,'Getting Started','getting-started','<p>It\'s easy to get started creating your website. Knowing some of the basics will help.</p><h3>What is a Content Management System?</h3><p>A content management system is software that allows you to create and manage webpages easily by separating the creation of your content from the mechanics required to present it on the web.</p><p>In this site, the content is stored in a <em>database</em>. The look and feel are created by a <em>template</em>. Joomla! brings together the template and your content to create web pages.</p><h3>Logging in</h3><p>To login to your site use the user name and password that were created as part of the installation process. Once logged-in you will be able to create and edit articles and modify some settings.</p><h3>Creating an article</h3><p>Once you are logged-in, a new menu will be visible. To create a new article, click on the "Submit Article" link on that menu.</p><p>The new article interface gives you a lot of options, but all you need to do is add a title and put something in the content area. To make it easy to find, set the state to published.</p><div>You can edit an existing article by clicking on the edit icon (this only displays to users who have the right to edit).</div><h3>Template, site settings, and modules</h3><p>The look and feel of your site is controlled by a template. You can change the site name, background colour, highlights colour and more by editing the template settings. Click the "Template Settings" in the user menu.Â </p><p>The boxes around the main content of the site are called modules. Â You can modify modules on the current page by moving your cursor to the module and clicking the edit link. Always be sure to save and close any module you edit.</p><p><span style="line-height: 1.3em;">You can change some site settings such as the site name and description by clicking on the "Site Settings" link.</span></p><p>More advanced options for templates, site settings, modules, and more are available in the site administrator.</p><h3>Site and Administrator</h3><p>Your site actually has two separate sites. The site (also called the front end) is what visitors to your site will see. The administrator (also called the back end) is only used by people managing your site. You can access the administrator by clicking the "Site Administrator" link on the "User Menu" menu (visible once you login) or by adding /administrator to the end of your domain name. The same user name and password are used for both sites.</p><h3>Learn more</h3><p>There is much more to learn about how to use Joomla! to create the website you envision. You can learn much more at the <a href="https://docs.joomla.org/" target="_blank">Joomla! documentation site</a> and on the<a href="https://forum.joomla.org/" target="_blank"> Joomla! forums</a>.</p>','',1,2,'2011-01-01 00:00:00',209,'','1970-01-01 00:00:00',209,0,'1970-01-01 00:00:00','2011-01-01 00:00:00','1970-01-01 00:00:00','{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}','{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}','{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"0","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":""}',9,0,'','',1,78,'{"robots":"","author":"","rights":"","xreference":""}',0,'*','');

SELECT setval('#__content_id_seq', max(id)) FROM #__content;

--
-- Dumping data for table #__menu
--
INSERT INTO "#__menu" VALUES
(1,'','Menu_Item_Root','root','','','','',1,0,0,0,0,'1970-01-01 00:00:00',0,0,'',0,'',0,229,0,'*',0),
(2,'menu','com_banners','Banners','','Banners','index.php?option=com_banners','component',0,1,1,4,0,'1970-01-01 00:00:00',0,0,'class:banners',0,'',3,12,0,'*',1),
(3,'menu','com_banners','Banners','','Banners/Banners','index.php?option=com_banners','component',0,2,2,4,0,'1970-01-01 00:00:00',0,0,'class:banners',0,'',4,5,0,'*',1),
(4,'menu','com_banners_categories','Categories','','Banners/Categories','index.php?option=com_categories&extension=com_banners','component',0,2,2,6,0,'1970-01-01 00:00:00',0,0,'class:banners-cat',0,'',6,7,0,'*',1),
(5,'menu','com_banners_clients','Clients','','Banners/Clients','index.php?option=com_banners&view=clients','component',0,2,2,4,0,'1970-01-01 00:00:00',0,0,'class:banners-clients',0,'',8,9,0,'*',1),
(6,'menu','com_banners_tracks','Tracks','','Banners/Tracks','index.php?option=com_banners&view=tracks','component',0,2,2,4,0,'1970-01-01 00:00:00',0,0,'class:banners-tracks',0,'',10,11,0,'*',1),
(7,'menu','com_contact','Contacts','','Contacts','index.php?option=com_contact','component',0,1,1,8,0,'1970-01-01 00:00:00',0,0,'class:contact',0,'',13,18,0,'*',1),
(8,'menu','com_contact','Contacts','','Contacts/Contacts','index.php?option=com_contact','component',0,7,2,8,0,'1970-01-01 00:00:00',0,0,'class:contact',0,'',14,15,0,'*',1),
(9,'menu','com_contact_categories','Categories','','Contacts/Categories','index.php?option=com_categories&extension=com_contact','component',0,7,2,6,0,'1970-01-01 00:00:00',0,0,'class:contact-cat',0,'',16,17,0,'*',1),
(10,'menu','com_messages','Messaging','','Messaging','index.php?option=com_messages','component',0,1,1,15,0,'1970-01-01 00:00:00',0,0,'class:messages',0,'',19,24,0,'*',1),
(11,'menu','com_messages_add','New Private Message','','Messaging/New Private Message','index.php?option=com_messages&task=message.add','component',0,10,2,15,0,'1970-01-01 00:00:00',0,0,'class:messages-add',0,'',20,21,0,'*',1),
(13,'menu','com_newsfeeds','News Feeds','','News Feeds','index.php?option=com_newsfeeds','component',0,1,1,17,0,'1970-01-01 00:00:00',0,0,'class:newsfeeds',0,'',25,30,0,'*',1),
(14,'menu','com_newsfeeds_feeds','Feeds','','News Feeds/Feeds','index.php?option=com_newsfeeds','component',0,13,2,17,0,'1970-01-01 00:00:00',0,0,'class:newsfeeds',0,'',26,27,0,'*',1),
(15,'menu','com_newsfeeds_categories','Categories','','News Feeds/Categories','index.php?option=com_categories&extension=com_newsfeeds','component',0,13,2,6,0,'1970-01-01 00:00:00',0,0,'class:newsfeeds-cat',0,'',28,29,0,'*',1),
(16,'menu','com_redirect','Redirect','','Redirect','index.php?option=com_redirect','component',0,1,1,24,0,'1970-01-01 00:00:00',0,0,'class:redirect',0,'',43,44,0,'*',1),
(17,'menu','com_search','Basic Search','','Basic Search','index.php?option=com_search','component',0,1,1,19,0,'1970-01-01 00:00:00',0,0,'class:search',0,'',33,34,0,'*',1),
(21,'menu','com_finder','Smart Search','','Smart Search','index.php?option=com_finder','component',0,1,1,27,0,'1970-01-01 00:00:00',0,0,'class:finder',0,'',31,32,0,'*',1),
(22,'menu','com_joomlaupdate','Joomla! Update','','Joomla! Update','index.php?option=com_joomlaupdate','component',0,1,1,28,0,'1970-01-01 00:00:00',0,0,'class:joomlaupdate',0,'',31,32,0,'*',1),
(23,'menu','com_tags','com-tags','','com-tags','index.php?option=com_tags','component',0,1,1,29,0,'1970-01-01 00:00:00',0,1,'class:tags',0,'',221,222,0,'',1),
(24,'main','com_postinstall','Post-installation messages','','Post-installation messages','index.php?option=com_postinstall','component',0,1,1,32,0,'1970-01-01 00:00:00',0,1,'class:postinstall',0,'',223,224,0,'*',1),
(201,'usermenu','Your Profile','your-profile','','your-profile','index.php?option=com_users&view=profile','component',1,1,1,25,0,'1970-01-01 00:00:00',0,2,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',213,214,0,'*',0),
(207,'top','Joomla.org','joomlaorg','','joomlaorg','https://www.joomla.org/','url',1,1,1,0,0,'1970-01-01 00:00:00',0,1,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":""}',211,212,0,'*',0),
(435,'mainmenu','Home','homepage','','homepage','index.php?option=com_content&view=article&id=1','component',1,1,1,22,0,'1970-01-01 00:00:00',0,1,'',0,'{"show_title":"1","link_titles":"","show_intro":"","info_block_position":"0","show_category":"0","link_category":"0","show_parent_category":"0","link_parent_category":"0","show_author":"0","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"0","show_item_navigation":"0","show_vote":"","show_icons":"0","show_print_icon":"0","show_email_icon":"0","show_hits":"0","show_noauth":"","urls_position":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',1,2,1,'*',0),
(448,'usermenu','Site Administrator','site-administrator','','site-administrator','administrator','url',1,1,1,0,0,'1970-01-01 00:00:00',1,6,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1}',219,220,0,'*',0),
(449,'usermenu','Submit an Article','submit-an-article','','submit-an-article','index.php?option=com_content&view=form&layout=edit','component',1,1,1,22,0,'1970-01-01 00:00:00',0,3,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',215,216,0,'*',0),
(464,'top','Home','home','','home','index.php?Itemid=','alias',1,1,1,0,0,'1970-01-01 00:00:00',0,1,'',0,'{"aliasoptions":"435","menu-anchor_title":"","menu-anchor_css":"","menu_image":""}',205,206,0,'*',0),
(467,'usermenu','Template Settings','template-settings','','template-settings','index.php?option=com_config&view=templates&controller=config.display.templates','component',1,1,1,23,0,'1970-01-01 00:00:00',0,6,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',225,226,0,'*',0),
(468,'usermenu','Site Settings','site-settings','','site-settings','index.php?option=com_config&view=config&controller=config.display.config','component',1,1,1,23,0,'1970-01-01 00:00:00',0,6,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',227,228,0,'*',0);

SELECT setval('#__menu_id_seq', max(id)) FROM #__menu;

--
-- Dumping data for table #__menu_types
--
INSERT INTO "#__menu_types" ("id", "menutype", "title", "description") VALUES
(1,'mainmenu','Main Menu','The main menu for the site'),
(2,'usermenu','User Menu','A Menu for logged-in Users');

SELECT setval('#__menu_types_id_seq', max(id)) FROM #__menu_types;

--
-- Dumping data for table #__modules
--
INSERT INTO "#__modules" VALUES
(1,0,'Main Menu','','',1,'position-1',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_menu',1,1,'{"menutype":"mainmenu","active":"","startLevel":"1","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":" nav-pills","window_open":"","layout":"_:default","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(2,0,'Login','','',1,'login',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_login',1,1,'',1,'*'),
(3,0,'Popular Articles','','',4,'cpanel',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',0,'mod_popular',3,1,'{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(4,0,'My Recently Added Articles','','',1,'cpanel',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_latest',2,1,'{"count":"5","ordering":"c_dsc","catid":"","user_id":"by_me","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(8,0,'Toolbar','','',1,'toolbar',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_toolbar',2,1,'{"layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(9,0,'Quick Links','','',1,'icon',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_quickicon',2,1,'{"context":"mod_quickicon","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(10,0,'Logged-in Users','','',2,'cpanel',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_logged',2,1,'{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}',1,'*'),
(12,0,'Admin Menu','','',1,'menu',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_menu',2,1,'{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}',1,'*'),
(13,0,'Admin Submenu','','',1,'submenu',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_submenu',2,1,'',1,'*'),
(14,0,'User Status','','',2,'status',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_status',2,1,'',1,'*'),
(15,0,'Title','','',1,'title',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_title',2,1,'',1,'*'),
(16,0,'Login Form','','',7,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_login',1,1,'{"greeting":"1","name":"0"}',0,'*'),
(17,0,'Breadcrumbs','','',1,'position-2',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_breadcrumbs',1,1,'{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"0","cache_time":"0","cachemode":"itemid"}',0,'*'),
(19,0,'User Menu','','',3,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_menu',2,1,'{"menutype":"usermenu","active":"","startLevel":"1","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"_:default","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(27,0,'Archived Articles','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_archive',1,1,'{"count":"10","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}',0,'*'),
(28,0,'Latest Articles','','',1,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_latest',1,1,'{"catid":[""],"count":"8","show_featured":"","ordering":"c_dsc","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(29,0,'Articles Most Read','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_popular',1,1,'{"catid":["26","29"],"count":"5","show_front":"1","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}',0,'*'),
(30,0,'Feed Display','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_feed',1,1,'{"rssurl":"https:\\/\\/community.joomla.org\\/blogs\\/community.feed?type=rss","rssrtl":"0","rsstitle":"1","rssdesc":"1","rssimage":"1","rssitems":"3","rssitemdesc":"1","word_count":"0","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900"}',0,'*'),
(31,0,'News Flash','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_news',1,1,'{"catid":["19"],"image":"0","item_title":"0","link_titles":"","item_heading":"h4","showLastSeparator":"1","readmore":"1","count":"1","ordering":"a.publish_up","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}',0,'*'),
(33,0,'Random Image','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_random_image',1,0,'{"type":"jpg","folder":"images\\/headers","link":"","width":"","height":"","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(34,0,'Articles Related Items','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_related_items',1,1,'{"showDate":"0","layout":"_:default","moduleclass_sfx":"","owncache":"1"}',0,'*'),
(35,0,'Search','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_search',1,1,'{"label":"","width":"20","text":"","button":"","button_pos":"right","imagebutton":"","button_text":"","opensearch":"1","opensearch_title":"","set_itemid":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}',0,'*'),
(36,0,'Statistics','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_stats',1,1,'{"serverinfo":"1","siteinfo":"1","counter":"1","increase":"0","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}',0,'*'),
(37,0,'Syndicate Feeds','','',1,'syndicateload',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_syndicate',1,1,'{"text":"Feed Entries","format":"rss","layout":"","moduleclass_sfx":"","cache":"0"}',0,'*'),
(38,0,'Users Latest','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_users_latest',1,1,'{"shownumber":"5","linknames":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","cache_time":"900","cachemode":"static"}',0,'*'),
(39,0,'Who\'s Online','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_whosonline',1,1,'{"showmode":"2","linknames":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}',0,'*'),
(40,0,'Wrapper','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_wrapper',1,1,'{"url":"https:\\/\\/www.youtube.com\\/embed\\/vb2eObvmvdI","add":"1","scrolling":"auto","width":"640","height":"390","height_auto":"1","target":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}',0,'*'),
(41,0,'Footer','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_footer',1,1,'{"layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}',0,'*'),
(48,0,'Image Module','','<p><img src="images/headers/blue-flower.jpg" alt="Blue Flower" /></p>',1,'position-3',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_custom',1,0,'{"prepare_content":"1","backgroundimage":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(52,0,'Breadcrumbs','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_breadcrumbs',1,1,'{"showHere":"1","showHome":"1","homeText":"Home","showLast":"1","separator":"","layout":"_:default","moduleclass_sfx":"","cache":"0","cache_time":"900","cachemode":"itemid"}',0,'*'),
(56,0,'Banners','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_banners',1,1,'{"target":"1","count":"1","cid":"1","catid":["15"],"tag_search":"0","ordering":"random","header_text":"","footer_text":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900"}',0,'*'),
(61,0,'Articles Categories','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_categories',1,1,'{"parent":"29","show_description":"0","show_children":"0","count":"0","maxlevel":"0","layout":"_:default","item_heading":"4","moduleclass_sfx":"","owncache":"1","cache_time":"900"}',0,'*'),
(62,0,'Language Switcher','','',3,'position-4',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',0,'mod_languages',1,1,'{"header_text":"","footer_text":"","image":"1","layout":"","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}',0,'*'),
(63,0,'Search','','',1,'position-0',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_search',1,1,'{"width":"20","text":"","button":"","button_pos":"right","imagebutton":"1","button_text":"","set_itemid":"","layout":"","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}',0,'*'),
(64,0,'Language Switcher','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',0,'mod_languages',1,1,'{"header_text":"","footer_text":"","dropdown":"0","image":"1","inline":"1","show_active":"1","full_name":"1","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(69,0,'Articles Category','','',1,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_category',1,1,'{"mode":"normal","show_on_article_page":"1","show_front":"show","count":"0","category_filtering_type":"1","catid":["72"],"show_child_category_articles":"0","levels":"1","author_filtering_type":"1","created_by":[""],"author_alias_filtering_type":"1","created_by_alias":[""],"excluded_articles":"","date_filtering":"off","date_field":"a.created","start_date_range":"","end_date_range":"","relative_date":"30","article_ordering":"a.title","article_ordering_direction":"ASC","article_grouping":"none","article_grouping_direction":"ksort","month_year_format":"F Y","item_heading":"4","link_titles":"1","show_date":"0","show_date_field":"created","show_date_format":"Y-m-d H:i:s","show_category":"0","show_hits":"0","show_author":"0","show_introtext":"0","introtext_limit":"100","show_readmore":"0","show_readmore_title":"1","readmore_limit":"15","layout":"_:default","moduleclass_sfx":"","owncache":"1","cache_time":"900"}',0,'*'),
(79,0,'Multilanguage status','','',1,'status',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',0,'mod_multilangstatus',3,1,'{"layout":"_:default","moduleclass_sfx":"","cache":"0"}',1,'*'),
(84,0,'Smart Search Module','','',2,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_finder',1,1,'{"searchfilter":"","show_autosuggest":"1","show_advanced":"0","layout":"_:default","moduleclass_sfx":"","field_size":20,"alt_label":"","show_label":"0","label_pos":"top","show_button":"0","button_pos":"right","opensearch":"1","opensearch_title":""}',0,'*'),
(86,0,'Joomla Version','','',1,'footer',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_version',2,1,'{"format":"short","product":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}',1,'*'),
(87,0,'Popular Tags','','',1,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_tags_popular',1,1,'{"maximum":"10","timeframe":"alltime","order_value":"count","order_direction":"1","display_count":0,"no_results_text":"0","minsize":1,"maxsize":2,"layout":"_:default","moduleclass_sfx":"","owncache":"1","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(88,0,'Site Information','','',3,'cpanel',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_stats_admin',1,1,'{"serverinfo":"1","siteinfo":"1","counter":"0","increase":"0","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(89,0,'Release News','','',0,'postinstall',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_feed',1,1,'{"rssurl":"https:\\/\\/www.joomla.org\\/announcements\\/release-news.feed","rssrtl":"0","rsstitle":"1","rssdesc":"1","rssimage":"1","rssitems":"3","rssitemdesc":"1","word_count":"0","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',1,'*');

SELECT setval('#__modules_id_seq', max(id)) FROM #__modules;

--
-- Dumping data for table #__modules_menu
--
INSERT INTO "#__modules_menu" VALUES
(1,0),
(2,0),
(3,0),
(4,0),
(6,0),
(7,0),
(8,0),
(9,0),
(10,0),
(12,0),
(13,0),
(14,0),
(15,0),
(16,0),
(17,0),
(19,0),
(28,0),
(33,0),
(48,0),
(79,0),
(86,0);
