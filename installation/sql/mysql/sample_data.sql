# @version		$Id$
#
# IMPORTANT - THIS FILE MUST BE SAVED WITH UTF-8 ENCODING ONLY. BEWARE IF EDITING!
#

--
-- Dumping data for table `#__assets`
--
INSERT INTO `#__assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES
	(27,8,31,32,2,'com_content.article.1','Welcome to Joomla!',''),
	(28,8,17,18,2,'com_content.category.11','News',''),
	(29,8,19,30,2,'com_content.category.12','Countries',''),
	(30,29,20,29,3,'com_content.category.23','Australia',''),
	(31,30,21,22,4,'com_content.category.24','Queensland',''),
	(32,30,23,28,4,'com_content.category.25','Tasmania',''),
	(33,31,24,25,5,'com_content.article.2','Great Barrier Reef',''),
	(34,32,26,27,5,'com_content.article.3','Cradle Mountain-Lake St Clair National Park','{"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(35,25,69,74,2,'com_weblinks.category.20','Uncategorised Weblinks',''),
	(36,35,70,73,3,'com_weblinks.category.21','Joomla! Specific Links',''),
	(37,36,71,72,4,'com_weblinks.category.22','Other Resources',''),
	(39,7,13,14,2,'com_contact.category.26','Contacts','{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'),
	(40,1,78,79,1,'com_banners.category.27','Banners',''),
	(41,19,55,56,2,'com_newsfeeds.category.28','News Feeds','{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}');

--
-- Dumping data for table `#__banners`
--

INSERT IGNORE INTO `#__banners` VALUES
(1, 1, 0, 'OSM 1', 'osm-1', 0, 43, 0, 'http://www.opensourcematters.org', 1, 27, '', 0, 1, '', '{"custom":{"bannercode":""},"alt":{"alt":"Open Source Matters"},"flash":{"width":"0","height":"0"},"image":{"url":"osmbanner1.png"}}',0 ,'' ,-1 ,-1 ,-1 , 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00','2009-10-10 13:52:59' );

--
-- Dumping data for table `#__banner_clients`
--

INSERT IGNORE INTO `#__banner_clients` VALUES
(1, 'Open Source Matters', 'Administrator', 'email@email.com', '', 1, 0, '0000-00-00 00:00:00','',0,'',-1,-1,-1);

--
-- Dumping data for table `#__categories` (remove existing rows first)
--

TRUNCATE `#__categories`;

INSERT IGNORE INTO `#__categories` VALUES
(1, 0, 0, 0, 23, 0, '', 'system', 'ROOT', 'root', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-10-18 16:21:40', 0, '0000-00-00 00:00:00', 0, ''),
(11, 28, 1, 1, 2, 1, 'news', 'com_content', 'News', 'news', 'The top articles category.', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-22 19:42:11', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(12, 29, 1, 9, 16, 1, 'countries', 'com_content', 'Countries', 'countries', 'The latest news from the Joomla! Team', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-22 20:25:13', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(20, 35, 1, 3, 8, 1, 'uncategorised-weblinks', 'com_weblinks', 'Uncategorised Weblinks', 'uncategorised-weblinks', 'The top weblinks category.', 1, 42, '2009-03-15 15:34:27', 1, '{}', '', '', '', 0, '2009-06-22 19:42:11', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(21, 36, 20, 4, 7, 2, 'uncategorised-weblinks/joomla-specific-links', 'com_weblinks', 'Joomla! Specific Links', 'joomla-specific-links', 'A selection of links that are all related to the Joomla! Project.', 1, 42, '2009-03-15 15:34:27', 1, '{}', '', '', '', 0, '2009-06-22 19:42:11', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(22, 37, 21, 5, 6, 3, 'uncategorised-weblinks/joomla-specific-links/other-resources', 'com_weblinks', 'Other Resources', 'other-resources', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-22 19:42:11', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(23, 30, 12, 10, 15, 2, 'countries/australia', 'com_content', 'Australia', 'australia', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-06-22 20:25:13', 0, '0000-00-00 00:00:00', 0, ''),
(24, 31, 23, 11, 12, 3, 'countries/australia/queensland', 'com_content', 'Queensland', 'queensland', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-06-22 20:25:17', 0, '0000-00-00 00:00:00', 0, ''),
(25, 32, 23, 13, 14, 3, 'countries/australia/tasmania', 'com_content', 'Tasmania', 'tasmania', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-06-22 20:25:17', 0, '0000-00-00 00:00:00', 0, ''),
(26, 38, 1, 17, 18, 1, 'contacts', 'com_contact', 'Contacts', 'contacts', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-10-10 05:52:59', 0, '0000-00-00 00:00:00', 0, ''),
(27, 40, 1, 19, 20, 1, 'banners', 'com_banners', 'Banners', 'banners', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-10-10 09:50:46', 0, '0000-00-00 00:00:00', 0, ''),
(28, 41, 1, 21, 22, 1, 'news-feeds', 'com_newsfeeds', 'News Feeds', 'news-feeds', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-10-10 10:17:32', 0, '0000-00-00 00:00:00', 0, '');

--
-- Dumping data for table `#__contact_details`
--

INSERT IGNORE INTO `#__contact_details` VALUES
(1, 'Contact Name', 'name', 'Position', 'Street Address', 'Suburb', 'State', 'Country', 'Zip Code', 'Telephone', 'Fax', '<p>Information about or by the contact.</p>', 'powered_by.png', 'top', 'email@email.com', 1, 1, 42, '2009-10-10 09:53:12', 1, '{"show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_misc":"1","show_image":"1","allow_vcard":"0","show_articles":"1","show_links":"1","linka_name":"Twitter","linka":"http:\/\/twitter.com\/joomla","linkb_name":"YouTube","linkb":"http:\/\/www.youtube.com\/user\/joomla","linkc_name":"Ustream","linkc":"http:\/\/www.ustream.tv\/joomla","linkd_name":"FriendFeed","linkd":"http:\/\/friendfeed.com/joomla","linke_name":"Scribed","linke":"http:\/\/www.scribd.com\/people\/view\/504592-joomla","show_profile":"1"}', 0, 26, 1, '', '');

--
-- Dumping data for table `#__content`
--

INSERT IGNORE INTO `#__content` VALUES
(1, 27, 'Welcome to Joomla!', 'welcome-to-joomla', '', '<p>Introtext</p>', '<p>Bodytext</p>', 1, 1, 0, 10, '2008-08-12 10:00:00', 42, '', '2008-08-12 10:00:00', 42, 0, '0000-00-00 00:00:00', '2006-01-03 01:00:00', '0000-00-00 00:00:00', '', '', '{"show_title":"","link_titles":"","show_intro":"","show_section":"","link_section":"","show_category":"","link_category":"","show_vote":"","show_author":"","show_create_date":"","show_modify_date":"","show_print_icon":"","show_email_icon":"","language":"en-GB","keyref":"","readmore":""}', 29, 0, 1, '', '', 1, 102, '{"robots":"","author":""}', 1, 'en-GB', ''),
(2, 33, 'Great Barrier Reef', 'great-barrier-reef', '', '<p>The Great Barrier Reef is the largest coral reef system composed of over 2,900 individual reefs[3] and 900 islands stretching for over 3,000 kilometres (1,600 mi) over an area of approximately 344,400 square kilometres (133,000 sq mi). The reef is located in the Coral Sea, off the coast of Queensland in northeast Australia.</p>\r\n<p>http://en.wikipedia.org/wiki/Great_Barrier_Reef</p>', '<p>The Great Barrier Reef can be seen from outer space and is the world''s biggest single structure made by living organisms. This reef structure is composed of and built by billions of tiny organisms, known as coral polyps. The Great Barrier Reef supports a wide diversity of life, and was selected as a World Heritage Site in 1981.CNN has labelled it one of the 7 natural wonders of the world. The Queensland National Trust has named it a state icon of Queensland.</p>\r\n<p>A large part of the reef is protected by the Great Barrier Reef Marine Park, which helps to limit the impact of human use, such as overfishing and tourism. Other environmental pressures to the reef and its ecosystem include water quality from runoff, climate change accompanied by mass coral bleaching, and cyclic outbreaks of the crown-of-thorns starfish.</p>\r\n<p>The Great Barrier Reef has long been known to and utilised by the Aboriginal Australian and Torres Strait Islander peoples, and is an important part of local groups'' cultures and spirituality. The reef is a very popular destination for tourists, especially in the Whitsundays and Cairns regions. Tourism is also an important economic activity for the region. Fishing also occurs in the region, generating AU$ 1 billion per year.</p>', 1, 0, 0, 24, '2009-06-22 11:07:08', 42, '', '2009-06-22 11:14:50', 42, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","article-allow_ratings":"","article-allow_comments":"","show_author":"","show_create_date":"","show_modify_date":"","show_print_icon":"","show_email_icon":"","readmore":"","page_title":"","layout":""}', 1, 0, 0, '', '', 1, 0, '{"robots":"","author":""}', 0, '', ''),
(3, 34, 'Cradle Mountain-Lake St Clair National Park', 'cradle-mountain-lake-st-clair-national-park', '', '<p>Cradle Mountain-Lake St Clair National Park is located in the Central Highlands area of Tasmania (Australia), 165 km northwest of Hobart. The park contains many walking trails, and is where hikes along the well-known Overland Track usually begins. Major features are Cradle Mountain and Barn Bluff in the northern end, Mount Pelion East, Mount Pelion West, Mount Oakleigh and Mount Ossa in the middle and Lake St Clair in the southern end of the park. The park is part of the Tasmanian Wilderness World Heritage Area.</p>\r\n<p>http://en.wikipedia.org/wiki/Cradle_Mountain-Lake_St_Clair_National_Park</p>', '<h3>Access and usage fee</h3>\r\n<p>Access from the south (Lake St. Clair) is usually from Derwent Bridge on the Lyell Highway. Northern access (Cradle Valley) is usually via Sheffield, Wilmot or Mole Creek. A less frequently used entrance is via the Arm River Track, from the east.</p>\r\n<p>In 2005, the Tasmanian Parks & Wildlife Service introduced a booking system & fee for use of the Overland Track over peak periods. Initially the fee was 100 Australian dollars, but this was raised to 150 Australian dollars in 2007. The money that is collected is used to finance the park ranger organisation, track maintenance, building of new facilities and rental of helicopter transport to remove waste from the toilets at the huts in the park.</p>', 1, 0, 0, 25, '2009-06-22 11:17:24', 42, '', '0000-00-00 00:00:00', 0, 0, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '', '{"show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","article-allow_ratings":"","article-allow_comments":"","show_author":"","show_create_date":"","show_modify_date":"","show_print_icon":"","show_email_icon":"","readmore":"","page_title":"","layout":""}', 1, 0, 0, '', '', 1, 0, '{"robots":"","author":""}', 0, '', '');

--
-- Dumping data for table `#__content_frontpage`
--

INSERT IGNORE INTO `#__content_frontpage` VALUES
(1, 0);

--
-- Dumping data for table `#__menu` (remove existing rows first)
--

TRUNCATE `#__menu`;

INSERT IGNORE INTO `#__menu` VALUES
(1, '', 'Menu_Item_Root', 'root', '', '', '', 1, 0, 0, 0, 0, 0, '0000-00-00 00:00:00', 0, 0, 0, '{"show_page_title":"1","page_title":"Welcome to the Frontpage","show_description":"0","show_description_image":"0","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","show_title":"1","pageclass_sfx":"","menu_image":"-1","secure":"0","orderby_pri":"","orderby_sec":"front","show_pagination":"2","show_pagination_results":"1","show_noauth":"0","link_titles":"0","show_intro":"1","show_section":"0","link_section":"0","show_category":"0","link_category":"0","show_author":"1","show_create_date":"1","show_modify_date":"1","show_item_navigation":"0","show_readmore":"1","show_vote":"0","show_icons":"1","show_pdf_icon":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1"}', 0, 19, 0),
(3, 'mainmenu', 'Administrator', 'administrator', 'administrator', 'administrator/', 'url', 1, 1, 1, 0, 2, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"menu_image":"-1"}', 9, 10, 0),
(2, 'mainmenu', 'Home', 'home', 'home', 'index.php?option=com_content&view=frontpage', 'component', 1, 1, 1, 20, 0, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"show_page_title":"1","page_title":"Welcome to the Frontpage","show_description":"0","show_description_image":"0","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","show_title":"1","pageclass_sfx":"","menu_image":"-1","secure":"0","orderby_pri":"","orderby_sec":"front","show_pagination":"2","show_pagination_results":"1","show_noauth":"0","link_titles":"0","show_intro":"1","show_section":"0","link_section":"0","show_category":"0","link_category":"0","show_author":"1","show_create_date":"1","show_modify_date":"1","show_item_navigation":"0","show_readmore":"1","show_vote":"0","show_icons":"1","show_pdf_icon":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1"}', 1, 2, 1),
(5, 'usermenu', 'Logout', 'logout', 'logout', 'index.php?option=com_user&view=login', 'component', 1, 1, 1, 14, 5, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 15, 16, 0),
(6, 'usermenu', 'Submit an Article', 'submit-an-article', 'submit-an-article', 'index.php?option=com_content&view=form&layout=edit', 'component', 1, 1, 1, 20, 3, 0, '0000-00-00 00:00:00', 0, 2, 0, '{"menu-anchor_title":"","menu-anchor_css":"","page_title":"","show_page_title":1,"page_heading":"","pageclass_sfx":"","menu_image":"","link_title":"","secure":0,"menu-meta_description":"","menu-meta_keywords":"","robots":""}', 11, 12, 0),
(7, 'usermenu', 'Submit a Web Link', 'submit-a-web-link', 'submit-a-web-link', 'index.php?option=com_weblinks&view=form&layout=edit', 'component', 1, 1, 1, 4, 4, 0, '0000-00-00 00:00:00', 0, 2, 0, '{"menu-anchor_title":"","menu-anchor_css":"","page_title":"","show_page_title":1,"page_heading":"","pageclass_sfx":"","menu_image":"","link_title":"","secure":0,"menu-meta_description":"","menu-meta_keywords":"","robots":""}', 13, 14, 0),
(8, 'mainmenu', 'Weblinks', 'weblinks', 'weblinks', 'index.php?option=com_weblinks&view=categories', 'component', 1, 1, 1, 4, 6, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"image":"-1","image_align":"right","show_feed_link":"1","show_comp_description":"","comp_description":"","show_link_hits":"","show_link_description":"","show_other_cats":"","show_headings":"","show_numbers":"","show_report":"","target":"","link_icons":"","page_title":"","show_page_title":"1","pageclass_sfx":"","menu_image":"-1","secure":"0"}', 17, 18, 0),
(9, 'mainmenu', 'Article Categories', 'article-categories', 'article-categories', 'index.php?option=com_content&view=categories', 'component', 1, 1, 1, 20, 0, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"Category":"12","menu-anchor_title":"","menu-anchor_css":"","page_title":"","show_page_title":1,"page_heading":"","pageclass_sfx":"","menu_image":"","link_title":"","secure":0,"menu-meta_description":"","menu-meta_keywords":"","robots":""}', 3, 4, 0),
(14, 'mainmenu', 'Contact', 'contact', 'contact', 'index.php?option=com_contact&amp;view=contact&amp;id=1', 'component', 1, 1, 1, 7, 0, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"show_contact_list":"0","show_category_crumb":"0","menu-anchor_title":"","menu-anchor_css":"","page_title":"","show_page_title":1,"page_heading":"","pageclass_sfx":"","menu_image":"","link_title":"","secure":0,"menu-meta_description":"","menu-meta_keywords":"","robots":""}', 5, 6, 0),
(15, 'usermenu', 'Your Profile', 'your-profile', 'your-profile', 'index.php?option=com_users&view=profile', 'component', 1, 1, 1, 31, 0, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"menu-anchor_title":"","menu-anchor_css":"","page_title":"","show_page_title":1,"page_heading":"","pageclass_sfx":"","menu_image":"","link_title":"","secure":0,"menu-meta_description":"","menu-meta_keywords":"","robots":""}', 7, 8, 0);

--
-- Dumping data for table `#__menu_types`
--

INSERT IGNORE INTO `#__menu_types` VALUES
(2, 'usermenu', 'User Menu', 'A Menu for logged in Users');

--
-- Dumping data for table `#__modules`
--

--
-- Dumping data for table `#__modules_menu`
--

--
-- Dumping data for table `#__newsfeeds`
--
INSERT IGNORE INTO `#__newsfeeds`  (`catid`,`id`,`name`,`alias`,`link`,`filename`,`published`,`numarticles`,`cache_time`,`checked_out`,`checked_out_time`,`ordering`,`rtl`,`access`,`language`)
VALUES
(28, 1, 'Joomla! Announcements', 'joomla-announcements', 'http://www.joomla.org/announcements.feed?type=rss', NULL, 1, 5, 3600, 0, '0000-00-00 00:00:00', 1,0,1,'en_GB'),
(28, 2, 'New Joomla! Extensions', 'new-joomla-extensions', 'http://feeds.joomla.org/JoomlaExtensions', NULL, 1, 5, 3600, 0, '0000-00-00 00:00:00', 1,0,1,'en_GB'),
(28, 3, 'Joomla! Security News', 'joomla-security-news', 'http://feeds.joomla.org/JoomlaSecurityNews', NULL, 1, 5, 3600, 0, '0000-00-00 00:00:00', 1,0,1,'en_GB'),
(28, 4, 'Joomla! Connect', 'joomla-connect', 'http://feeds.joomla.org/JoomlaConnect', NULL, 1, 5, 3600, 0, '0000-00-00 00:00:00', 1,0,1,'en_GB');

--
-- Dumping data for table `#__usergroups`
--

INSERT IGNORE INTO `#__usergroups` VALUES
	(9,2,7,8,'Park Rangers');


--
-- Dumping data for table `#__viewlevels`
--

INSERT IGNORE INTO `#__viewlevels` (`id`, `title`, `ordering`, `rules`) VALUES
(4, 'Confidential', 3, '[9]');

--
-- Dumping data for table `#__weblinks`
--

INSERT IGNORE INTO `#__weblinks` VALUES
(1, 20, 0, 'Joomla!', 'joomla', 'http://www.joomla.org', 'Home of Joomla!', '2005-02-14 15:19:02', 3, 1, 0, '0000-00-00 00:00:00', 1, 0, 1, 1, '{"target":"0"}', 'en-GB'),
(2, 21, 0, 'php.net', 'php', 'http://www.php.net', 'The language that Joomla! is developed in', '2004-07-07 11:33:24', 6, 1, 0, '0000-00-00 00:00:00', 3, 0, 1, 1, '{}', 'en-GB'),
(3, 21, 0, 'MySQL', 'mysql', 'http://www.mysql.com', 'The database that Joomla! uses', '2004-07-07 10:18:31', 1, 1, 0, '0000-00-00 00:00:00', 5, 0, 1, 1, '{}', 'en-GB'),
(4, 20, 0, 'OpenSourceMatters', 'opensourcematters', 'http://www.opensourcematters.org', 'Home of OSM', '2005-02-14 15:19:02', 11, 1, 0, '0000-00-00 00:00:00', 2, 0, 1, 1, '{"target":"0"}', 'en-GB'),
(5, 21, 0, 'Joomla! - Forums', 'joomla-forums', 'http://forum.joomla.org', 'Joomla! Forums', '2005-02-14 15:19:02', 4, 1, 0, '0000-00-00 00:00:00', 4, 0, 1, 1, '{"target":"0"}', 'en-GB'),
(6, 21, 0, 'Ohloh Tracking of Joomla!', 'ohloh-tracking-of-joomla', 'http://www.ohloh.net/projects/20', 'Objective reports from Ohloh about Joomla''s development activity. Joomla! has some star developers with serious kudos.', '2007-07-19 09:28:31', 1, 1, 0, '0000-00-00 00:00:00', 6, 0, 1, 1, '{"target":"0"}', 'en-GB');
