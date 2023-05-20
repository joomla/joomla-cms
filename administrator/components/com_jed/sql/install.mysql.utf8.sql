SET FOREIGN_KEY_CHECKS = 0;

/* Templates for Emails being sent from JED */
DROP TABLE IF EXISTS `#__jed_message_templates`;

CREATE TABLE `#__jed_message_templates`
(
    `id`            int unsigned NOT NULL AUTO_INCREMENT,
    `title`         varchar(255) NOT NULL DEFAULT '',
    `subject`       varchar(255) NOT NULL DEFAULT '',
    `template`      text         NOT NULL,
    `email_type`    tinyint      NOT NULL DEFAULT '0',
    `ticket_status` tinyint      NOT NULL DEFAULT '0',
    `created_by`    int          NOT NULL DEFAULT '0',
    `modified_by`   int          NOT NULL DEFAULT '0',
    `created`       datetime,
    `modified`      datetime,
    `state`         tinyint(1)   NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

/*Data for the table `#__jed_message_templates` */


INSERT INTO `#__jed_message_templates`(`title`, `subject`, `template`, `email_type`, `created_by`, `modified_by`, `created`, `modified`, `state`) VALUES
('Review Report Received', 'Review Report Received', '<p>Your report has been successfully received and a ticket has been opened. A team member will look into the issue and you should receive an update to your ticket within the next 3 business days.</p>', 1, 652, 652, '2021-05-13 14:37:13', '2021-05-13 14:37:13', 1),
('Listing Submission Received', 'Listing Submission Received', '<p>Your submission has been successfully received.</p>\r\n<p>Your listing will be screened by an extension specialist. If it passes all of our requirements it will be published. If it does not pass the initial screening you will receive a notice outlining the errors found and the next steps to take. If you checked the field \"My extension includes external libraries\" it will be manually screened in the order it was received by a team member.</p>\r\n<p><strong>NOTE</strong>: Our extension specialists review the extensions with <strong>JED Checker</strong>.&nbsp;The JED checker can be used to identify which files have issues - <a href=\"http://extensions.joomla.org/extension/jedchecker\" rel=\"alternate\">http://extensions.joomla.org/extension/jedchecker</a>.&nbsp;<strong>You are still on time to do a last check up</strong>.</p>\r\n<hr />\r\n<h2>Support the Joomla Community. Sponsor Us!</h2>\r\n<p><a href=\"https://community.joomla.org/sponsorship-campaigns.html\"><img src=\"https://extensions.joomla.org/images/social-images/joomla-community-sponsorships.jpg\" alt=\"Sponsor Us!\" /></a></p>\r\n<p><a href=\"https://community.joomla.org/sponsorship-campaigns.html\" target=\"_blank\" rel=\"noopener noreferrer\"><strong>Community Sponsorships</strong></a> allow those looking to support the project with smaller sponsorship amounts. With community sponsorships available from $5, every little bit helps to make Joomla! better.</p>\r\n<p><a class=\"btn btn-primary\" href=\"https://community.joomla.org/community-2017.html\" target=\"_blank\" rel=\"noopener noreferrer\"> Sponsor Now! </a></p>\r\n<hr />\r\n<ul>\r\n<li><a href=\"http://extensions.joomla.org/index.php?option=com_jed&amp;view=extension&amp;layout=edit&amp;amp;id={fab_qs}extension_id{/fab_qs}\">Back to edit {fab_qs}core_title{/fab_qs}</a></li>\r\n<li><a href=\"http://extensions.joomla.org/browse/my-extensions\">View my listings</a></li>\r\n</ul>', 1, 652, 652, '2021-05-13 14:37:13', '2021-05-13 14:37:13', 1),
('Listing Update Received', 'Listing Update Received', '<p>Your extension&nbsp;has been successfully updated.</p>\r\n<hr />\r\n<h2>Support the Joomla Community. Sponsor Us!</h2>\r\n<p><a href=\"https://community.joomla.org/sponsorship-campaigns.html\"><img src=\"https://extensions.joomla.org/images/social-images/joomla-community-sponsorships.jpg\" alt=\"Sponsor Us!\" /></a></p>\r\n<p><a href=\"https://community.joomla.org/sponsorship-campaigns.html\" target=\"_blank\" rel=\"noopener noreferrer\"><strong>Community Sponsorships</strong></a> allow those looking to support the project with smaller sponsorship amounts. With community sponsorships available from $5, every little bit helps to make Joomla! better.</p>\r\n<p><a class=\"btn btn-primary\" href=\"https://community.joomla.org/community-2017.html\" target=\"_blank\" rel=\"noopener noreferrer\"> Sponsor Now! </a></p>\r\n<hr />\r\n<p><a class=\"btn btn-info\" href=\"https://extensions.joomla.org/index.php?option=com_jed&amp;view=extension&amp;layout=edit&amp;amp;id={fab_qs}extension_id{/fab_qs}&amp;Itemid=134\">Back to edit {fab_qs}core_title{/fab_qs}</a> <a class=\"btn btn-primary\" href=\"https://extensions.joomla.org/index.php?option=com_jed&amp;view=extension&amp;layout=default&amp;id={fab_qs}extension_id{/fab_qs}&amp;Itemid=145\">Continue to {fab_qs}core_title{/fab_qs}</a> <a class=\"btn\" href=\"https://extensions.joomla.org/browse/my-extensions\">View my listings</a></p>', 1, 652, 652, '2021-05-13 14:37:13', '2021-05-13 14:37:13', 1),
('Extension Report Received', 'Extension Report Received', '<p>Your report has been successfully received and a ticket has been opened. A team member will look into the issue and you should receive an update to your ticket within the next 3 business days.</p>', 1, 652, 652, '2021-05-13 14:38:15', '2021-05-13 14:38:15', 1),
('Extension - JED Checker Errors', 'Extension - JED Checker Errors', '<p>Hi,<br /><br />Your extension has been flagged with [XXX] errors. Please correct these issues and upload an amended zip file to your listing. The JED checker can be used to identify which files have issues - http://extensions.joomla.org/extension/jedchecker<br /><br />When you\'ve finished your changes, please open a ticket under “New Listing Support” and ask for your extension to be checked again.<br /><br />Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: ER1 - error_reporting(0) Found', 'Extension: ER1 - error_reporting(0) Found', '<p>Hi,<br /><br />Please remove any use of error_reporting(0) from your files, use of this is discouraged because Joomla provides an error reporting options in the Global Configuration. <br /><br />When you\'ve finished your changes, please upload an amended zip file to your listing and then open a ticket under “New Listing Support” and ask for your extension to be checked again.<br /><br />Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: NM1 - Install Name Doesn\'t Match Listing Name', 'Extension: NM1 - Install Name Doesn\'t Match Listing Name', '<p>Hi,<br /><br />The install name of your extension doesn\'t match the listing name. Please update the name in your xml/language files or request for the listing name to be changed.<br /><br />When you\'ve finished your changes, please upload an amended zip file to your listing and then open a ticket under “New Listing Support” and ask for your extension to be checked again, stating which of the above options you prefer.<br /><br />Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: LK2 - Invalid Download Link', 'Extension: LK2 - Invalid Download Link', '<p>Hi,<br /><br />Please update the download link so that it complies with the JED Rules as follows:</p>\r\n<ul>\r\n<li>Download links must point directly to the download or product page.</li>\r\n<li>You may point directly to the file itself if registration isn’t required.</li>\r\n<li>Download links may not point to \"Extension Installers\".</li>\r\n<li>If you offer multiple versions of an extension (for example, a Paid version and a Free version) you must only point the download link on your listing to a page that the version promoted is the one displayed on the JED.</li>\r\n<li>Links pointing to distribution sites that also distribute non-GPL Joomla extensions will not be accepted.</li>\r\n</ul>\r\n<p>When you\'ve finished your changes, please open a ticket under “New Listing Support” and ask for your extension to be checked again.<br /><br />Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: ZP1 - Zipfile Issues', 'Extension: ZP1 - Zipfile Issues', '<p>Hi,</p>\r\n<p>An issue has been detected with the zip file of your extension. Please ensure that the zip file of your extension has:</p>\r\n<ul>\r\n<li>Been packaged properly and is not corrupt and can be opened.</li>\r\n<li>Been uploaded correctly to your listing.</li>\r\n</ul>\r\n<p>When you\'ve done this, please upload an amended zip file to your listing and then open a ticket under “New Listing Support” and ask for your extension to be checked again.<br /><br />Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: NM2 - Extension Specific Naming Issue', 'Extension: NM2 - Extension Specific Naming Issue', '<p>Hi,<br /><br />The name of your extension doesn’t comply with the JED naming conventions - extension specific listings should have a name in the form “{Extension Name} for {Parent Extension}”.<br /><br />Please refer to the JED Naming Conventions: http://extensions.joomla.org/support/knowledgebase/item/extension-names and choose a compliant name for your listing. This new name needs to be reflected in the files of your extension, specifically in the xml and language files.<br /><br />When you’ve finished your changes, please upload an amended zip file to your listing and then open a ticket under “New Listing Support” and include the new name for your listing. A member of the team will then update the listing name to match the install name of your extension.<br /><br />Kind Regards,</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: TM2 - Use of the Joomla Trademark', 'Extension: TM2 - Use of the Joomla Trademark', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">The name of your extension doesn’t comply with the JED naming conventions - an extension name cannot include the word Joomla, unless in the form “{Extension Name} for Joomla”.</span></p>\r\n<p><span style=\"font-weight: 400;\">Please refer to the JED Naming Conventions: </span><a href=\"http://extensions.joomla.org/support/knowledgebase/item/extension-names\"><span style=\"font-weight: 400;\">http://extensions.joomla.org/support/knowledgebase/item/extension-names</span></a><span style=\"font-weight: 400;\"> and choose a compliant name for your listing. This new name needs to be reflected in the files of your extension, specifically in the xml and language files.</span></p>\r\n<p><span style=\"font-weight: 400;\">Alternatively, if you wish to use the word Joomla in any other form, please see the following links for more information </span><a href=\"https://tm.joomla.org/joomla-name-and-logo-use\"><span style=\"font-weight: 400;\">https://tm.joomla.org/joomla-name-and-logo-use</span></a><span style=\"font-weight: 400;\"> and </span><a href=\"https://tm.joomla.org/trademark\"><span style=\"font-weight: 400;\">https://tm.joomla.org/trademark</span></a><span style=\"font-weight: 400;\">. You can submit your request via the Trademark Contact Center link. If your request is approved, you must inform the JED of the decision.</span></p>\r\n<p><span style=\"font-weight: 400;\">When you’ve finished your changes, please open a ticket under “New Listing Support” and include the new name for your listing. A member of the team will then update the listing name to match the install name of your extension.</span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards,</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension - Hidden Files or Folders', 'Extension - Hidden Files or Folders', '<p>Hi,</p>\r\n<p>The zip file of your extension includes some hidden files/folders (e.g.__MACOSX folders or .DS_Store files), that can cause issues on some hosting providers. Please remove all occurrences of these files/folders and upload an amended zip file to your listing.</p>\r\n<p>When you\'ve done this, please open a ticket under “New Listing Support” and ask for your extension to be checked again.</p>\r\n<p>Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: NM3 - Non-Permitted Words in Name', 'Extension: NM3 - Non-Permitted Words in Name', '<p>Hi,</p>\r\n<p>The name of your extension doesn’t comply with the JED naming conventions - an extension name cannot include the words: component, module, plugin or extension</p>\r\n<p>Please refer to the JED Naming Conventions: http://extensions.joomla.org/support/knowledgebase/item/extension-names and choose a compliant name for your listing. This new name needs to be reflected in the files of your extension, specifically in the xml and language files.</p>\r\n<p>When you’ve finished your changes, please upload an amended zip file to your listing and then open a ticket under “New Listing Support” and include the new name for your listing. A member of the team will then update the listing name to match the install name of your extension.</p>\r\n<p>Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension - Plugin Install Name', 'Extension - Plugin Install Name', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">The name of your plugin does not comply with the JED naming conventions - plugins should have a name in the form “{Type} - {Extension Name}”.</span></p>\r\n<p><span style=\"font-weight: 400;\">When you’ve finished your changes and have uploaded an amended zip file to your listing, please open a ticket under “New Listing Support” and ask for it to be checked again.</span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension - Backlink Issues', 'Extension - Backlink Issues', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">Your extension contains backlinks that are not inline with the JED TOS. Extensions listed on the JED are permitted to insert backlinks to the developers distribution site, which will render in the HTML output of the extension. The JED has specific rules regarding backlinks:</span></p>\r\n<ul>\r\n<li><span style=\"font-weight: 400;\">Users must be able to remove the backlink (by editing the code)</span></li>\r\n<li><span style=\"font-weight: 400;\">Base64 or any other method to obfuscate the backlink is not permitted</span></li>\r\n<li><span style=\"font-weight: 400;\">No more than one backlink can be inserted</span></li>\r\n<li><span style=\"font-weight: 400;\">The backlink can only point to the developer\'s distribution site</span></li>\r\n</ul>\r\n<p><span style=\"font-weight: 400;\">See this page for more information. <a href=\"http://extensions.joomla.org/support/knowledgebase/item/backlinks\">http://extensions.joomla.org/support/knowledgebase/item/backlinks</a> </span></p>\r\n<p><span style=\"font-weight: 400;\">Please make the necessary amendments to your files and then upload an amended zipfile to your listing. When you\'ve done this, open a ticket under \"New Listing Support\" and ask for it to be checked again.</span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: LC2 - Paid Listing without License Link', 'Extension: LC2 - Paid Listing without License Link', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">You have listed your extension as \"Paid\", but have not added a link to the license page on your website. This is required according to the JED TOS. As the JED grows, new opportunities and questions arise. One issue that plagues the JED and its users is arriving at a site from a listing that is \"Paid\" only to find out that additional restrictions have been placed on the extension. To help monitor this, if your listing is Paid, you must include a link to your terms of service or license agreement.<br /></span></p>\r\n<p><span style=\"font-weight: 400;\">Please update the link and then open a ticket under \"New Listing Support\" and ask for it to be checked again.</span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension - Listed on VEL', 'Extension - Listed on VEL', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">Your extension has been listed in the the VEL. JED requires the VEL resolved link before your extension can be republished.</span></p>\r\n<p><span style=\"font-weight: 400;\">Please provide us with the VEL resolved link by opening a ticket under \"Unpublished Support\".<br /></span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension - Broken Links', 'Extension - Broken Links', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">Your extension has been unpublished because we have found that one or more of your links on your listing is not functioning. Please correct this link so that it goes to the correct URL.</span></p>\r\n<p><span style=\"font-weight: 400;\">When you have done this, please open a ticket under \"Unpublished Support\".<br /></span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Ticket - Misdirected Support Request', 'Ticket - Misdirected Support Request', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">This ticket system is for developers of extensions listed on JED - we are unable to provide support for your issue. For support questions about how to use an extension, please contact the developer directly via their website.</span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Ticket - Not Enough Detail', 'Ticket - Not Enough Detail', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">Your ticket does not include enough information for us to be able to look into your issue. If you still have an issue, please open a new ticket and include as much information as possible, including the name of the extension and issue details.</span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: LC1 - Licensing Violation', 'Extension: LC1 - Licensing Violation', '<p>Hi,</p>\r\n<p>An issue has been found with the licensing of your extension.</p>\r\n<p>Every listing must comply with the current GPL License that Joomla is distributed as. Currently, Joomla is distributed using GPL v2. There are other licenses that are compatible with the GPL v2, and those are acceptable as well. Additionally, the JED does not allow \"additional restrictions\" on top of the GPL. For example, you cannot limit the usage of your extension to limited number of domains. You may, however, sell \"support\" based on a limited number of domains.</p>\r\n<p>Please make the necessary amendments to ensure that your extension complies with the above restrictions. Also ensure that you have updated the relevant pages on your website, including the extension page and licensing page to reflect the changes that you have made.</p>\r\n<p>When you have finished your changes, please upload an amended zip file to your listing and then open a ticket under \"Current Listing Support\" and ask for your extension to be checked again.</p>\r\n<p>Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: LC3 - License Link does not Mention Extensions', 'Extension: LC3 - License Link does not Mention Extensions', '<p>Hi,</p>\r\n<p>The license link that you have provided does not mention extensions. Your license page should reference specifically how your extensions are licensed.</p>\r\n<p>Please make the necessary changes to this page on your website and then open a ticket under \"Current Listing Support\" and ask for it to be checked again.</p>\r\n<p>Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: LC4 - Invalid License Type', 'Extension: LC4 - Invalid License Type', '<p>Hi,</p>\r\n<p>Your extension has been found to have an invalid license type. Extensions are required to be GNU/GPL or AGPL licensed. <em><strong>LGPL is for library extensions only.</strong></em> Any other license type is unacceptable.</p>\r\n<p>Please make the necessary changes to your website and your extension, upload an amended zip file to your listing and then open a ticket under \"Current Listing Support\" and ask for it to be checked again.</p>\r\n<p>Kind Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: US1 - Update Server Requirement', 'Extension: US1 - Update Server Requirement', '<p><span style=\"font-weight: 400;\">Hi,</span></p>\r\n<p><span style=\"font-weight: 400;\">Extensions uploaded to JED after 10th January 2017 are required to implement the Joomla! Update System as detailed in this documentation page: </span><a href=\"support/knowledgebase/item/joomla-update-system-requirement\"><span style=\"font-weight: 400;\">https://extensions.joomla.org/support/knowledgebase/item/joomla-update-system-requirement</span></a></p>\r\n<p><span style=\"font-weight: 400;\">Please make the necessary changes to your extension and upload an amended zipfile to your listing. Also, please ensure that you have checked the ‘Joomla Update System’ checkbox on the edit page of your listing.</span></p>\r\n<p><span style=\"font-weight: 400;\">When you’ve finished your changes please open a ticket under “New Listing Support” and ask for it to be checked again.</span></p>\r\n<p><span style=\"font-weight: 400;\">Kind Regards,</span></p>\r\n<p><span style=\"font-weight: 400;\">[Your Name]</span></p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1),
('Extension: PE1 - Under Investigation', 'Extension: PE1 - Under Investigation', '<p>Hi,</p>\r\n<p>Your extension has been tagged as under investigation.&nbsp;</p>\r\n<p>Please open a ticket under \"Current Listing Support\" to contact the JED team.</p>\r\n<p>Best Regards</p>', 2, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1);
INSERT INTO `#__jed_message_templates`(`id`, `title`, `subject`, `template`, `email_type`, `created_by`, `modified_by`, `created`, `modified`, `state`) VALUES
(1000, 'System Messages: Thank you for contacting ', 'Confirmation of Submission', '<p>Thank you for contacting the Joomla Extension Directory (JED).</p>\r\n<p>A ticket has been created on our system and is awaiting review by a member of the JED Team.</p>\r\n<p>You will be notified by email when an update is made to your ticket.</p>\r\n<p>You can view your tickets by clicking on blah-blah-blah link.</p>', 1, 652, 652, '2021-05-13 14:40:36', '2021-05-13 14:40:36', 1);
UPDATE `#__jed_message_templates` set ticket_status=1 where id in (6,7,8,9,10,11,12,13,14,15,16,17,18,20,21,22,23,24,25);

/* Ticket Category Types */
DROP TABLE IF EXISTS `#__jed_ticket_categories`;

CREATE TABLE IF NOT EXISTS `#__jed_ticket_categories`
(
    `id`               int unsigned NOT NULL AUTO_INCREMENT,
    `categorytype`     varchar(255) DEFAULT '',
    `ordering`         int          DEFAULT '0',
    `state`            tinyint(1)   DEFAULT '1',
    `checked_out`      int unsigned,
    `checked_out_time` datetime,
    `created_by`       int          DEFAULT '0',
    `modified_by`      int          DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__jed_ticket_categories`(`id`, `categorytype`, `ordering`, `state`, `checked_out`, `checked_out_time`, `created_by`, `modified_by`) VALUES
(1, 'Unknown', 0, 1, NULL, NULL, 652, 652),
(2, 'Extension', 0, 1, NULL, NULL, 652, 652),
(3, 'Review', 0, 1, NULL, NULL, 652, 652),
(4, 'Joomla Site Issue', 0, 1, NULL, NULL, 652, 652),
(5, 'New Listing Support', 0, 1, NULL, NULL, 652, 652),
(6, 'Current Listing Support', 0, 1, NULL, NULL, 652, 652),
(7, 'Site Technical Issues', 0, 1, NULL, NULL, 652, 652),
(8, 'Unpublished Support', 0, 1, NULL, NULL, 652, 652),
(9, 'Reported Review', 0, 1, NULL, NULL, 652, 652),
(10, 'Reported Extension', 0, 1, NULL, NULL, 652, 652),
(11, 'Vulnerable Item Report', 0, 1, NULL, NULL, 652, 652),
(12, 'VEL Developer Update', 0, 1, NULL, NULL, 652, 652),
(13, 'VEL Abandonware Report', 0, 1, NULL, NULL, 652, 652);

/* Ticket Allocation Groups */
DROP TABLE IF EXISTS `#__jed_ticket_groups`;
CREATE TABLE IF NOT EXISTS `#__jed_ticket_groups`
(
    `id`               int unsigned NOT NULL AUTO_INCREMENT,
    `name`             varchar(255) DEFAULT '',
    `ordering`         int          DEFAULT '0',
    `state`            tinyint(1)   DEFAULT '1',
    `checked_out`      int unsigned,
    `checked_out_time` datetime,
    `created_by`       int          DEFAULT '0',
    `modified_by`      int          DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__jed_ticket_groups`(`id`, `name`, `ordering`, `state`, `checked_out`, `checked_out_time`, `created_by`, `modified_by`) VALUES
(1, 'Any', 0, 1, NULL, NULL, 652, 652),
(2, 'Team Leadership', 0, 1, NULL, NULL, 652, 652),
(3, 'Listing Specialist', 0, 1, NULL, NULL, 652, 652),
(4, 'Review Specialist', 0, 1, NULL, NULL, 652, 652),
(5, 'Support Speciailist', 0, 1, NULL, NULL, 652, 652),
(6, 'VEL Specialist', 0, 1, NULL, NULL, 652, 652);

/* Ticket Linked Items */
DROP TABLE IF EXISTS `#__jed_ticket_linked_item_types`;
CREATE TABLE IF NOT EXISTS `#__jed_ticket_linked_item_types`
(
    `id`               int unsigned NOT NULL AUTO_INCREMENT,
    `title`            varchar(255) DEFAULT '',
    `model`            varchar(255) DEFAULT '',
    `ordering`         int          DEFAULT '0',
    `state`            tinyint(1)   DEFAULT '1',
    `checked_out`      int unsigned,
    `checked_out_time` datetime,
    `created_by`       int          DEFAULT '0',
    `modified_by`      int          DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

INSERT INTO `#__jed_ticket_linked_item_types`(`id`, `title`, `model`, `ordering`, `state`, `checked_out`, `checked_out_time`, `created_by`, `modified_by`) VALUES
(1, 'Unknown', 'unknown', 0, 1, NULL, NULL, 652, 652),
(2, 'Extension', 'Extension', 1, 1, NULL, NULL, 652, 652),
(3, 'Review', 'Review', 0, 1, NULL, NULL, 652, 652),
(4, 'Vulnerable Item Initial Report', 'Velreport', 0, 1, NULL, NULL, 652, 652),
(5, 'Vulnerable Item Developer Update', 'Veldeveloperupdate', 0, 1, NULL, NULL, 652, 652),
(6, 'VEL Abandonware Report', 'Velabandonedreport', 0, 1, NULL, NULL, 652, 652);

/* Vulnerable Extension Lists */

DROP TABLE IF EXISTS `#__jed_vel_report`;
CREATE TABLE IF NOT EXISTS `#__jed_vel_report`
(
    `id`                               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reporter_fullname`                VARCHAR(255) NOT NULL DEFAULT '',
    `reporter_email`                   VARCHAR(255) NOT NULL DEFAULT '',
    `reporter_organisation`            VARCHAR(255) NOT NULL DEFAULT '',
    `pass_details_ok`                  INT          NOT NULL DEFAULT '0',
    `vulnerability_type`               INT          NOT NULL DEFAULT '0',
    `vulnerable_item_name`             VARCHAR(255) NOT NULL DEFAULT '',
    `vulnerable_item_version`          VARCHAR(255) NOT NULL DEFAULT '',
    `exploit_type`                     INT          NOT NULL DEFAULT '0',
    `exploit_other_description`        TEXT         NOT NULL,
    `vulnerability_description`        TEXT         NOT NULL,
    `vulnerability_how_found`          TEXT         NOT NULL,
    `vulnerability_actively_exploited` INT          NOT NULL DEFAULT '0',
    `vulnerability_publicly_available` INT          NOT NULL DEFAULT '0',
    `vulnerability_publicly_url`       VARCHAR(255) NOT NULL DEFAULT '',
    `vulnerability_specific_impact`    TEXT         NOT NULL,
    `developer_communication_type`     INT          NOT NULL DEFAULT '0',
    `developer_patch_download_url`     VARCHAR(255) NOT NULL DEFAULT '',
    `developer_name`                   VARCHAR(255) NOT NULL DEFAULT '',
    `developer_contact_email`          VARCHAR(255) NOT NULL DEFAULT '',
    `tracking_db_name`                 VARCHAR(255) NOT NULL DEFAULT '',
    `tracking_db_id`                   VARCHAR(255) NOT NULL DEFAULT '',
    `jed_url`                          VARCHAR(255) NOT NULL DEFAULT '',
    `developer_additional_info`        TEXT         NOT NULL,
    `download_url`                     VARCHAR(255) NOT NULL DEFAULT '',
    `consent_to_process`               INT          NOT NULL DEFAULT '0',
    `passed_to_vel`                    INT          NOT NULL DEFAULT '0',
    `vel_item_id`                      INT          NOT NULL DEFAULT '0',
    `data_source`                      INT          NOT NULL DEFAULT '0',
    `date_submitted`                   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `user_ip`                          VARCHAR(20)  NOT NULL DEFAULT '',
    `created_by`                       INT          NOT NULL DEFAULT '0',
    `modified_by`                      INT          NOT NULL DEFAULT '0',
    `created`                          DATETIME,
    `modified`                         DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__jed_vel_developer_update`;
CREATE TABLE IF NOT EXISTS `#__jed_vel_developer_update`
(
    `id`                      int unsigned NOT NULL AUTO_INCREMENT,
    `contact_fullname`        varchar(255) NOT NULL DEFAULT '',
    `contact_organisation`    varchar(255) NOT NULL DEFAULT '',
    `contact_email`           varchar(255) NOT NULL DEFAULT '',
    `vulnerable_item_name`    varchar(255) NOT NULL DEFAULT '',
    `vulnerable_item_version` varchar(255) NOT NULL DEFAULT '',
    `extension_update`        text         NOT NULL,
    `new_version_number`      varchar(255) NOT NULL DEFAULT '',
    `update_notice_url`       varchar(255) NOT NULL DEFAULT '',
    `changelog_url`           varchar(255) NOT NULL DEFAULT '',
    `download_url`            varchar(255) NOT NULL DEFAULT '',
    `consent_to_process`      int                   DEFAULT '0',
    `vel_item_id`             int          NOT NULL DEFAULT '0',
    `update_data_source`      int          NOT NULL DEFAULT '0',
    `update_date_submitted`   datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_user_ip`          varchar(20)  NOT NULL DEFAULT '',
    `created_by`              int          NOT NULL DEFAULT '0',
    `modified_by`             int          NOT NULL DEFAULT '0',
    `created`                 datetime,
    `modified`                datetime,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__jed_vel_abandoned_report`;
CREATE TABLE IF NOT EXISTS `#__jed_vel_abandoned_report`
(
    `id`                    int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `reporter_fullname`     VARCHAR(255)     NOT NULL,
    `reporter_email`        VARCHAR(255)     NOT NULL,
    `reporter_organisation` VARCHAR(255)     NULL     DEFAULT '',
    `extension_name`        VARCHAR(255)     NOT NULL,
    `developer_name`        VARCHAR(255)     NULL     DEFAULT '',
    `extension_version`     VARCHAR(255)     NULL     DEFAULT '',
    `extension_url`         VARCHAR(255)     NULL     DEFAULT '',
    `abandoned_reason`      TEXT             NULL,
    `consent_to_process`    VARCHAR(255)     NOT NULL DEFAULT '0',
    `passed_to_vel`         VARCHAR(255)     NULL     DEFAULT '0',
    `vel_item_id`           INT              NULL     DEFAULT 0,
    `data_source`           VARCHAR(255)     NULL     DEFAULT '0',
    `date_submitted`        DATETIME         NULL     DEFAULT CURRENT_TIMESTAMP,
    `user_ip`               VARCHAR(20)      NULL     DEFAULT '',
    `created_by`            INT(11)          NULL     DEFAULT 0,
    `modified_by`           INT(11)          NULL     DEFAULT 0,
    `created`               DATETIME,
    `modified`              DATETIME,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `#__jed_vel_vulnerable_item`;
CREATE TABLE IF NOT EXISTS `#__jed_vel_vulnerable_item`
(
    `id`                        int unsigned  NOT NULL AUTO_INCREMENT,
    `vulnerable_item_name`      varchar(255)  NOT NULL DEFAULT '',
    `vulnerable_item_version`   varchar(255)  NOT NULL DEFAULT '',
    `title`                     varchar(255)  NOT NULL DEFAULT '',
    `internal_description`      text          NOT NULL,
    `status`                    int           NOT NULL DEFAULT '0',
    `report_id`                 int           NOT NULL DEFAULT '-1',
    `jed`                       varchar(255)  NOT NULL DEFAULT '',
    `risk_level`                varchar(255)  NOT NULL DEFAULT '',
    `start_version`             varchar(255)  NOT NULL DEFAULT '',
    `vulnerable_version`        varchar(255)  NOT NULL DEFAULT '',
    `patch_version`             varchar(255)  NOT NULL DEFAULT '',
    `recommendation`            varchar(255)  NOT NULL DEFAULT '',
    `update_notice`             varchar(255)  NOT NULL DEFAULT '',
    `exploit_type`              int           NOT NULL DEFAULT '0',
    `exploit_other_description` text          NOT NULL,
    `xml_manifest`              text          NOT NULL,
    `manifest_location`         varchar(255)  NOT NULL DEFAULT '',
    `install_data`              varchar(255)  NOT NULL DEFAULT '',
    `discovered_by`             varchar(255)  NOT NULL DEFAULT '',
    `discoverer_public`         varchar(255)  NOT NULL DEFAULT '',
    `fixed_by`                  varchar(255)  NOT NULL DEFAULT '',
    `coordinated_by`            varchar(255)  NOT NULL DEFAULT '',
    `jira`                      varchar(255)  NOT NULL DEFAULT '',
    `cve_id`                    varchar(255)  NOT NULL DEFAULT '',
    `cwe_id`                    varchar(255)  NOT NULL DEFAULT '',
    `cvssthirty_base`           varchar(255)  NOT NULL DEFAULT '',
    `cvssthirty_base_score`     decimal(5, 2) NOT NULL DEFAULT '0.00',
    `cvssthirty_temp`           varchar(255)  NOT NULL DEFAULT '',
    `cvssthirty_temp_score`     decimal(5, 2) NOT NULL DEFAULT '0.00',
    `cvssthirty_env`            varchar(255)  NOT NULL DEFAULT '',
    `cvssthirty_env_score`      decimal(5, 2) NOT NULL DEFAULT '0.00',
    `public_description`        text          NOT NULL,
    `alias`                     varchar(255)  NOT NULL DEFAULT '',
    `created_by`                int           NOT NULL DEFAULT '0',
    `modified_by`               int           NOT NULL DEFAULT '0',
    `created`                   datetime,
    `modified`                  datetime,
    `checked_out`               int unsigned,
    `checked_out_time`          datetime,
    `state`                     tinyint       NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;


/* JED Ticket Messages */
DROP TABLE IF EXISTS `#__jed_ticket_messages`;
CREATE TABLE IF NOT EXISTS `#__jed_ticket_messages`
(
    `id`                int unsigned NOT NULL AUTO_INCREMENT,
    `ticket_id`         int          DEFAULT '0',
    `subject`           varchar(255) NOT NULL,
    `message`           text,
    `message_direction` int          DEFAULT '0',
    `ordering`          int          DEFAULT '0',
    `state`             tinyint(1)   DEFAULT '1',
    `checked_out`       int unsigned,
    `checked_out_time`  datetime,
    `created_by`        int          DEFAULT '0',
    `modified_by`       int          DEFAULT '0',
    `created_on`        datetime     DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

/* Table for Ticket Internal Notes */
DROP TABLE IF EXISTS `#__jed_ticket_internal_notes`;
CREATE TABLE IF NOT EXISTS `#__jed_ticket_internal_notes`
(
    `id`               INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_id`        INT(10)          NULL DEFAULT 0,
    `summary`          VARCHAR(255)     NULL DEFAULT '',
    `note`             TEXT             NULL,
    `ordering`         INT(11)          NULL DEFAULT 0,
    `state`            TINYINT(1)       NULL DEFAULT 1,
    `checked_out`      int unsigned,
    `checked_out_time` datetime,
    `created_by`       INT(11)          NULL DEFAULT 0,
    `modified_by`      INT(11)          NULL DEFAULT 0,
    `created_on`       DATETIME              DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

/* JED Tickets */
DROP TABLE IF EXISTS `#__jed_jedtickets`;
CREATE TABLE IF NOT EXISTS `#__jed_jedtickets`
(
    `id`                      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ticket_origin`           VARCHAR(255)     NULL DEFAULT '0',
    `ticket_category_type`    INT(10)          NULL DEFAULT 0,
    `ticket_subject`          VARCHAR(255)     NULL DEFAULT '',
    `ticket_text`             TEXT             NULL,
    `internal_notes`          TEXT             NULL,
    `uploaded_files_preview`  BLOB             NULL,
    `uploaded_files_location` VARCHAR(255)     NULL DEFAULT '',
    `allocated_group`         INT(10)          NULL DEFAULT 0,
    `allocated_to`            INT(11)          NULL DEFAULT 0,
    `linked_item_type`        INT(10)          NULL DEFAULT 0,
    `linked_item_id`          INT              NULL DEFAULT 0,
    `ticket_status`           VARCHAR(255)     NULL DEFAULT '0',
    `parent_id`               INT              NULL DEFAULT 0,
    `state`                   INT              NULL DEFAULT 0,
    `ordering`                INT              NULL DEFAULT 0,
    `created_by`              INT(11)          NULL DEFAULT 0,
    `created_on`              DATETIME,
    `modified_by`             INT(11)          NULL DEFAULT 0,
    `modified_on`             DATETIME,
    `checked_out`             int unsigned,
    `checked_out_time`        datetime,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

/*Table structure for table `#__jed_extension_supply_options` */

CREATE TABLE IF NOT EXISTS `#__jed_extension_supply_options`
(
    `id`               int unsigned NOT NULL AUTO_INCREMENT,
    `title`            varchar(255) DEFAULT '',
    `state`            tinyint(1)   DEFAULT '1',
    `ordering`         int          DEFAULT '0',
    `checked_out`      int unsigned,
    `checked_out_time` datetime,
    `created_by`       int          DEFAULT '0',
    `modified_by`      int          DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

/*Data for the table `#__jed_extension_supply_options` */

INSERT INTO `#__jed_extension_supply_options`(`id`, `title`, `state`, `ordering`, `checked_out`, `checked_out_time`, `created_by`, `modified_by`) VALUES
(1, 'Free', 1, 1, NULL, NULL, 627, 627),
(2, 'Paid', 1, 2, NULL, NULL, 627, 627),
(3, 'Cloud', 1, 3, NULL, NULL, 627, 627);


CREATE TABLE IF NOT EXISTS `#__jed_reviews`
(
    `id`                      int unsigned NOT NULL AUTO_INCREMENT,
    `extension_id`            int unsigned DEFAULT '0',
    `supply_option_id`        int unsigned DEFAULT '0',
    `title`                   varchar(400) DEFAULT '',
    `alias`                   varchar(400) DEFAULT NULL,
    `body`                    mediumtext,
    `functionality`           int DEFAULT '0',
    `functionality_comment`   text,
    `ease_of_use`             int DEFAULT '0',
    `ease_of_use_comment`     text,
    `support`                 int DEFAULT '0',
    `support_comment`         text,
    `documentation`           int DEFAULT '0',
    `documentation_comment`   text,
    `value_for_money`         int DEFAULT '0',
    `value_for_money_comment` text,
    `overall_score`           int DEFAULT '0',
    `used_for`                varchar(400) DEFAULT '',
    `version`                 varchar(255) DEFAULT NULL,
    `flagged`                 varchar(255) DEFAULT '0',
    `ip_address`              varchar(255) DEFAULT '',
    `published`               varchar(255) DEFAULT '0',
    `created_on`              datetime     DEFAULT NULL,
    `created_by`              int          DEFAULT '0',
    `ordering`                int          DEFAULT '0',
    `checked_out`             int unsigned,
    `checked_out_time`        datetime,
    PRIMARY KEY (`id`),
    KEY `FK_jed_reviews` (`extension_id`),
    KEY `FK_jed_reviews_user` (`created_by`),
    KEY `FK_jed_reviews_supply_option` (`supply_option_id`),
    CONSTRAINT `FKC_jed_reviews` FOREIGN KEY (`extension_id`) REFERENCES `#__jed_extensions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_reviews_user` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_reviews_supply_option` FOREIGN KEY (`supply_option_id`) REFERENCES `#__jed_extension_supply_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jed_reviews_comments`
(
    `id`         int unsigned NOT NULL AUTO_INCREMENT,
    `review_id`  int unsigned DEFAULT '0',
    `comments`   text,
    `ip_address` varchar(255) DEFAULT '',
    `created_on` datetime     DEFAULT NULL,
    `created_by` int          DEFAULT '0',
    `ordering`   int          DEFAULT '0',
    `state`      tinyint(1)   DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `FK_jed_reviews_comments` (`review_id`),
    KEY `FKC_jed_reviews_comments_user` (`created_by`),
    CONSTRAINT `FKC_jed_reviews_comments` FOREIGN KEY (`review_id`) REFERENCES `#__new_jed_reviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_reviews_comments_user` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jed_extensions`
(
    `id`                    int unsigned NOT NULL AUTO_INCREMENT,
    `joomla_versions`       varchar(255) DEFAULT '',
    `popular`               tinyint(1)   DEFAULT '0',
    `requires_registration` tinyint(1)   DEFAULT '0',
    `gpl_license_type`      varchar(255) DEFAULT '',
    `jed_internal_note`     text,
    `can_update`            tinyint(1)   DEFAULT '0',
    `video`                 varchar(255) DEFAULT '',
    `version`               varchar(255) DEFAULT '',
    `uses_updater`          tinyint(1)   DEFAULT '0',
    `includes`              varchar(255) DEFAULT '',
    `approved`              tinyint(1)   DEFAULT '0',
    `approved_time`         datetime     DEFAULT NULL,
    `second_contact_email`  varchar(100) DEFAULT '',
    `jed_checked`           tinyint(1)   DEFAULT '0',
    `uses_third_party`      tinyint(1)   DEFAULT '0',
    `primary_category_id`   int          DEFAULT NULL,
    `logo`                  varchar(255) DEFAULT '',
    `approved_notes`        text,
    `approved_reason`       varchar(255) DEFAULT '',
    `published_notes`       varchar(255) DEFAULT '',
    `published_reason`      varchar(255) DEFAULT '',
    `published`             tinyint(1)   DEFAULT '0',
    `checked_out`           int unsigned,
    `checked_out_time`      datetime,
    `created_by`            int          DEFAULT '0',
    `modified_by`           int          DEFAULT '0',
    `created_on`            datetime     DEFAULT NULL,
    `modified_on`           datetime     DEFAULT NULL,
    `state`                 int          DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `FK_jed_extensions_category_id` (`primary_category_id`),
    KEY `FKC_jed_extensions_user` (`created_by`),
    KEY `FKC_jed_extensions_moduser` (`modified_by`),
    CONSTRAINT `FKC_jed_extensions_category` FOREIGN KEY (`primary_category_id`) REFERENCES `#__categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_extensions_user` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_extensions_moduser` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jed_extension_varied_data`
(
    `id`                        int unsigned NOT NULL AUTO_INCREMENT,
    `extension_id`              int unsigned DEFAULT '0',
    `supply_option_id`          int unsigned DEFAULT '0',
    `title`                     varchar(255) DEFAULT '',
    `alias`                     varchar(255) DEFAULT NULL,
    `intro_text`                varchar(255) DEFAULT '',
    `description`               text,
    `homepage_link`             varchar(255) DEFAULT '',
    `download_link`             varchar(255) DEFAULT '',
    `demo_link`                 varchar(255) DEFAULT '',
    `support_link`              varchar(255) DEFAULT '',
    `documentation_link`        varchar(255) DEFAULT '',
    `license_link`              varchar(255) DEFAULT '',
    `translation_link`          varchar(255) DEFAULT '',
    `tags`                      varchar(255) DEFAULT '',
    `update_url`                varchar(255) DEFAULT '',
    `update_url_ok`             tinyint(1)   DEFAULT '0',
    `download_integration_type` varchar(255) DEFAULT '',
    `download_integration_url`  varchar(255) DEFAULT '',
    `logo`                      varchar(255) DEFAULT NULL,
    `is_default_data`           tinyint(1)   DEFAULT '0',
    `ordering`                  int          DEFAULT '0',
    `state`                     tinyint(1)   DEFAULT '0',
    `checked_out`               int unsigned,
    `checked_out_time`          datetime,
    `created_by`                int          DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `FK_jed_extension_varied_data` (`extension_id`),
    KEY `FK_jed_extension_varied_data_user` (`created_by`),
    KEY `FKC_jed_extension_varied_data_supply_option` (`supply_option_id`),
    CONSTRAINT `FKC_jed_extension_varied_data` FOREIGN KEY (`extension_id`) REFERENCES `#__jed_extensions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_extension_varied_data_user` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_extension_varied_data_supply_option` FOREIGN KEY (`supply_option_id`) REFERENCES `#__jed_extension_supply_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jed_extension_images`
(
    `id`               int unsigned NOT NULL AUTO_INCREMENT,
    `extension_id`     int unsigned DEFAULT '0',
    `supply_option_id` int          DEFAULT NULL,
    `filename`         text,
    `state`            tinyint(1)   DEFAULT '1',
    `ordering`         int          DEFAULT '0',
    `checked_out`      int unsigned,
    `checked_out_time` datetime,
    `created_by`       int          DEFAULT '0',
    `modified_by`      int          DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `FK_jed_extension_images` (`extension_id`),
    KEY `FK_jed_extension_images_user` (`created_by`),
    KEY `FK_jed_extension_images_moduser` (`modified_by`),
    CONSTRAINT `FKC_jed_extension_images` FOREIGN KEY (`extension_id`) REFERENCES `#__jed_extensions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_extension_images_user` FOREIGN KEY (`created_by`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FKC_jed_extension_images_moduser` FOREIGN KEY (`modified_by`) REFERENCES `#__users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jed_extension_scores`
(
    `id`                    int unsigned NOT NULL AUTO_INCREMENT,
    `extension_id`          int unsigned DEFAULT '0',
    `supply_option_id`      int unsigned DEFAULT '0',
    `functionality_score`   int          DEFAULT '0',
    `ease_of_use_score`     int          DEFAULT '0',
    `support_score`         int          DEFAULT '0',
    `value_for_money_score` int          DEFAULT '0',
    `documentation_score`   int          DEFAULT '0',
    `number_of_reviews`     int          DEFAULT '0',
    `state`                 tinyint(1)   DEFAULT '1',
    `ordering`              int          DEFAULT '0',
    `checked_out`           int unsigned,
    `checked_out_time`      datetime,
    `created_by`            int          DEFAULT '0',
    `modified_by`           int          DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `FK_jed_extension_scores` (`extension_id`),
    KEY `FK_jed_extension_scores_user` (`created_by`),
    KEY `FK_jed_extension_scores_moduser` (`modified_by`),
    KEY `FK_jed_extension_scores_supply_option` (`supply_option_id`),
    CONSTRAINT `FKC_jed_extension_scores_data_supply_option` FOREIGN KEY (`supply_option_id`) REFERENCES `#__jed_extension_supply_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jed_developers`
(
    `id`             int unsigned NOT NULL AUTO_INCREMENT,
    `user_id`        int          DEFAULT NULL,
    `developer_name` varchar(150) DEFAULT NULL,
    `suspicious`     tinyint(1)   DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
