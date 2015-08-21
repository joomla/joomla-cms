TRUNCATE "#__assets" RESTART IDENTITY;
TRUNCATE "#__categories" RESTART IDENTITY;
TRUNCATE "#__menu" RESTART IDENTITY;
TRUNCATE "#__menu_types" RESTART IDENTITY;
TRUNCATE "#__modules" RESTART IDENTITY;
TRUNCATE "#__modules_menu" RESTART IDENTITY;

--
-- Dumping data for table #__assets
--
INSERT INTO "#__assets" VALUES
(1,0,0,93,0,'root.1','Root Asset','{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}'),
(2,1,1,2,1,'com_admin','com_admin','{}'),
(3,1,3,6,1,'com_banners','com_banners','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(4,1,7,8,1,'com_cache','com_cache','{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(5,1,9,10,1,'com_checkin','com_checkin','{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(6,1,11,12,1,'com_config','com_config','{}'),
(7,1,13,16,1,'com_contact','com_contact','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(8,1,17,34,1,'com_content','com_content','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'),
(9,1,35,36,1,'com_cpanel','com_cpanel','{}'),
(10,1,37,38,1,'com_installer','com_installer','{"core.admin":[],"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'),
(11,1,39,40,1,'com_languages','com_languages','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(12,1,41,42,1,'com_login','com_login','{}'),
(13,1,43,44,1,'com_mailto','com_mailto','{}'),
(14,1,45,46,1,'com_massmail','com_massmail','{}'),
(15,1,47,48,1,'com_media','com_media','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'),
(16,1,49,50,1,'com_menus','com_menus','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(17,1,51,52,1,'com_messages','com_messages','{"core.admin":{"7":1},"core.manage":{"7":1}}'),
(18,1,53,54,1,'com_modules','com_modules','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(19,1,55,58,1,'com_newsfeeds','com_newsfeeds','{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(20,1,59,60,1,'com_plugins','com_plugins','{"core.admin":{"7":1},"core.manage":[],"core.edit":[],"core.edit.state":[]}'),
(21,1,61,62,1,'com_redirect','com_redirect','{"core.admin":{"7":1},"core.manage":[]}'),
(22,1,63,64,1,'com_search','com_search','{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(23,1,65,66,1,'com_templates','com_templates','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(24,1,67,70,1,'com_users','com_users','{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.own":{"6":1},"core.edit.state":[]}'),
(26,1,77,78,1,'com_wrapper','com_wrapper','{}'),
(27,8,18,23,2,'com_content.category.2','Uncategorised','{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(28,3,4,5,2,'com_banners.category.3','Uncategorised','{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(29,7,14,15,2,'com_contact.category.4','Uncategorised','{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(30,19,56,57,2,'com_newsfeeds.category.5','Uncategorised','{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(32,24,68,69,1,'com_users.category.7','Uncategorised','{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(33,1,79,80,1,'com_finder','com_finder','{"core.admin":{"7":1},"core.manage":{"6":1}}'),
(35,8,24,33,2,'com_content.category.9','Blog','{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'),
(36,27,19,20,3,'com_content.article.1','About','{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(37,27,21,22,3,'com_content.article.2','Working on Your Site','{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(38,35,25,26,3,'com_content.article.3','Welcome to your blog','{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(39,35,27,28,3,'com_content.article.4','About your home page','{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(40,35,29,30,3,'com_content.article.5','Your Modules','{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
(41,1,81,82,1,'com_users.category.10','Uncategorised',''),
(42,1,83,84,1,'com_joomlaupdate','com_joomlaupdate','{"core.admin":[],"core.manage":[],"core.delete":[],"core.edit.state":[]}'),
(43,35,31,32,3,'com_content.article.6','Your Template','{"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(44,1,85,86,1,'com_tags','com_tags','{"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1}}'),
(45,1,87,88,1,'com_contenthistory','com_contenthistory','{}'),
(46,1,89,90,1,'com_ajax','com_ajax','{}'),
(47,1,91,92,1,'com_postinstall','com_postinstall','{}');

SELECT setval('#__assets_id_seq', max(id)) FROM #__assets;

--
-- Dumping data for table #__categories
--
INSERT INTO "#__categories" VALUES
(1,0,0,0,17,0,'','system','ROOT','root','','',1,0,'1970-01-01 00:00:00',1,'{}','','','',363,'2011-01-01 00:00:01',0,'1970-01-01 00:00:00',0,'*',1),
(2,27,1,1,2,1,'uncategorised','com_content','Uncategorised','uncategorised','','',1,0,'1970-01-01 00:00:00',1,'{"target":"","image":""}','','','{"page_title":"","author":"","robots":""}',363,'2011-01-01 00:00:01',0,'1970-01-01 00:00:00',0,'*',1),
(3,28,1,3,4,1,'uncategorised','com_banners','Uncategorised','uncategorised','','',1,0,'1970-01-01 00:00:00',1,'{"target":"","image":"","foobar":""}','','','{"page_title":"","author":"","robots":""}',363,'2011-01-01 00:00:01',0,'1970-01-01 00:00:00',0,'*',1),
(4,29,1,5,6,1,'uncategorised','com_contact','Uncategorised','uncategorised','','',1,0,'1970-01-01 00:00:00',1,'{"target":"","image":""}','','','{"page_title":"","author":"","robots":""}',363,'2011-01-01 00:00:01',0,'1970-01-01 00:00:00',0,'*',1),
(5,30,1,7,8,1,'uncategorised','com_newsfeeds','Uncategorised','uncategorised','','',1,0,'1970-01-01 00:00:00',1,'{"target":"","image":""}','','','{"page_title":"","author":"","robots":""}',363,'2011-01-01 00:00:01',0,'1970-01-01 00:00:00',0,'*',1),
(7,32,1,11,12,1,'uncategorised','com_users','Uncategorised','uncategorised','','',1,0,'1970-01-01 00:00:00',1,'{"target":"","image":""}','','','{"page_title":"","author":"","robots":""}',363,'2011-01-01 00:00:01',0,'1970-01-01 00:00:00',0,'*',1),
(9,35,1,15,16,1,'blog','com_content','Blog','blog','','',1,0,'1970-01-01 00:00:00',1,'{"category_layout":"","image":""}','','','{"author":"","robots":""}',363,'2011-01-01 00:00:01',0,'1970-01-01 00:00:00',0,'*',1);

SELECT setval('#__categories_id_seq', max(id)) FROM #__categories;

--
-- Dumping data for table #__content
--
INSERT INTO "#__content" VALUES
(1,36,'About','about','<p>This tells you a bit about this blog and the person who writes it. </p><p>When you are logged in you will be able to edit this page by clicking on the edit icon.</p>','',1,2,'2011-01-01 00:00:01',713,'Joomla','1970-01-01 00:00:00',0,0,'1970-01-01 00:00:00','2012-01-04 16:10:42','1970-01-01 00:00:00','{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}','{"urla":"","urlatext":"","targeta":"","urlb":"","urlbtext":"","targetb":"","urlc":"","urlctext":"","targetc":""}','{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","alternative_readmore":"","article_layout":"","show_publishing_options":"","show_article_options":"","show_urls_images_backend":"","show_urls_images_frontend":""}',1,2,'','',1,16,'{"robots":"","author":"","rights":"","xreference":""}',0,'*',''),
(2,37,'Working on Your Site','working-on-your-site','<p>Here are some basic tips for working on your site.</p><ul><li>Joomla! has a "front end" that you are looking at now and an "administrator" or "back end" which is where you do the more advanced work of creating your site such as setting up the menus and deciding what modules to show. You need to login to the administrator separately using the same user name and password that you used to login to this part of the site.</li><li>One of the first things you will probably want to do is change the site title and tag line and to add a logo. To do this click on the Template Settings link in the top menu. To change your site description, browser title, default email and other items, click Site Settings. More advanced configuration options are available in the administrator.</li><li>To totally change the look of your site you will probably want to install a new template. In the Extensions menu click on Extensions Manager and then go to the Install tab. There are many free and commercial templates available for Joomla.</li><li>As you have already seen, you can control who can see different parts of you site. When you work with modules, articles setting the Access level to Registered will mean that only logged in users can see them</li><li>When you create a new article or other kind of content you also can save it as Published or Unpublished. If it is Unpublished site visitors will not be able to see it but you will.</li><li>You can learn much more about working with Joomla from the <a href="https://docs.joomla.org">Joomla documentation site</a> and get help from other users at the <a href="http://forum.joomla.org">Joomla forums</a>. In the administrator there are help buttons on every page that provide detailed information about the functions on that page.</li></ul>','',1,2,'2011-01-01 00:00:01',713,'Joomla','2013-10-13 17:16:12',713,0,'1970-01-01 00:00:00','2012-01-04 16:48:38','1970-01-01 00:00:00','{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}','{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}','{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":""}',2,1,'','',3,8,'{"robots":"","author":"","rights":"","xreference":""}',0,'*',''),
(3,38,'Welcome to your blog','welcome-to-your-blog','<p>This is a sample blog posting.</p><p>If you log in to the site (the Author Login link is on the very bottom of this page) you will be able to edit it and all of the other existing articles. You will also be able to create a new article and make other changes to the site.</p><p>As you add and modify articles you will see how your site changes and also how you can customise it in various ways.</p><p>Go ahead, you can\'t break it.</p>','',1,9,'2011-01-05 00:00:01',713,'Joomla','2013-10-13 16:58:11',713,0,'1970-01-01 00:00:00','2012-01-05 16:55:36','1970-01-01 00:00:00','{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}','{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}','{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"0","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":""}',4,2,'','',1,5,'{"robots":"","author":"","rights":"","xreference":""}',0,'*',''),
(4,39,'About your home page','about-your-home-page','<p>Your home page is set to display the four most recent articles from the blog category in a column. Then there are links to the 4 nest oldest articles. You can change those numbers by editing the content options settings in the blog tab in your site administrator. There is a link to your site administrator in the top menu.</p><p>If you want to have your blog post broken into two parts, an introduction and then a full length separate page, use the Read More button to insert a break.</p>','<p>On the full page you will see both the introductory content and the rest of the article. You can change the settings to hide the introduction if you want.</p><p> </p><p> </p><p> </p>',1,9,'2011-01-03 00:00:01',713,'Joomla','2013-10-13 16:59:32',713,0,'1970-01-01 00:00:00','2012-01-03 00:00:00','1970-01-01 00:00:00','{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}','{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}','{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"0","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":""}',7,1,'','',1,5,'{"robots":"","author":"","rights":"","xreference":""}',0,'*',''),
(5,40,'Your Modules','your-modules','<p>Your site has some commonly used modules already preconfigured. These include:</p><ul><li>Image Module which holds the image beneath the menu. This is a Custom HTML module that you can edit to change the image.</li><li>Blog Roll. which lets you link to other blogs. We\'ve put in two examples, but you\'ll want to change them. When you are logged in, click on blog roll on the top menu to update this.</li><li>Most Read Posts which lists articles based on the number of times they have been read.</li><li>Older Articles which lists out articles by month.</li><li>Syndicate which allows your readers to read your posts in a news reader.</li><li>Popular Tags, which will appear if you use tagging on your articles. Just enter a tag in the Tags field when editing.</li></ul><p>Each of these modules has many options which you can experiment with in the Module Manager in your site Administrator. Moving your mouse over a module and clicking on the edit icon will take you to an edit screen for that module. Always be sure to save and close any module you edit. </p><p>Joomla! also includes many other modules you can incorporate in your site. As you develop your site you may want to add more module that you can find at the <a href="http://extensions.joomla.org">Joomla Extensions Directory.</a></p>','',1,9,'2010-12-31 00:00:01',713,'Joomla','2013-10-13 17:59:36',713,0,'1970-01-01 00:00:00','2010-12-31 00:00:01','1970-01-01 00:00:00','{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}','{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}','{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"0","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":""}',11,0,'','',1,4,'{"robots":"","author":"","rights":"","xreference":""}',0,'*',''),
(6,43,'Your Template','your-template','<p>Templates control the look and feel of your website.</p><p>This blog is installed with the Protostar template.</p><p>You can edit the options by clicking on the Working on Your Site, Template Settings link in the top menu (visible when you login).</p><p>For example you can change the site background color, highlights color, site title, site description and title font used. </p><p>More options are available in the site administrator. You may also install a new template using the extension manager.</p>','',1,9,'2011-01-02 00:00:01',713,'Joomla','2013-10-13 17:04:31',713,0,'1970-01-01 00:00:00','2011-01-02 00:00:01','1970-01-01 00:00:00','{"image_intro":"","float_intro":"","image_intro_alt":"","image_intro_caption":"","image_fulltext":"","float_fulltext":"","image_fulltext_alt":"","image_fulltext_caption":""}','{"urla":false,"urlatext":"","targeta":"","urlb":false,"urlbtext":"","targetb":"","urlc":false,"urlctext":"","targetc":""}','{"show_title":"","link_titles":"","show_tags":"","show_intro":"","info_block_position":"0","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_vote":"","show_hits":"","show_noauth":"","urls_position":"","alternative_readmore":"","article_layout":""}',17,0,'','',1,2,'{"robots":"","author":"","rights":"","xreference":""}',0,'*','');

SELECT setval('#__content_id_seq', max(id)) FROM #__content;

--
-- Dumping data for table #__menu
--
INSERT INTO "#__menu" VALUES
(1,'','Menu_Item_Root','root','','','','',1,0,0,0,0,'1970-01-01 00:00:00',0,0,'',0,'',0,73,0,'*',0),
(2,'menu','com_banners','Banners','','Banners','index.php?option=com_banners','component',0,1,1,4,0,'1970-01-01 00:00:00',0,0,'class:banners',0,'',1,10,0,'*',1),
(3,'menu','com_banners','Banners','','Banners/Banners','index.php?option=com_banners','component',0,2,2,4,0,'1970-01-01 00:00:00',0,0,'class:banners',0,'',2,3,0,'*',1),
(4,'menu','com_banners_categories','Categories','','Banners/Categories','index.php?option=com_categories&extension=com_banners','component',0,2,2,6,0,'1970-01-01 00:00:00',0,0,'class:banners-cat',0,'',4,5,0,'*',1),
(5,'menu','com_banners_clients','Clients','','Banners/Clients','index.php?option=com_banners&view=clients','component',0,2,2,4,0,'1970-01-01 00:00:00',0,0,'class:banners-clients',0,'',6,7,0,'*',1),
(6,'menu','com_banners_tracks','Tracks','','Banners/Tracks','index.php?option=com_banners&view=tracks','component',0,2,2,4,0,'1970-01-01 00:00:00',0,0,'class:banners-tracks',0,'',8,9,0,'*',1),
(7,'menu','com_contact','Contacts','','Contacts','index.php?option=com_contact','component',0,1,1,8,0,'1970-01-01 00:00:00',0,0,'class:contact',0,'',33,38,0,'*',1),
(8,'menu','com_contact','Contacts','','Contacts/Contacts','index.php?option=com_contact','component',0,7,2,8,0,'1970-01-01 00:00:00',0,0,'class:contact',0,'',34,35,0,'*',1),
(9,'menu','com_contact_categories','Categories','','Contacts/Categories','index.php?option=com_categories&extension=com_contact','component',0,7,2,6,0,'1970-01-01 00:00:00',0,0,'class:contact-cat',0,'',36,37,0,'*',1),
(10,'menu','com_messages','Messaging','','Messaging','index.php?option=com_messages','component',0,1,1,15,0,'1970-01-01 00:00:00',0,0,'class:messages',0,'',39,44,0,'*',1),
(11,'menu','com_messages_add','New Private Message','','Messaging/New Private Message','index.php?option=com_messages&task=message.add','component',0,10,2,15,0,'1970-01-01 00:00:00',0,0,'class:messages-add',0,'',40,41,0,'*',1),
(12,'menu','com_messages_read','Read Private Message','','Messaging/Read Private Message','index.php?option=com_messages','component',0,10,2,15,0,'1970-01-01 00:00:00',0,0,'class:messages-read',0,'',42,43,0,'*',1),
(13,'menu','com_newsfeeds','News Feeds','','News Feeds','index.php?option=com_newsfeeds','component',0,1,1,17,0,'1970-01-01 00:00:00',0,0,'class:newsfeeds',0,'',45,50,0,'*',1),
(14,'menu','com_newsfeeds_feeds','Feeds','','News Feeds/Feeds','index.php?option=com_newsfeeds','component',0,13,2,17,0,'1970-01-01 00:00:00',0,0,'class:newsfeeds',0,'',46,47,0,'*',1),
(15,'menu','com_newsfeeds_categories','Categories','','News Feeds/Categories','index.php?option=com_categories&extension=com_newsfeeds','component',0,13,2,6,0,'1970-01-01 00:00:00',0,0,'class:newsfeeds-cat',0,'',48,49,0,'*',1),
(16,'menu','com_redirect','Redirect','','Redirect','index.php?option=com_redirect','component',0,1,1,24,0,'1970-01-01 00:00:00',0,0,'class:redirect',0,'',63,64,0,'*',1),
(17,'menu','com_search','Basic Search','','Basic Search','index.php?option=com_search','component',0,1,1,19,0,'1970-01-01 00:00:00',0,0,'class:search',0,'',53,54,0,'*',1),
(21,'menu','com_finder','Smart Search','','Smart Search','index.php?option=com_finder','component',0,1,1,27,0,'1970-01-01 00:00:00',0,0,'class:finder',0,'',51,52,0,'*',1),
(22,'menu','com_joomlaupdate','Joomla! Update','','Joomla! Update','index.php?option=com_joomlaupdate','component',0,1,1,28,0,'1970-01-01 00:00:00',0,0,'class:joomlaupdate',0,'',61,62,0,'*',1),
(101,'mainmenu','Home','home','','home','index.php?option=com_content&view=category&layout=blog&id=9','component',1,1,1,22,0,'1970-01-01 00:00:00',0,1,'',0,'{"layout_type":"blog","show_category_title":"0","show_description":"","show_description_image":"","maxLevel":"","show_empty_categories":"","show_no_articles":"","show_subcat_desc":"","show_cat_num_articles":"","page_subheading":"","num_leading_articles":"4","num_intro_articles":"0","num_columns":"1","num_links":"2","multi_column_order":"1","show_subcategory_content":"","orderby_pri":"","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_title":"","link_titles":"","show_intro":"","show_category":"0","link_category":"","show_parent_category":"","link_parent_category":"","info_bloc_position":"0","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"0","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"0","show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',29,30,1,'*',0),
(102,'bottommenu','Author Login','login','','login','index.php?option=com_users&view=login','component',1,1,1,25,0,'1970-01-01 00:00:00',0,1,'',0,'{"login_redirect_url":"index.php?Itemid=101","logindescription_show":"1","login_description":"","login_image":"","logout_redirect_url":"","logoutdescription_show":"1","logout_description":"","logout_image":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',65,66,0,'*',0),
(103,'authormenu','Change Password','change-password','','change-password','index.php?option=com_users&view=profile&layout=edit','component',1,1,1,25,0,'1970-01-01 00:00:00',0,2,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',25,26,0,'*',0),
(104,'authormenu','Create a Post','create-a-post','','create-a-post','index.php?option=com_content&view=form&layout=edit','component',1,1,1,22,0,'1970-01-01 00:00:00',0,3,'',0,'{"enable_category":"1","catid":"9","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',11,12,0,'*',0),
(106,'authormenu','Site Administrator','2012-01-04-15-46-42','','2012-01-04-15-46-42','administrator','url',1,1,1,0,0,'1970-01-01 00:00:00',1,3,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1}',23,24,0,'*',0),
(107,'authormenu','Log out','log-out','','log-out','index.php?option=com_users&view=login','component',1,1,1,25,0,'1970-01-01 00:00:00',0,1,'',0,'{"login_redirect_url":"","logindescription_show":"1","login_description":"","login_image":"","logout_redirect_url":"","logoutdescription_show":"1","logout_description":"","logout_image":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',27,28,0,'*',0),
(108,'mainmenu','About','about','','about','index.php?option=com_content&view=article&id=1','component',1,1,1,22,0,'1970-01-01 00:00:00',0,1,'',0,'{"show_title":"","link_titles":"","show_intro":"","info_block_position":"0","show_category":"0","link_category":"0","show_parent_category":"","link_parent_category":"","show_author":"0","link_author":"","show_create_date":"0","show_modify_date":"","show_publish_date":"0","show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"0","show_noauth":"","urls_position":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',31,32,0,'*',0),
(109,'authormenu','Working on Your Site','working-on-your-site','','working-on-your-site','index.php?option=com_content&view=article&id=2','component',1,1,1,22,0,'1970-01-01 00:00:00',0,1,'',0,'{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',17,22,0,'*',0),
(111,'menu','com_tags','com-tags','','com-tags','index.php?option=com_tags','component',0,1,1,29,0,'1970-01-01 00:00:00',0,1,'class:tags',0,'',67,68,0,'',1),
(112,'main','com_postinstall','Post-installation messages','','Post-installation messages','index.php?option=com_postinstall','component',0,1,1,32,0,'1970-01-01 00:00:00',0,1,'class:postinstall',0,'',69,70,0,'*',1),
(113,'authormenu','Site Settings','site-settings','','working-on-your-site/site-settings','index.php?option=com_config&view=config&controller=config.display.config','component',1,109,2,23,0,'1970-01-01 00:00:00',0,6,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',18,19,0,'*',0),
(114,'authormenu','Template Settings','template-settings','','working-on-your-site/template-settings','index.php?option=com_config&view=templates&controller=config.display.templates','component',1,109,2,23,0,'1970-01-01 00:00:00',0,1,'',0,'{"menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',20,21,0,'*',0),
(115,'mainmenu','Author Login','author-login','','author-login','index.php?option=com_users&view=login','component',1,1,1,25,0,'1970-01-01 00:00:00',0,1,'',0,'{"login_redirect_url":"","logindescription_show":"1","login_description":"","login_image":"","logout_redirect_url":"","logoutdescription_show":"1","logout_description":"","logout_image":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":0,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}',71,72,0,'*',0);

SELECT setval('#__menu_id_seq', max(id)) FROM #__menu;

--
-- Dumping data for table #__menu_types
--
INSERT INTO "#__menu_types" VALUES
(1,'mainmenu','Main Menu','The main menu for the site'),
(2,'authormenu','Author Menu',''),
(3,'bottommenu','Bottom Menu','');

SELECT setval('#__menu_types_id_seq', max(id)) FROM #__menu_types;

--
-- Dumping data for table #__modules
--
INSERT INTO "#__modules" VALUES
(1,0,'Main Menu','','',1,'position-1',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_menu',1,0,'{"menutype":"mainmenu","base":"","startLevel":"1","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":" nav-pills","window_open":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(2,0,'Login','','',1,'login',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_login',1,1,'',1,'*'),
(3,0,'Popular Articles','','',1,'cpanel',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_popular',3,1,'{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(4,0,'Recently Added Articles','','',2,'cpanel',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_latest',3,1,'{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(8,0,'Toolbar','','',1,'toolbar',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_toolbar',3,1,'',1,'*'),
(9,0,'Quick Icons','','',1,'icon',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_quickicon',3,1,'',1,'*'),
(10,0,'Logged-in Users','','',3,'cpanel',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_logged',3,1,'{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(12,0,'Admin Menu','','',1,'menu',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_menu',3,1,'{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}',1,'*'),
(13,0,'Admin Submenu','','',1,'submenu',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_submenu',3,1,'',1,'*'),
(14,0,'User Status','','',2,'status',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_status',3,1,'',1,'*'),
(15,0,'Title','','',1,'title',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_title',3,1,'',1,'*'),
(16,0,'Login Form','','',7,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',0,'mod_login',1,1,'{"greeting":"1","name":"0"}',0,'*'),
(17,0,'Breadcrumbs','','',1,'position-2',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_breadcrumbs',1,1,'{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"1","cache_time":"900","cachemode":"itemid"}',0,'*'),
(79,0,'Multilanguage status','','',1,'status',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',0,'mod_multilangstatus',3,1,'{"layout":"_:default","moduleclass_sfx":"","cache":"0"}',1,'*'),
(80,0,'Author Menu','','',1,'position-1',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_menu',3,0,'{"menutype":"authormenu","base":"","startLevel":"1","endLevel":"0","showAllChildren":"1","tag_id":"","class_sfx":" nav-pills","window_open":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(82,0,'Syndication','','',6,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_syndicate',1,0,'{"display_text":1,"text":"My Blog","format":"rss","layout":"_:default","moduleclass_sfx":"","cache":"0"}',0,'*'),
(83,0,'Archived Articles','','',4,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_archive',1,1,'{"count":"10","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}',0,'*'),
(84,0,'Most Read Posts','','',5,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_popular',1,1,'{"catid":["9"],"count":"5","show_front":"1","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static"}',0,'*'),
(85,0,'Older Posts','','',2,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_articles_category',1,1,'{"mode":"normal","show_on_article_page":"0","show_front":"show","count":"6","category_filtering_type":"1","catid":["9"],"show_child_category_articles":"0","levels":"1","author_filtering_type":"1","created_by":[""],"author_alias_filtering_type":"1","created_by_alias":[""],"excluded_articles":"","date_filtering":"off","date_field":"a.created","start_date_range":"","end_date_range":"","relative_date":"30","article_ordering":"a.created","article_ordering_direction":"DESC","article_grouping":"none","article_grouping_direction":"krsort","month_year_format":"F Y","item_heading":"5","link_titles":"1","show_date":"0","show_date_field":"created","show_date_format":"Y-m-d H:i:s","show_category":"0","show_hits":"0","show_author":"0","show_introtext":"0","introtext_limit":"100","show_readmore":"0","show_readmore_title":"1","readmore_limit":"15","layout":"_:default","moduleclass_sfx":"","owncache":"1","cache_time":"900","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(86,0,'Bottom Menu','','',1,'footer',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_menu',1,0,'{"menutype":"bottommenu","base":"","startLevel":"1","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(87,0,'Search','','',1,'position-0',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_search',1,1,'{"label":"","width":"20","text":"","button":"","button_pos":"right","imagebutton":"","button_text":"","opensearch":"1","opensearch_title":"","set_itemid":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}',0,'*'),
(88,0,'Image','','<p><img src="images/headers/raindrops.jpg" alt="" /></p>',1,'position-3',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_custom',1,0,'{"prepare_content":"1","backgroundimage":"","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(89,0,'Popular Tags','','',1,'position-7',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_tags_popular',1,1,'{"maximum":"8","timeframe":"alltime","order_value":"count","order_direction":"1","display_count":0,"no_results_text":"0","minsize":1,"maxsize":2,"layout":"_:default","moduleclass_sfx":"","owncache":"1","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(90,0,'Similar Items','','',0,'',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_tags_similar',1,1,'{"maximum":"5","matchtype":"any","layout":"_:default","moduleclass_sfx":"","owncache":"1","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',0,'*'),
(91,0,'Site Information','','',4,'cpanel',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_stats_admin',6,1,'{"serverinfo":"1","siteinfo":"1","counter":"0","increase":"0","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"static","module_tag":"div","bootstrap_size":"6","header_tag":"h3","header_class":"","style":"0"}',1,'*'),
(92,0,'Release News','','',1,'postinstall',0,'1970-01-01 00:00:00','1970-01-01 00:00:00','1970-01-01 00:00:00',1,'mod_feed',1,1,'{"rssurl":"http:\\/\\/www.joomla.org\\/announcements\\/release-news.feed","rssrtl":"0","rsstitle":"1","rssdesc":"1","rssimage":"1","rssitems":"3","rssitemdesc":"1","word_count":"0","layout":"_:default","moduleclass_sfx":"","cache":"1","cache_time":"900","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',1,'*');

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
(79,0),
(80,0),
(81,0),
(82,0),
(83,0),
(84,0),
(85,0),
(86,0),
(87,0),
(88,0),
(89,0),
(90,0),
(91,0),
(92,0);
