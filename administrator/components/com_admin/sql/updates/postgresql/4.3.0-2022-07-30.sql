--
-- Table structure for table `#__guidedtours`
--

CREATE TABLE IF NOT EXISTS "#__guidedtours" (
  "id" serial NOT NULL,
  "asset_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) NOT NULL,
  "description" text NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "extensions" text NOT NULL,
  "url" varchar(255) NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone,
  "checked_out" integer,
  "published" smallint DEFAULT 0 NOT NULL,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__guidedtours_idx_state" ON "#__guidedtours" ("published");
CREATE INDEX "#__guidedtours_idx_language" ON "#__guidedtours" ("language");

--
-- Dumping data for table `#__guidedtours`
--

INSERT INTO "#__guidedtours" ("id", "asset_id", "title", "description", "ordering", "extensions", "url", "created", "created_by", "modified", "modified_by", "checked_out_time", "checked_out", "published", "language", "note") VALUES
(1, 0, 'How to create a Guided Tour in Joomla Backend?', '<p>This Tour will show you how you can create a Guided Tour in the Joomla Backend!</p>', 0, '[\"com_guidedtours\"]', 'administrator/index.php?option=com_guidedtours&view=tours', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(2, 0, 'How to create Articles?', '<p>This Tour will show you how you can create Articles in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_content&view=articles', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(3, 0, 'How to create Categories?', '<p>This Tour will show you how you can create Categories in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_categories&view=categories&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(4, 0, 'How to create Menus?', '<p>This Tour will show you how you can create Menus in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_menus&view=menus', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(5, 0, 'How to create Tags?', '<p>This Tour will show you how you can create Tags in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_tags&view=tags', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(6, 0, 'How to create Banners?', '<p>This Tour will show you how you can create Banners in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_banners&view=banners', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(7, 0, 'How to create Contacts?', '<p>This Tour will show you how you can create Contacts in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_contact&view=contacts', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(8, 0, 'How to create News Feeds?', '<p>This Tour will show you how you can create News Feeds in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_newsfeeds&view=newsfeeds', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(9, 0, 'How to create Smart Search?', '<p>This Tour will show you how you can create Smart Search in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_finder&view=filters', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour'),
(10, 0, 'How to create Users?', '<p>This will show you how you can create Users in Joomla!</p>', 0, '[\"*\"]', 'administrator/index.php?option=com_users&view=users', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, NULL, 0, 1, '*', 'Default tour')
ON CONFLICT DO NOTHING;

SELECT setval('#__guidedtours_id_seq', 11, false);

-- --------------------------------------------------------

--
-- Table structure for table `#__guidedtour_steps`
--

CREATE TABLE IF NOT EXISTS "#__guidedtour_steps" (
  "id" serial NOT NULL,
  "tour_id" bigint DEFAULT 0 NOT NULL,
  "title" varchar(255) NOT NULL,
  "published" smallint DEFAULT 0 NOT NULL,
  "description" text NOT NULL,
  "ordering" bigint DEFAULT 0 NOT NULL,
  "step_no" bigint DEFAULT 0 NOT NULL,
  "position" varchar(255) NOT NULL,
  "target" varchar(255) NOT NULL,
  "type" bigint NOT NULL,
  "interactive_type" bigint DEFAULT 1 NOT NULL,
  "url" varchar(255) NOT NULL,
  "created" timestamp without time zone NOT NULL,
  "created_by" bigint DEFAULT 0 NOT NULL,
  "modified" timestamp without time zone NOT NULL,
  "modified_by" bigint DEFAULT 0 NOT NULL,
  "checked_out_time" timestamp without time zone,
  "checked_out" integer,
  "language" varchar(7) DEFAULT '' NOT NULL,
  "note" varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY ("id")
);

CREATE INDEX "#__guidedtours_steps_idx_tour_id" ON "#__guidedtour_steps" ("tour_id");
CREATE INDEX "#__guidedtours_steps_idx_state" ON "#__guidedtour_steps" ("published");
CREATE INDEX "#__guidedtours_steps_idx_language" ON "#__guidedtour_steps" ("language");

--
-- Dumping data for table `#__guidedtour_steps`
--

INSERT INTO "#__guidedtour_steps" ("id", "tour_id", "title", "published", "description", "ordering", "step_no", "position", "target", "type", "interactive_type", "url", "created", "created_by", "modified", "modified_by", "language") VALUES
(1, 1, 'Click here!', 1, '<p>This Tour will show you how you can create a Guided Tour in the Joomla Backend!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_guidedtours&view=tours', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(2, 1, 'Add title for your Tour', 1, '<p>Here you have to add the title of your Tour Step.</p>', 0, 1, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(3, 1, 'Add Content', 1, '<p>Add the content of your Tour here!</p>', 0, 1, 'bottom', '.tox-edit-area__iframe', 2, 3, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(4, 1, 'Plugin selector', 1, '<p>Select the extensions where you want to show your Tour. e.g If you are creating a tour which is only in "Users" extensions then select Users here.</p>', 0, 1, 'bottom', '.choices__inner', 0, 1, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(5, 1, 'URL', 1, '<p>Add Relative URL of the page from where you want to start your Tour.</p>', 0, 1, 'bottom', '#jform_url', 0, 1, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(6, 1, 'Save and Close', 1, '<p>Save and close the tour.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(7, 1, 'Congratulations!!!', 1, '<p>You successfully created your first Guided Tour!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_guidedtours&view=tour&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(8, 2, 'Click here!', 1, '<p>This Tour will show you how you can create Articles in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_content&view=articles', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(9, 2, 'Add title for your Article', 1, '<p>Here you have to add the title of your Article.</p>', 0, 1, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(10, 2, 'Alias', 1, '<p>You can write the internal name of this article. You can leave this blank and Joomla will fill a default value in lower case with dashes instead of spaces.</p>', 0, 1, 'bottom', '#jform_alias', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(11, 2, 'Add Content', 1, '<p>Add the content of your Article here!</p>', 0, 1, 'bottom', '.tox-edit-area__iframe', 1, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(12, 2, 'Status', 1, '<p>Here you can select Status for your article.</p>', 0, 1, 'bottom', '#jform_state', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(13, 2, 'Category', 1, '<p>Select the Category for this article.</p>', 0, 1, 'bottom', '.choices__inner', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(14, 2, 'Featured Article', 1, '<p>Click on the Featured tab to feature your article.</p>', 0, 1, 'bottom', '#jform_featured0', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(15, 2, 'Access Level', 1, '<p>Here you can select Access level from Public, Guest, Registered, Special and Super Users.</p>', 0, 1, 'bottom', '#jform_access', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(16, 2, 'Tags', 1, '<p>Select Tags for your article. You can also enter a new tag by typing the name in the field and pressing enter.</p>', 0, 1, 'bottom', '#jform_tags-lbl', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(17, 2, 'Note', 1, '<p>This is normally for the administrator use and does not show in the Frontend.</p>', 0, 1, 'bottom', '#jform_note', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(18, 2, 'Version Note', 1, '<p>This is an optional field to identify the version of this article.</p>', 0, 1, 'bottom', '#jform_version_note', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(19, 2, 'Save and Close ', 1, '<p>Save and close the Article.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(20, 2, 'Congratulations!!!', 1, '<p>You successfully created your Article in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_content&view=article&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(21, 3, 'Click here!', 1, '<p>This Tour will show you how you can create Categories in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_categories&view=categories&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(22, 3, 'Add title for your Categories', 1, '<p>Here you have to add the title of your Categories.</p>', 0, 1, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(23, 3, 'Alias', 1, '<p>You can write the internal name of this item. You can leave this blank and Joomla will fill a default value in lower case with dashes instead of spaces.</p>', 0, 1, 'bottom', '#jform_alias', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(24, 3, 'Add Content', 1, '<p>Add the content of your Category here!</p>', 0, 1, 'bottom', '.tox-edit-area__iframe', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(25, 3, 'Parent', 1, '<p>The item (category, menu item, and so on) is the parent of the item being edited.</p>', 0, 1, 'bottom', '.choices', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(26, 3, 'Status', 1, '<p>Here you can select Status for your category.</p>', 0, 1, 'bottom', '#jform_published', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(27, 3, 'Access Level', 1, '<p>Here you can select Access level from Public, Guest, Registered, Special and Super Users.</p>', 0, 1, 'bottom', '#jform_access', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(28, 3, 'Tags', 1, '<p>Select Tags for your Category. You can also enter a new tag by typing the name in the field and pressing enter.</p>', 0, 1, 'bottom', '#jform_tags-lbl', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(29, 3, 'Note', 1, '<p>This is normally for the administrator use and does not show in the Frontend.</p>', 0, 1, 'bottom', '#jform_note', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(30, 3, 'Version Note', 1, '<p>This is an optional field to identify the version of this item.</p>', 0, 1, 'bottom', '#jform_version_note', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(31, 3, 'Save and Close ', 1, '<p>Save and close the Category.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(32, 3, 'Congratulations!!!', 1, '<p>You successfully created your Category in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_categories&view=category&layout=edit&extension=com_content', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(33, 4, 'Click here!', 1, '<p>This Tour will show you how you can create Menus in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_menus&view=menus', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(34, 4, 'Add title for your Menu', 1, '<p>Here you have to add the title of your Menu.</p>', 0, 1, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(35, 4, 'Unique Name', 1, '<p>Here you have to write the system name of the menu.</p>', 0, 1, 'bottom', '#jform_menutype', 2, 2, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(36, 4, 'Description', 1, '<p>Add description about the purpose of the menu.</p>', 0, 1, 'bottom', '#jform_menudescription', 0, 1, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(37, 4, 'Save and Close ', 1, '<p>Save and close the menu.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(38, 4, 'Congratulations!!!', 1, '<p>You successfully created your Menu in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_menus&view=menu&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(39, 5, 'Click here!', 1, '<p>This Tour will show you how you can create Tags in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_tags&view=tags', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(40, 5, 'Add title for your Tags', 1, '<p>Here you have to add the title of your Tag.</p>', 0, 1, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(41, 5, 'Alias', 1, '<p>You can write the internal name of this item. You can leave this blank and Joomla will fill a default value in lower case with dashes instead of spaces.</p>', 0, 1, 'bottom', '#jform_alias', 0, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(42, 5, 'Add Content', 1, '<p>Add the content of your Tags here!</p>', 0, 1, 'bottom', '.tox-edit-area__iframe', 1, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(43, 5, 'Parent', 1, '<p>The item (category, menu item, and so on) is the parent of the item being edited.</p>', 0, 1, 'bottom', '.choices', 0, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(44, 5, 'Status', 1, '<p>Here you can select Status for your tag.</p>', 0, 1, 'bottom', '#jform_published', 0, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(45, 5, 'Access Level', 1, '<p>Here you can select Access level from Public, Guest, Registered, Special and Super Users.</p>', 0, 1, 'bottom', '#jform_access', 0, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(46, 5, 'Note', 1, '<p>This is normally for the administrator use and does not show in the Frontend.</p>', 0, 1, 'bottom', '#jform_note', 0, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(47, 5, 'Version Note', 1, '<p>This is an optional field to identify the version of this item.</p>', 0, 1, 'bottom', '#jform_version_note', 0, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(48, 5, 'Save and Close ', 1, '<p>Save and close the Tag.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(49, 5, 'Congratulations!!!', 1, '<p>You successfully created your Tag in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_tags&view=tag&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(50, 6, 'Click here!', 1, '<p>This Tour will show you how you can create Banners in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_banners&view=banners', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(51, 6, 'Add title for your Banner', 1, '<p>Here you have to add the title of your Banner.</p>', 0, 1, 'bottom', '#jform_name', 2, 2, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(52, 6, 'Alias', 1, '<p>You can write the internal name of this banner. You can leave this blank and Joomla will fill a default value in lower case with dashes instead of spaces.</p>', 0, 1, 'bottom', '#jform_alias', 0, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(53, 6, 'Add Details', 1, '<p>Add the Details of your Banner here!</p>', 0, 1, 'bottom', '.tox-edit-area__iframe', 0, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(54, 6, 'Status', 1, '<p>Here you can select Status for your banner.</p>', 0, 1, 'bottom', '#jform_state', 0, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(55, 6, 'Category', 1, '<p>Select the Category for this banner.</p>', 0, 1, 'bottom', '.choices__inner', 0, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(56, 6, 'Pinned', 1, '<p>Click on the toggle to Pin your Banner.</p>', 0, 1, 'bottom', '#jform_sticky1', 0, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(57, 6, 'Version Note', 1, '<p>This is an optional field to identify the version of this banner.</p>', 0, 1, 'bottom', '#jform_version_note', 0, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(58, 6, 'Save and Close ', 1, '<p>Save and close the Banner.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(59, 6, 'Congratulations!!!', 1, '<p>You successfully created your Banner in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_banners&view=banner&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(60, 7, 'Click here!', 1, '<p>This Tour will show you how you can create Contacts in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_contact&view=contacts', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(61, 7, 'Add title for your Contact', 1, '<p>Here you have to add the title of your Contact.</p>', 0, 1, 'bottom', '#jform_name', 2, 2, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(62, 7, 'Alias', 1, '<p>You can write the internal name of this contact. You can leave this blank and Joomla will fill a default value in lower case with dashes instead of spaces.</p>', 0, 1, 'bottom', '#jform_alias', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(63, 7, 'Add Content', 1, '<p>Add the content of your Contacts here!</p>', 0, 1, 'bottom', '.col-lg-9', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(64, 7, 'Status', 1, '<p>Here you can select Status for your contact.</p>', 0, 1, 'bottom', '#jform_published', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(65, 7, 'Category', 1, '<p>Select the Category for this contact.</p>', 0, 1, 'bottom', '.choices__inner', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(66, 7, 'Featured Contact', 1, '<p>Click on the Featured tab to feature your contact.</p>', 0, 1, 'bottom', '#jform_featured0', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(67, 7, 'Access Level', 1, '<p>Here you can select Access level from Public, Guest, Registered, Special and Super Users.</p>', 0, 1, 'bottom', '#jform_access', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(68, 7, 'Tags', 1, '<p>Select Tags for your contact. You can also enter a new tag by typing the name in the field and pressing enter.</p>', 0, 1, 'bottom', '#jform_tags-lbl', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(69, 7, 'Version Note', 1, '<p>This is an optional field to identify the version of this contact.</p>', 0, 1, 'bottom', '#jform_version_note', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(70, 7, 'Save and Close ', 1, '<p>Save and close the Contact.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(71, 7, 'Congratulations!!!', 1, '<p>You successfully created your Contact in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_contact&view=contact&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(72, 8, 'Click here!', 1, '<p>This Tour will show you how you can create News Feeds in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeeds', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(73, 8, 'Add title for your News Feeds', 1, '<p>Here you have to add the title of your News Feeds.</p>', 0, 1, 'bottom', '#jform_name', 2, 2, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(74, 8, 'Alias', 1, '<p>You can write the internal name of this news feed. You can leave this blank and Joomla will fill a default value in lower case with dashes instead of spaces.</p>', 0, 1, 'bottom', '#jform_alias', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(75, 8, 'Add Link', 1, '<p>Add the Link of this News Feeds here!</p>', 0, 1, 'bottom', '#jform_link', 2, 2, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(76, 8, 'Add Content', 1, '<p>Add the content of your News Feeds here!</p>', 0, 1, 'bottom', '.tox-edit-area__iframe', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(77, 8, 'Status', 1, '<p>Here you can select Status for your news feeds.</p>', 0, 1, 'bottom', '#jform_state', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(78, 8, 'Category', 1, '<p>Select the Category for this news feeds.</p>', 0, 1, 'bottom', '.choices__inner', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(79, 8, 'Access Level', 1, '<p>Here you can select Access level from Public, Guest, Registered, Special and Super Users.</p>', 0, 1, 'bottom', '#jform_access', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(80, 8, 'Tags', 1, '<p>Select Tags for your news feeds. You can also enter a new tag by typing the name in the field and pressing enter.</p>', 0, 1, 'bottom', '#jform_tags-lbl', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(81, 8, 'Version Note', 1, '<p>This is an optional field to identify the version of this news feeds.</p>', 0, 1, 'bottom', '#jform_version_note', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(82, 8, 'Save and Close ', 1, '<p>Save and close the News Feeds.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(83, 8, 'Congratulations!!!', 1, '<p>You successfully created your News Feeds in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_newsfeeds&view=newsfeed&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(84, 9, 'Click here!', 1, '<p>This Tour will show you how you can create Smart Search in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_finder&view=filters', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(85, 9, 'Add title for your Smart Search', 1, '<p>Here you have to add the title of your Smart Search.</p>', 0, 1, 'bottom', '#jform_title', 2, 2, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(86, 9, 'Alias', 1, '<p>You can write the internal name of this smart search. You can leave this blank and Joomla will fill a default value in lower case with dashes instead of spaces.</p>', 0, 1, 'bottom', '#jform_alias', 0, 1, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(87, 9, 'Add Content', 1, '<p>Add the content of your Smart Search here!</p>', 0, 1, 'bottom', '.col-lg-9', 0, 1, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(88, 9, 'Status', 1, '<p>Here you can select Status for your smart search.</p>', 0, 1, 'bottom', '#jform_state', 0, 1, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(89, 9, 'Save and Close ', 1, '<p>Save and close the Smart Search.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(90, 9, 'Congratulations!!!', 1, '<p>You successfully created your Smart Search in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_finder&view=filter&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),

(91, 10, 'Click here!', 1, '<p>This will show you how you can create Users in Joomla!</p>', 0, 1, 'bottom', '.button-new', 2, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(92, 10, 'Add Name of the User', 1, '<p>Here you have to add the name of the User.</p>', 0, 1, 'bottom', '#jform_name', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(93, 10, 'Add Login Name', 1, '<p>Enter the login name (Username) for the user.</p>', 0, 1, 'bottom', '#jform_username', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(94, 10, 'Password', 1, '<p>Fill in a (new) password. Although this field is not required, the user will not be able to log in when no password is set.</p>', 0, 1, 'bottom', '#jform_password', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(95, 10, 'Confirm Password', 1, '<p>Fill in the password from the field above again, to verify it. This field is required when you filled in the New password field.</p>', 0, 1, 'bottom', '#jform_password2', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(96, 10, 'Email', 1, '<p>Enter an email address for the user.</p>', 0, 1, 'bottom', '#jform_email', 2, 2, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(97, 10, 'Receive System Emails', 1, '<p>Set to yes, if you want to receive system emails.</p>', 0, 1, 'bottom', '#jform_sendEmail1', 0, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(98, 10, 'User Status', 1, '<p>Enable or block this user.</p>', 0, 1, 'bottom', '#jform_block0', 0, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(99, 10, 'Require Password Reset', 1, '<p>If set to yes, the user will have to reset their password the next time they log into the site.</p>', 0, 1, 'bottom', '#jform_requireReset0', 0, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(100, 10, 'Save and Close ', 1, '<p>Save and close the User.</p>', 0, 1, 'bottom', '#save-group-children-save', 2, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*'),
(101, 10, 'Congratulations!!!', 1, '<p>You successfully created your User in Joomla!</p>', 0, 1, 'bottom', '', 0, 1, 'administrator/index.php?option=com_users&view=user&layout=edit', CURRENT_TIMESTAMP, 0, CURRENT_TIMESTAMP, 0, '*')
ON CONFLICT DO NOTHING;

SELECT setval('#__guidedtour_steps_id_seq', 102, false);

-- Add new `#__extensions`
INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'com_guidedtours', 'component', 'com_guidedtours', '', 1, 1, 0, 0, 1, '', '{}', '', 0, 0),
(0, 'mod_guidedtours', 'module', 'mod_guidedtours', '', 1, 1, 1, 0, 1, '', '{}', '', 0, 0),
(0, 'plg_system_tour', 'plugin', 'tour', 'system', 0, 1, 1, 0, 0, '', '{}', '', 0, 0);
