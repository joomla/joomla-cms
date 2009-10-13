# @version		$Id$
#
# IMPORTANT - THIS FILE MUST BE SAVED WITH UTF-8 ENCODING ONLY. BEWARE IF EDITING!
#

--
-- Dumping data for table `#__assets`
--
INSERT INTO `#__assets` (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES
(27, 8, 29, 30, 2,'com_content.article.1','Welcome to Joomla!',''),
(28, 8, 15, 16, 2,'com_content.category.11','News',''),
(29, 8, 17, 28, 2,'com_content.category.12','Countries',''),
(30, 29, 18, 27, 3,'com_content.category.23','Australia',''),
(31, 30, 19, 20, 4,'com_content.category.24','Queensland',''),
(32, 30, 21, 26, 4,'com_content.category.25','Tasmania',''),
(33, 31, 22, 23, 5,'com_content.article.2','Great Barrier Reef',''),
(34, 32, 24, 25, 5,'com_content.article.3','Cradle Mountain-Lake St Clair National Park',''),
(35, 25, 65, 70, 2,'com_weblinks.category.20','Uncategorised Weblinks',''),
(36, 35, 66, 69, 3,'com_weblinks.category.21','Joomla! Specific Links',''),
(37, 36, 67, 68, 4,'com_weblinks.category.22','Other Resources','');


--
-- Dumping data for table `#__banner`
--

INSERT IGNORE INTO `#__banner` VALUES
(1, 1, 'banner', 'OSM 1', 'osm-1', 0, 43, 0, 'osmbanner1.png', 'http://www.opensourcematters.org', '2004-07-07 15:31:29', 1, 0, '0000-00-00 00:00:00', '', '', 13, '', 0, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', '', '');

--
-- Dumping data for table `#__bannerclient`
--

INSERT IGNORE INTO `#__bannerclient` VALUES
(1, 'Open Source Matters', 'Administrator', 'admin@opensourcematters.org', '', 0, '00:00:00', NULL);

--
-- Dumping data for table `#__categories`
--
INSERT IGNORE INTO `#__categories` VALUES
(11, 28, 1, 1, 2, 1, 'news', 'com_content', 'News', 'news', 'The top articles category.', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-22 19:42:11', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(12, 29, 1, 9, 16, 1, 'countries', 'com_content', 'Countries', 'countries', 'The latest news from the Joomla! Team', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-22 20:25:13', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(20, 35, 1, 3, 8, 1, 'uncategorised-weblinks', 'com_weblinks', 'Uncategorised Weblinks', 'uncategorised-weblinks', 'The top weblinks category.', 1, 42, '2009-03-15 15:34:27', 1, '{}', '', '', '', 0, '2009-06-22 19:42:11', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(21, 36, 20, 4, 7, 2, 'uncategorised-weblinks/joomla-specific-links', 'com_weblinks', 'Joomla! Specific Links', 'joomla-specific-links', 'A selection of links that are all related to the Joomla! Project.', 1, 42, '2009-03-15 15:34:27', 1, '{}', '', '', '', 0, '2009-06-22 19:42:11', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(22, 37, 21, 5, 6, 3, 'uncategorised-weblinks/joomla-specific-links/other-resources', 'com_weblinks', 'Other Resources', 'other-resources', '', 1, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-22 19:42:11', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(23, 30, 12, 10, 15, 2, 'countries/australia', 'com_content', 'Australia', 'australia', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-06-22 20:25:13', 0, '0000-00-00 00:00:00', 0, ''),
(24, 31, 23, 11, 12, 3, 'countries/australia/queensland', 'com_content', 'Queensland', 'queensland', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-06-22 20:25:17', 0, '0000-00-00 00:00:00', 0, ''),
(25, 32, 23, 13, 14, 3, 'countries/australia/tasmania', 'com_content', 'Tasmania', 'tasmania', '', 1, 0, '0000-00-00 00:00:00', 1, '', '', '', '', 0, '2009-06-22 20:25:17', 0, '0000-00-00 00:00:00', 0, '');

--
-- Dumping data for table `#__contact_details`
--

INSERT IGNORE INTO `#__contact_details` VALUES
(1, 'Name', 'name', 'Position', 'Street', 'Suburb', 'State', 'Country', 'Zip Code', 'Telephone', 'Fax', 'Miscellanous info', 'powered_by.png', 'top', 'email@email.com', 1, 1, 0, '0000-00-00 00:00:00', 1, 'show_name=1\r\nshow_position=1\r\nshow_email=0\r\nshow_street_address=1\r\nshow_suburb=1\r\nshow_state=1\r\nshow_postcode=1\r\nshow_country=1\r\nshow_telephone=1\r\nshow_mobile=1\r\nshow_fax=1\r\nshow_webpage=1\r\nshow_misc=1\r\nshow_image=1\r\nallow_vcard=0\r\ncontact_icons=0\r\nicon_address=\r\nicon_email=\r\nicon_telephone=\r\nicon_fax=\r\nicon_misc=\r\nshow_email_form=1\r\nemail_description=1\r\nshow_email_copy=1\r\nbanned_email=\r\nbanned_subject=\r\nbanned_text=', 0, 12, 1, '', '');

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
-- Dumping data for table `#__menu`
--

UPDATE `#__menu` SET `params` = 'show_page_title=1\npage_title=Welcome to the Frontpage\nshow_description=0\nshow_description_image=0\nnum_leading_articles=1\nnum_intro_articles=4\nnum_columns=2\nnum_links=4\nshow_title=1\npageclass_sfx=\nmenu_image=-1\nsecure=0\norderby_pri=\norderby_sec=front\nshow_pagination=2\nshow_pagination_results=1\nshow_noauth=0\nlink_titles=0\nshow_intro=1\nshow_section=0\nlink_section=0\nshow_category=0\nlink_category=0\nshow_author=1\nshow_create_date=1\nshow_modify_date=1\nshow_item_navigation=0\nshow_readmore=1\nshow_vote=0\nshow_icons=1\nshow_pdf_icon=1\nshow_print_icon=1\nshow_email_icon=1\nshow_hits=1\n\n' WHERE id = 1;

INSERT IGNORE INTO `#__menu` VALUES
(3, 'mainmenu', 'Administrator', 'administrator', 'administrator', 'administrator/', 'url', 1, 1, 1, 0, 2, 0, '0000-00-00 00:00:00', 0, 1, 0, 'menu_image=-1\r\n\r\n', 13, 14, 0),
(4, 'usermenu', 'Your Details', 'your-details', 'your-details', 'index.php?option=com_user&view=user&task=edit', 'component', 1, 1, 1, 14, 1, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 3, 4, 0),
(5, 'usermenu', 'Logout', 'logout', 'logout', 'index.php?option=com_user&view=login', 'component', 1, 1, 1, 14, 5, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 15, 16, 0),
(6, 'usermenu', 'Submit an Article', 'submit-an-article', 'submit-an-article', 'index.php?option=com_content&view=article&layout=form', 'component', 1, 1, 1, 20, 3, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 5, 6, 0),
(7, 'usermenu', 'Submit a Web Link', 'submit-a-web-link', 'submit-a-web-link', 'index.php?option=com_weblinks&view=weblink&layout=form', 'component', 1, 1, 1, 4, 4, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 11, 12, 0),
(8, 'mainmenu', 'Weblinks', 'weblinks', 'weblinks', 'index.php?option=com_weblinks&view=categories', 'component', 1, 1, 1, 4, 6, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"image":"-1","image_align":"right","show_feed_link":"1","show_comp_description":"","comp_description":"","show_link_hits":"","show_link_description":"","show_other_cats":"","show_headings":"","show_numbers":"","show_report":"","target":"","link_icons":"","page_title":"","show_page_title":"1","pageclass_sfx":"","menu_image":"-1","secure":"0"}', 9, 10, 0),
(9, 'mainmenu', 'Article Categories', 'article-categories', 'article-categories', 'index.php?option=com_content&view=categories', 'component', 1, 1, 1, 20, 0, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"Category":"12","menu-anchor_title":"","menu-anchor_css":"","page_title":"","show_page_title":1,"page_heading":"","pageclass_sfx":"","menu_image":"","link_title":"","secure":0,"menu-meta_description":"","menu-meta_keywords":"","robots":""}', 7, 8, 0);

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
(1, 20, 0, 'Joomla!', 'joomla', 'http://www.joomla.org', 'Home of Joomla!', '2005-02-14 15:19:02', 3, 1, 0, '0000-00-00 00:00:00', 1, 0, 1, 1, '{"target":"0"}'),
(2, 21, 0, 'php.net', 'php', 'http://www.php.net', 'The language that Joomla! is developed in', '2004-07-07 11:33:24', 6, 1, 0, '0000-00-00 00:00:00', 3, 0, 1, 1, '{}'),
(3, 21, 0, 'MySQL', 'mysql', 'http://www.mysql.com', 'The database that Joomla! uses', '2004-07-07 10:18:31', 1, 1, 0, '0000-00-00 00:00:00', 5, 0, 1, 1, '{}'),
(4, 20, 0, 'OpenSourceMatters', 'opensourcematters', 'http://www.opensourcematters.org', 'Home of OSM', '2005-02-14 15:19:02', 11, 1, 0, '0000-00-00 00:00:00', 2, 0, 1, 1, '{"target":"0"}'),
(5, 21, 0, 'Joomla! - Forums', 'joomla-forums', 'http://forum.joomla.org', 'Joomla! Forums', '2005-02-14 15:19:02', 4, 1, 0, '0000-00-00 00:00:00', 4, 0, 1, 1, '{"target":"0"}'),
(6, 21, 0, 'Ohloh Tracking of Joomla!', 'ohloh-tracking-of-joomla', 'http://www.ohloh.net/projects/20', 'Objective reports from Ohloh about Joomla''s development activity. Joomla! has some star developers with serious kudos.', '2007-07-19 09:28:31', 1, 1, 0, '0000-00-00 00:00:00', 6, 0, 1, 1, '{"target":"0"}');
