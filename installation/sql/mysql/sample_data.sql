# @version		$Id: sample_data.sql 334 2009-05-27 22:34:42Z andrew.eddie $
#
# IMPORTANT - THIS FILE MUST BE SAVED WITH UTF-8 ENCODING ONLY. BEWARE IF EDITING!
#

INSERT IGNORE INTO `#__access_assets` VALUES 
(2, 7, 'com_weblinks', 'weblink.1', 'Joomla!'),
(3, 7, 'com_weblinks', 'weblink.4', 'OpenSourceMatters'),
(4, 7, 'com_weblinks', 'weblink.2', 'php.net'),
(5, 7, 'com_weblinks', 'weblink.5', 'Joomla! - Forums'),
(6, 7, 'com_weblinks', 'weblink.3', 'MySQL'),
(7, 7, 'com_weblinks', 'weblink.6', 'Ohloh Tracking of Joomla!');

INSERT IGNORE INTO `#__access_asset_assetgroup_map` VALUES 
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1);

INSERT IGNORE INTO `jos_access_assets` VALUES 
(8, 1, 'core', 'menu.7', 'Weblinks');

INSERT IGNORE INTO `jos_access_asset_assetgroup_map` VALUES 
(8, 1);

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
(1, 0, 1, 4, 0, '', 'com_content', 'Latest', 'latest-news', 'The latest news from the Joomla! Team', 1, 0, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-18 13:51:00', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(2, 0, 5, 6, 0, '', 'com_weblinks', 'Joomla! Specific Links', 'joomla-specific-links', 'A selection of links that are all related to the Joomla! Project.', 1, 0, 42, '2009-03-15 15:34:27', 1, '{}', '', '', '', 0, '2009-06-18 13:51:00', 0, '0000-00-00 00:00:00', 0, 'en_GB'),
(3, 0, 7, 8, 0, '', 'com_weblinks', 'Other Resources', 'other-resources', '', 1, 0, 0, '0000-00-00 00:00:00', 1, '{}', '', '', '', 0, '2009-06-18 13:51:00', 0, '0000-00-00 00:00:00', 0, 'en_GB');

--
-- Dumping data for table `#__contact_details`
--

INSERT IGNORE INTO `#__contact_details` VALUES
(1, 'Name', 'name', 'Position', 'Street', 'Suburb', 'State', 'Country', 'Zip Code', 'Telephone', 'Fax', 'Miscellanous info', 'powered_by.png', 'top', 'email@email.com', 1, 1, 0, '0000-00-00 00:00:00', 1, 'show_name=1\r\nshow_position=1\r\nshow_email=0\r\nshow_street_address=1\r\nshow_suburb=1\r\nshow_state=1\r\nshow_postcode=1\r\nshow_country=1\r\nshow_telephone=1\r\nshow_mobile=1\r\nshow_fax=1\r\nshow_webpage=1\r\nshow_misc=1\r\nshow_image=1\r\nallow_vcard=0\r\ncontact_icons=0\r\nicon_address=\r\nicon_email=\r\nicon_telephone=\r\nicon_fax=\r\nicon_misc=\r\nshow_email_form=1\r\nemail_description=1\r\nshow_email_copy=1\r\nbanned_email=\r\nbanned_subject=\r\nbanned_text=', 0, 12, 1, '', '');

--
-- Dumping data for table `#__content`
--

INSERT IGNORE INTO `#__content` VALUES
(1, 'Welcome to Joomla!', 'welcome-to-joomla', '', '<p>Introtext</p>', '<p>Bodytext</p>', 1, 1, 0, 1, '2008-08-12 10:00:00', 42, '', '2008-08-12 10:00:00', 42, 0, '0000-00-00 00:00:00', '2006-01-03 01:00:00', '0000-00-00 00:00:00', '', '', '{"show_title":"","link_titles":"","show_intro":"","show_section":"","link_section":"","show_category":"","link_category":"","show_vote":"","show_author":"","show_create_date":"","show_modify_date":"","show_print_icon":"","show_email_icon":"","language":"en-GB","keyref":"","readmore":""}', 29, 0, 1, '', '', 1, 92, '{"robots":"","author":""}', 1, 'en-GB', '');

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
(3, 'mainmenu', 'Administrator', 'administrator', 'administrator', 'administrator/', 'url', 1, 1, 1, 0, 2, 0, '0000-00-00 00:00:00', 0, 1, 0, 'menu_image=-1\r\n\r\n', 5, 6, 0),
(4, 'usermenu', 'Your Details', 'your-details', 'your-details', 'index.php?option=com_user&view=user&task=edit', 'component', 1, 1, 1, 14, 1, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 3, 4, 0),
(5, 'usermenu', 'Logout', 'logout', 'logout', 'index.php?option=com_user&view=login', 'component', 1, 1, 1, 14, 5, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 11, 12, 0),
(6, 'usermenu', 'Submit an Article', 'submit-an-article', 'submit-an-article', 'index.php?option=com_content&view=article&layout=form', 'component', 1, 1, 1, 20, 3, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 7, 8, 0),
(7, 'usermenu', 'Submit a Web Link', 'submit-a-web-link', 'submit-a-web-link', 'index.php?option=com_weblinks&view=weblink&layout=form', 'component', 1, 1, 1, 4, 4, 0, '0000-00-00 00:00:00', 0, 2, 0, '', 9, 10, 0),
(8, 'mainmenu', 'Weblinks', 'weblinks', 'weblinks', 'index.php?option=com_weblinks&view=categories', 'component', 1, 1, 1, 4, 6, 0, '0000-00-00 00:00:00', 0, 1, 0, '{"image":"-1","image_align":"right","show_feed_link":"1","show_comp_description":"","comp_description":"","show_link_hits":"","show_link_description":"","show_other_cats":"","show_headings":"","show_numbers":"","show_report":"","target":"","link_icons":"","page_title":"","show_page_title":"1","pageclass_sfx":"","menu_image":"-1","secure":"0"}', 13, 14, 0);

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
-- Dumping data for table `#__weblinks`
--

INSERT IGNORE INTO `#__weblinks` VALUES
(1, 2, 0, 'Joomla!', 'joomla', 'http://www.joomla.org', 'Home of Joomla!', '2005-02-14 15:19:02', 3, 1, 0, '0000-00-00 00:00:00', 1, 0, 1, 1, '{"target":"0"}'),
(2, 3, 0, 'php.net', 'php', 'http://www.php.net', 'The language that Joomla! is developed in', '2004-07-07 11:33:24', 6, 1, 0, '0000-00-00 00:00:00', 3, 0, 1, 1, '{}'),
(3, 3, 0, 'MySQL', 'mysql', 'http://www.mysql.com', 'The database that Joomla! uses', '2004-07-07 10:18:31', 1, 1, 0, '0000-00-00 00:00:00', 5, 0, 1, 1, '{}'),
(4, 2, 0, 'OpenSourceMatters', 'opensourcematters', 'http://www.opensourcematters.org', 'Home of OSM', '2005-02-14 15:19:02', 11, 1, 0, '0000-00-00 00:00:00', 2, 0, 1, 1, '{"target":"0"}'),
(5, 3, 0, 'Joomla! - Forums', 'joomla-forums', 'http://forum.joomla.org', 'Joomla! Forums', '2005-02-14 15:19:02', 4, 1, 0, '0000-00-00 00:00:00', 4, 0, 1, 1, '{"target":"0"}'),
(6, 3, 0, 'Ohloh Tracking of Joomla!', 'ohloh-tracking-of-joomla', 'http://www.ohloh.net/projects/20', 'Objective reports from Ohloh about Joomla''s development activity. Joomla! has some star developers with serious kudos.', '2007-07-19 09:28:31', 1, 1, 0, '0000-00-00 00:00:00', 6, 0, 1, 1, '{"target":"0"}');
