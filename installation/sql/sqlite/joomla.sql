
-- Table structure for table #__assets

CREATE TABLE IF NOT EXISTS #__assets (
id INTEGER PRIMARY KEY AUTOINCREMENT,
parent_id INTEGER NOT NULL DEFAULT '0',
lft INTEGER NOT NULL DEFAULT '0',
rgt INTEGER NOT NULL DEFAULT '0',
level INTEGER NOT NULL,
name varchar(50) NOT NULL,
title varchar(100) NOT NULL,
rules varchar(5120) NOT NULL
);
-- Table structure for table #__associations

CREATE TABLE IF NOT EXISTS #__associations (
id varchar(50) PRIMARY KEY NOT NULL,
context varchar(50) NOT NULL,
key char(32) NOT NULL
);
-- Table structure for table #__banner_clients

CREATE TABLE IF NOT EXISTS #__banner_clients (
id INTEGER PRIMARY KEY AUTOINCREMENT,
name varchar(255) NOT NULL,
contact varchar(255) NOT NULL,
email varchar(255) NOT NULL,
extrainfo text NOT NULL,
state tinyint(3) NOT NULL DEFAULT '0',
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
metakey text NOT NULL,
own_prefix tinyint(4) NOT NULL DEFAULT '0',
metakey_prefix varchar(255) NOT NULL,
purchase_type tinyint(4) NOT NULL DEFAULT '-1',
track_clicks tinyint(4) NOT NULL DEFAULT '-1',
track_impressions tinyint(4) NOT NULL DEFAULT '-1'
);
-- Table structure for table #__banner_tracks

CREATE TABLE IF NOT EXISTS #__banner_tracks (
track_date datetime PRIMARY KEY NOT NULL,
track_type INTEGER NOT NULL,
banner_id INTEGER NOT NULL,
count INTEGER NOT NULL DEFAULT '0'
);
-- Table structure for table #__banners

CREATE TABLE IF NOT EXISTS #__banners (
id INTEGER PRIMARY KEY AUTOINCREMENT,
cid INTEGER NOT NULL DEFAULT '0',
type INTEGER NOT NULL DEFAULT '0',
name varchar(255) NOT NULL,
alias varchar(255) NOT NULL,
imptotal INTEGER NOT NULL DEFAULT '0',
impmade INTEGER NOT NULL DEFAULT '0',
clicks INTEGER NOT NULL DEFAULT '0',
clickurl varchar(200) NOT NULL,
state tinyint(3) NOT NULL DEFAULT '0',
catid INTEGER NOT NULL DEFAULT '0',
description text NOT NULL,
custombannercode varchar(2048) NOT NULL,
sticky tinyint(1) NOT NULL DEFAULT '0',
ordering INTEGER NOT NULL DEFAULT '0',
metakey text NOT NULL,
params text NOT NULL,
own_prefix tinyint(1) NOT NULL DEFAULT '0',
metakey_prefix varchar(255) NOT NULL,
purchase_type tinyint(4) NOT NULL DEFAULT '-1',
track_clicks tinyint(4) NOT NULL DEFAULT '-1',
track_impressions tinyint(4) NOT NULL DEFAULT '-1',
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
reset datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
language char(7) NOT NULL
);
-- Table structure for table #__categories

CREATE TABLE IF NOT EXISTS #__categories (
id INTEGER PRIMARY KEY AUTOINCREMENT,
asset_id INTEGER NOT NULL DEFAULT '0',
parent_id INTEGER NOT NULL DEFAULT '0',
lft INTEGER NOT NULL DEFAULT '0',
rgt INTEGER NOT NULL DEFAULT '0',
level INTEGER NOT NULL DEFAULT '0',
path varchar(255) NOT NULL,
extension varchar(50) NOT NULL,
title varchar(255) NOT NULL,
alias varchar(255) NOT NULL,
note varchar(255) NOT NULL,
description mediumtext NOT NULL,
published tinyint(1) NOT NULL DEFAULT '0',
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
access INTEGER NOT NULL DEFAULT '0',
params text NOT NULL,
metadesc varchar(1024) NOT NULL,
metakey varchar(1024) NOT NULL,
metadata varchar(2048) NOT NULL,
created_user_id INTEGER NOT NULL DEFAULT '0',
created_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
modified_user_id INTEGER NOT NULL DEFAULT '0',
modified_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
hits INTEGER NOT NULL DEFAULT '0',
language char(7) NOT NULL
);
-- Table structure for table #__contact_details

CREATE TABLE IF NOT EXISTS #__contact_details (
id INTEGER PRIMARY KEY AUTOINCREMENT,
name varchar(255) NOT NULL,
alias varchar(255) NOT NULL,
con_position varchar(255),
address text,
suburb varchar(100),
state varchar(100),
country varchar(100),
postcode varchar(100),
telephone varchar(255),
fax varchar(255),
misc mediumtext,
image varchar(255),
imagepos varchar(20),
email_to varchar(255),
default_con tinyint(1) NOT NULL DEFAULT '0',
published tinyint(1) NOT NULL DEFAULT '0',
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
ordering INTEGER NOT NULL DEFAULT '0',
params text NOT NULL,
user_id INTEGER NOT NULL DEFAULT '0',
catid INTEGER NOT NULL DEFAULT '0',
access INTEGER NOT NULL DEFAULT '0',
mobile varchar(255) NOT NULL,
webpage varchar(255) NOT NULL,
sortname1 varchar(255) NOT NULL,
sortname2 varchar(255) NOT NULL,
sortname3 varchar(255) NOT NULL,
language char(7) NOT NULL,
created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
created_by INTEGER NOT NULL DEFAULT '0',
created_by_alias varchar(255) NOT NULL,
modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
modified_by INTEGER NOT NULL DEFAULT '0',
metakey text NOT NULL,
metadesc text NOT NULL,
metadata text NOT NULL,
featured tinyint(3) NOT NULL DEFAULT '0',
xreference varchar(50) NOT NULL,
publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
);
-- Table structure for table #__content

CREATE TABLE IF NOT EXISTS #__content (
id INTEGER PRIMARY KEY AUTOINCREMENT,
asset_id INTEGER NOT NULL DEFAULT '0',
title varchar(255) NOT NULL,
alias varchar(255) NOT NULL,
title_alias varchar(255) NOT NULL,
introtext mediumtext NOT NULL,
fulltext mediumtext NOT NULL,
state tinyint(3) NOT NULL DEFAULT '0',
sectionid INTEGER NOT NULL DEFAULT '0',
mask INTEGER NOT NULL DEFAULT '0',
catid INTEGER NOT NULL DEFAULT '0',
created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
created_by INTEGER NOT NULL DEFAULT '0',
created_by_alias varchar(255) NOT NULL,
modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
modified_by INTEGER NOT NULL DEFAULT '0',
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
images text NOT NULL,
urls text NOT NULL,
attribs varchar(5120) NOT NULL,
version INTEGER NOT NULL DEFAULT '1',
parentid INTEGER NOT NULL DEFAULT '0',
ordering INTEGER NOT NULL DEFAULT '0',
metakey text NOT NULL,
metadesc text NOT NULL,
access INTEGER NOT NULL DEFAULT '0',
hits INTEGER NOT NULL DEFAULT '0',
metadata text NOT NULL,
featured tinyint(3) NOT NULL DEFAULT '0',
language char(7) NOT NULL,
xreference varchar(50) NOT NULL
);
-- Table structure for table #__content_frontpage

CREATE TABLE IF NOT EXISTS #__content_frontpage (
content_id INTEGER PRIMARY KEY NOT NULL DEFAULT '0',
ordering INTEGER NOT NULL DEFAULT '0'
);
-- Table structure for table #__content_rating

CREATE TABLE IF NOT EXISTS #__content_rating (
content_id INTEGER PRIMARY KEY NOT NULL DEFAULT '0',
rating_sum INTEGER NOT NULL DEFAULT '0',
rating_count INTEGER NOT NULL DEFAULT '0',
lastip varchar(50) NOT NULL
);
-- Table structure for table #__core_log_searches

CREATE TABLE IF NOT EXISTS #__core_log_searches (
search_term varchar(128) NOT NULL,
hits INTEGER NOT NULL DEFAULT '0'
);
-- Table structure for table #__extensions

CREATE TABLE IF NOT EXISTS #__extensions (
extension_id INTEGER PRIMARY KEY AUTOINCREMENT,
name varchar(100) NOT NULL,
type varchar(20) NOT NULL,
element varchar(100) NOT NULL,
folder varchar(100) NOT NULL,
client_id tinyint(3) NOT NULL,
enabled tinyint(3) NOT NULL DEFAULT '1',
access INTEGER NOT NULL DEFAULT '1',
protected tinyint(3) NOT NULL DEFAULT '0',
manifest_cache text NOT NULL,
params text NOT NULL,
custom_data text NOT NULL,
system_data text NOT NULL,
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
ordering INTEGER DEFAULT '0',
state INTEGER DEFAULT '0'
);
-- Table structure for table #__languages

CREATE TABLE IF NOT EXISTS #__languages (
lang_id INTEGER PRIMARY KEY AUTOINCREMENT,
lang_code char(7) NOT NULL,
title varchar(50) NOT NULL,
title_native varchar(50) NOT NULL,
sef varchar(50) NOT NULL,
image varchar(50) NOT NULL,
description varchar(512) NOT NULL,
metakey text NOT NULL,
metadesc text NOT NULL,
sitename varchar(1024) NOT NULL,
published INTEGER NOT NULL DEFAULT '0',
ordering INTEGER NOT NULL DEFAULT '0'
);
-- Table structure for table #__menu

CREATE TABLE IF NOT EXISTS #__menu (
id INTEGER PRIMARY KEY AUTOINCREMENT,
menutype varchar(24) NOT NULL,
title varchar(255) NOT NULL,
alias varchar(255) NOT NULL,
note varchar(255) NOT NULL,
path varchar(1024) NOT NULL,
link varchar(1024) NOT NULL,
type varchar(16) NOT NULL,
published tinyint(4) NOT NULL DEFAULT '0',
parent_id INTEGER NOT NULL DEFAULT '1',
level INTEGER NOT NULL DEFAULT '0',
component_id INTEGER NOT NULL DEFAULT '0',
ordering INTEGER NOT NULL DEFAULT '0',
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
browserNav tinyint(4) NOT NULL DEFAULT '0',
access INTEGER NOT NULL DEFAULT '0',
img varchar(255) NOT NULL,
template_style_id INTEGER NOT NULL DEFAULT '0',
params text NOT NULL,
lft INTEGER NOT NULL DEFAULT '0',
rgt INTEGER NOT NULL DEFAULT '0',
home tinyint(3) NOT NULL DEFAULT '0',
language char(7) NOT NULL,
client_id tinyint(4) NOT NULL DEFAULT '0'
);
-- Table structure for table #__menu_types

CREATE TABLE IF NOT EXISTS #__menu_types (
id INTEGER PRIMARY KEY AUTOINCREMENT,
menutype varchar(24) NOT NULL,
title varchar(48) NOT NULL,
description varchar(255) NOT NULL
);
-- Table structure for table #__messages

CREATE TABLE IF NOT EXISTS #__messages (
message_id INTEGER PRIMARY KEY AUTOINCREMENT,
user_id_from INTEGER NOT NULL DEFAULT '0',
user_id_to INTEGER NOT NULL DEFAULT '0',
folder_id tinyint(3) NOT NULL DEFAULT '0',
date_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
state tinyint(1) NOT NULL DEFAULT '0',
priority tinyint(1) NOT NULL DEFAULT '0',
subject varchar(255) NOT NULL,
message text NOT NULL
);
-- Table structure for table #__messages_cfg

CREATE TABLE IF NOT EXISTS #__messages_cfg (
user_id INTEGER PRIMARY KEY NOT NULL DEFAULT '0',
cfg_name varchar(100) NOT NULL,
cfg_value varchar(255) NOT NULL
);
-- Table structure for table #__modules

CREATE TABLE IF NOT EXISTS #__modules (
id INTEGER PRIMARY KEY AUTOINCREMENT,
title varchar(100) NOT NULL,
note varchar(255) NOT NULL,
content text NOT NULL,
ordering INTEGER NOT NULL DEFAULT '0',
position varchar(50) NOT NULL,
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
published tinyint(1) NOT NULL DEFAULT '0',
module varchar(50),
access INTEGER NOT NULL DEFAULT '0',
showtitle tinyint(3) NOT NULL DEFAULT '1',
params text NOT NULL,
client_id tinyint(4) NOT NULL DEFAULT '0',
language char(7) NOT NULL
);
-- Table structure for table #__modules_menu

CREATE TABLE IF NOT EXISTS #__modules_menu (
moduleid INTEGER PRIMARY KEY NOT NULL DEFAULT '0',
menuid INTEGER NOT NULL DEFAULT '0'
);
-- Table structure for table #__newsfeeds

CREATE TABLE IF NOT EXISTS #__newsfeeds (
catid INTEGER NOT NULL DEFAULT '0',
id INTEGER PRIMARY KEY AUTOINCREMENT,
name varchar(100) NOT NULL,
alias varchar(255) NOT NULL,
link varchar(200) NOT NULL,
filename varchar(200),
published tinyint(1) NOT NULL DEFAULT '0',
numarticles INTEGER NOT NULL DEFAULT '1',
cache_time INTEGER NOT NULL DEFAULT '3600',
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
ordering INTEGER NOT NULL DEFAULT '0',
rtl tinyint(4) NOT NULL DEFAULT '0',
access INTEGER NOT NULL DEFAULT '0',
language char(7) NOT NULL,
params text NOT NULL,
created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
created_by INTEGER NOT NULL DEFAULT '0',
created_by_alias varchar(255) NOT NULL,
modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
modified_by INTEGER NOT NULL DEFAULT '0',
metakey text NOT NULL,
metadesc text NOT NULL,
metadata text NOT NULL,
xreference varchar(50) NOT NULL,
publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
);
-- Table structure for table #__redirect_links

CREATE TABLE IF NOT EXISTS #__redirect_links (
id INTEGER PRIMARY KEY AUTOINCREMENT,
old_url varchar(255) NOT NULL,
new_url varchar(255) NOT NULL,
referer varchar(150) NOT NULL,
comment varchar(255) NOT NULL,
published tinyint(4) NOT NULL,
created_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
modified_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
);
-- Table structure for table #__schemas

CREATE TABLE IF NOT EXISTS #__schemas (
extension_id INTEGER PRIMARY KEY NOT NULL,
version_id varchar(20) NOT NULL
);
-- Table structure for table #__session

CREATE TABLE IF NOT EXISTS #__session (
session_id varchar(200) PRIMARY KEY NOT NULL,
client_id tinyint(3) NOT NULL DEFAULT '0',
guest tinyint(4) DEFAULT '1',
time varchar(14),
data mediumtext,
userid INTEGER DEFAULT '0',
username varchar(150),
usertype varchar(50)
);
-- Table structure for table #__template_styles

CREATE TABLE IF NOT EXISTS #__template_styles (
id INTEGER PRIMARY KEY AUTOINCREMENT,
template varchar(50) NOT NULL,
client_id tinyint(1) NOT NULL DEFAULT '0',
home char(7) NOT NULL DEFAULT '0',
title varchar(255) NOT NULL,
params text NOT NULL
);
-- Table structure for table #__update_categories

CREATE TABLE IF NOT EXISTS #__update_categories (
categoryid INTEGER PRIMARY KEY AUTOINCREMENT,
name varchar(20),
description text NOT NULL,
parent INTEGER DEFAULT '0',
updatesite INTEGER DEFAULT '0'
);
-- Table structure for table #__update_sites

CREATE TABLE IF NOT EXISTS #__update_sites (
update_site_id INTEGER PRIMARY KEY AUTOINCREMENT,
name varchar(100),
type varchar(20),
location text NOT NULL,
enabled INTEGER DEFAULT '0'
);
-- Table structure for table #__update_sites_extensions

CREATE TABLE IF NOT EXISTS #__update_sites_extensions (
update_site_id INTEGER PRIMARY KEY NOT NULL DEFAULT '0',
extension_id INTEGER NOT NULL DEFAULT '0'
);
-- Table structure for table #__updates

CREATE TABLE IF NOT EXISTS #__updates (
update_id INTEGER PRIMARY KEY AUTOINCREMENT,
update_site_id INTEGER DEFAULT '0',
extension_id INTEGER DEFAULT '0',
categoryid INTEGER DEFAULT '0',
name varchar(100),
description text NOT NULL,
element varchar(100),
type varchar(20),
folder varchar(20),
client_id tinyint(3) DEFAULT '0',
version varchar(10),
data text NOT NULL,
detailsurl text NOT NULL
);
-- Table structure for table #__user_profiles

CREATE TABLE IF NOT EXISTS #__user_profiles (
user_id INTEGER PRIMARY KEY NOT NULL,
profile_key varchar(100) NOT NULL,
profile_value varchar(255) NOT NULL,
ordering INTEGER NOT NULL DEFAULT '0'
);
-- Table structure for table #__user_usergroup_map

CREATE TABLE IF NOT EXISTS #__user_usergroup_map (
user_id INTEGER PRIMARY KEY NOT NULL DEFAULT '0',
group_id INTEGER NOT NULL DEFAULT '0'
);
-- Table structure for table #__usergroups

CREATE TABLE IF NOT EXISTS #__usergroups (
id INTEGER PRIMARY KEY AUTOINCREMENT,
parent_id INTEGER NOT NULL DEFAULT '0',
lft INTEGER NOT NULL DEFAULT '0',
rgt INTEGER NOT NULL DEFAULT '0',
title varchar(100) NOT NULL
);
-- Table structure for table #__users

CREATE TABLE IF NOT EXISTS #__users (
id INTEGER PRIMARY KEY AUTOINCREMENT,
name varchar(255) NOT NULL,
username varchar(150) NOT NULL,
email varchar(100) NOT NULL,
password varchar(100) NOT NULL,
usertype varchar(25) NOT NULL,
block tinyint(4) NOT NULL DEFAULT '0',
sendEmail tinyint(4) DEFAULT '0',
registerDate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
lastvisitDate datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
activation varchar(100) NOT NULL,
params text NOT NULL
);
-- Table structure for table #__viewlevels

CREATE TABLE IF NOT EXISTS #__viewlevels (
id INTEGER PRIMARY KEY AUTOINCREMENT,
title varchar(100) NOT NULL,
ordering INTEGER NOT NULL DEFAULT '0',
rules varchar(5120) NOT NULL
);
-- Table structure for table #__weblinks

CREATE TABLE IF NOT EXISTS #__weblinks (
id INTEGER PRIMARY KEY AUTOINCREMENT,
catid INTEGER NOT NULL DEFAULT '0',
sid INTEGER NOT NULL DEFAULT '0',
title varchar(250) NOT NULL,
alias varchar(255) NOT NULL,
url varchar(250) NOT NULL,
description text NOT NULL,
date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
hits INTEGER NOT NULL DEFAULT '0',
state tinyint(1) NOT NULL DEFAULT '0',
checked_out INTEGER NOT NULL DEFAULT '0',
checked_out_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
ordering INTEGER NOT NULL DEFAULT '0',
archived tinyint(1) NOT NULL DEFAULT '0',
approved tinyint(1) NOT NULL DEFAULT '1',
access INTEGER NOT NULL DEFAULT '1',
params text NOT NULL,
language char(7) NOT NULL,
created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
created_by INTEGER NOT NULL DEFAULT '0',
created_by_alias varchar(255) NOT NULL,
modified datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
modified_by INTEGER NOT NULL DEFAULT '0',
metakey text NOT NULL,
metadesc text NOT NULL,
metadata text NOT NULL,
featured tinyint(3) NOT NULL DEFAULT '0',
xreference varchar(50) NOT NULL,
publish_up datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
publish_down datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
);
-- Table data for table #__assets

INSERT INTO #__assets
      SELECT '1' AS id, '0' AS parent_id, '1' AS lft, '414' AS rgt, '0' AS level, 'root.1' AS name, 'Root Asset' AS title, '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}' AS rules
UNION SELECT '2', '1', '1', '2', '1', 'com_admin', 'com_admin', '{}'
UNION SELECT '3', '1', '3', '6', '1', 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION SELECT '4', '1', '7', '8', '1', 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION SELECT '5', '1', '9', '10', '1', 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION SELECT '6', '1', '11', '12', '1', 'com_config', 'com_config', '{}'
UNION SELECT '7', '1', '13', '16', '1', 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION SELECT '8', '1', '17', '20', '1', 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'
UNION SELECT '9', '1', '21', '22', '1', 'com_cpanel', 'com_cpanel', '{}'
UNION SELECT '10', '1', '23', '24', '1', 'com_installer', 'com_installer', '{"core.admin":{"7":1},"core.manage":{"7":1},"core.delete":[],"core.edit.state":[]}'
UNION SELECT '11', '1', '25', '26', '1', 'com_languages', 'com_languages', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION SELECT '12', '1', '27', '28', '1', 'com_login', 'com_login', '{}'
UNION SELECT '13', '1', '29', '30', '1', 'com_mailto', 'com_mailto', '{}'
UNION SELECT '14', '1', '31', '32', '1', 'com_massmail', 'com_massmail', '{}'
UNION SELECT '15', '1', '33', '34', '1', 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'
UNION SELECT '16', '1', '35', '36', '1', 'com_menus', 'com_menus', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION SELECT '17', '1', '37', '38', '1', 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
UNION SELECT '18', '1', '39', '40', '1', 'com_modules', 'com_modules', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION SELECT '19', '1', '41', '44', '1', 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION SELECT '20', '1', '45', '46', '1', 'com_plugins', 'com_plugins', '{"core.admin":{"7":1},"core.manage":[],"core.edit":[],"core.edit.state":[]}'
UNION SELECT '21', '1', '47', '48', '1', 'com_redirect', 'com_redirect', '{"core.admin":{"7":1},"core.manage":[]}'
UNION SELECT '22', '1', '49', '50', '1', 'com_search', 'com_search', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
UNION SELECT '23', '1', '51', '52', '1', 'com_templates', 'com_templates', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION SELECT '24', '1', '53', '54', '1', 'com_users', 'com_users', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.own":{"6":1},"core.edit.state":[]}'
UNION SELECT '25', '1', '55', '58', '1', 'com_weblinks', 'com_weblinks', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'
UNION SELECT '26', '1', '59', '60', '1', 'com_wrapper', 'com_wrapper', '{}'
UNION SELECT '27', '8', '18', '19', '2', 'com_content.category.2', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION SELECT '28', '3', '4', '5', '2', 'com_banners.category.3', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
UNION SELECT '29', '7', '14', '15', '2', 'com_contact.category.4', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION SELECT '30', '19', '42', '43', '2', 'com_newsfeeds.category.5', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
UNION SELECT '31', '25', '56', '57', '2', 'com_weblinks.category.6', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
;
-- Table data for table #__categories

INSERT INTO #__categories
      SELECT '1' AS id, '0' AS asset_id, '0' AS parent_id, '0' AS lft, '11' AS rgt, '0' AS level, '' AS path, 'system' AS extension, 'ROOT' AS title, 'root' AS alias, '' AS note, '' AS description, '1' AS published, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '1' AS access, '{}' AS params, '' AS metadesc, '' AS metakey, '' AS metadata, '0' AS created_user_id, '2009-10-18 16:07:09' AS created_time, '0' AS modified_user_id, '0000-00-00 00:00:00' AS modified_time, '0' AS hits, '*' AS language
UNION SELECT '2', '27', '1', '1', '2', '1', 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:26:37', '0', '0000-00-00 00:00:00', '0', '*'
UNION SELECT '3', '28', '1', '3', '4', '1', 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"target":"","image":"","foobar":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:27:35', '0', '0000-00-00 00:00:00', '0', '*'
UNION SELECT '4', '29', '1', '5', '6', '1', 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:27:57', '0', '0000-00-00 00:00:00', '0', '*'
UNION SELECT '5', '30', '1', '7', '8', '1', 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:28:15', '0', '0000-00-00 00:00:00', '0', '*'
UNION SELECT '6', '31', '1', '9', '10', '1', 'uncategorised', 'com_weblinks', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"target":"","image":""}', '', '', '{"page_title":"","author":"","robots":""}', '42', '2010-06-28 13:28:33', '0', '0000-00-00 00:00:00', '0', '*'
;
-- Table data for table #__extensions

INSERT INTO #__extensions
      SELECT '1' AS extension_id, 'com_mailto' AS name, 'component' AS type, 'com_mailto' AS element, '' AS folder, '0' AS client_id, '1' AS enabled, '1' AS access, '1' AS protected, '{"legacy":false,"name":"com_mailto","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MAILTO_XML_DESCRIPTION","group":""}' AS manifest_cache, '' AS params, '' AS custom_data, '' AS system_data, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '0' AS ordering, '0' AS state
UNION SELECT '2', 'com_wrapper', 'component', 'com_wrapper', '', '0', '1', '1', '1', '{"legacy":false,"name":"com_wrapper","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_WRAPPER_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '3', 'com_admin', 'component', 'com_admin', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_admin","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_ADMIN_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '4', 'com_banners', 'component', 'com_banners', '', '1', '1', '1', '0', '{"legacy":false,"name":"com_banners","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_BANNERS_XML_DESCRIPTION","group":""}', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":""}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '5', 'com_cache', 'component', 'com_cache', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_cache","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CACHE_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '6', 'com_categories', 'component', 'com_categories', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_categories","type":"component","creationDate":"December 2007","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CATEGORIES_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '7', 'com_checkin', 'component', 'com_checkin', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_checkin","type":"component","creationDate":"Unknown","author":"Joomla! Project","copyright":"(C) 2005 - 2008 Open Source Matters. All rights reserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CHECKIN_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '8', 'com_contact', 'component', 'com_contact', '', '1', '1', '1', '0', '{"legacy":false,"name":"com_contact","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CONTACT_XML_DESCRIPTION","group":""}', '{"show_contact_category":"hide","show_contact_list":"0","presentation_style":"sliders","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_misc":"1","show_image":"1","image":"","allow_vcard":"0","show_articles":"0","show_profile":"0","show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","show_headings":"1","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","allow_vcard_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_email_form":"1","show_email_copy":"1","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_category_crumb":"0","metakey":"","metadesc":"","robots":"","author":"","rights":"","xreference":""}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '9', 'com_cpanel', 'component', 'com_cpanel', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_cpanel","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CPANEL_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '10', 'com_installer', 'component', 'com_installer', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_installer","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_INSTALLER_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '11', 'com_languages', 'component', 'com_languages', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_languages","type":"component","creationDate":"2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_LANGUAGES_XML_DESCRIPTION","group":""}', '{"administrator":"en-GB","site":"en-GB"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '12', 'com_login', 'component', 'com_login', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_login","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_LOGIN_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '13', 'com_media', 'component', 'com_media', '', '1', '1', '0', '1', '{"legacy":false,"name":"com_media","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MEDIA_XML_DESCRIPTION","group":""}', '{"upload_extensions":"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS","upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","check_mime":"1","image_extensions":"bmp,gif,jpg,png","ignore_extensions":"","upload_mime":"image\/jpeg,image\/gif,image\/png,image\/bmp,application\/x-shockwave-flash,application\/msword,application\/excel,application\/pdf,application\/powerpoint,text\/plain,application\/x-zip","upload_mime_illegal":"text\/html","enable_flash":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '14', 'com_menus', 'component', 'com_menus', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_menus","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MENUS_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '15', 'com_messages', 'component', 'com_messages', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_messages","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MESSAGES_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '16', 'com_modules', 'component', 'com_modules', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_modules","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_MODULES_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '17', 'com_newsfeeds', 'component', 'com_newsfeeds', '', '1', '1', '1', '0', '{"legacy":false,"name":"com_newsfeeds","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_NEWSFEEDS_XML_DESCRIPTION","group":""}', '{"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_word_count":"0","show_headings":"1","show_name":"1","show_articles":"0","show_link":"1","show_description":"1","show_description_image":"1","display_num":"","show_pagination_limit":"1","show_pagination":"1","show_pagination_results":"1","show_cat_items":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '18', 'com_plugins', 'component', 'com_plugins', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_plugins","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_PLUGINS_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '19', 'com_search', 'component', 'com_search', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_search","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_SEARCH_XML_DESCRIPTION","group":""}', '{"enabled":"0","show_date":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '20', 'com_templates', 'component', 'com_templates', '', '1', '1', '1', '1', '{"legacy":false,"name":"com_templates","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_TEMPLATES_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '21', 'com_weblinks', 'component', 'com_weblinks', '', '1', '1', '1', '0', '{"legacy":false,"name":"com_weblinks","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\n\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_WEBLINKS_XML_DESCRIPTION","group":""}', '{"show_comp_description":"1","comp_description":"","show_link_hits":"1","show_link_description":"1","show_other_cats":"0","show_headings":"0","show_numbers":"0","show_report":"1","count_clicks":"1","target":"0","link_icons":""}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '22', 'com_content', 'component', 'com_content', '', '1', '1', '0', '1', '{"legacy":false,"name":"com_content","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CONTENT_XML_DESCRIPTION","group":""}', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","show_category":"1","link_category":"1","show_parent_category":"0","link_parent_category":"0","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","category_layout":"_:blog","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","multi_column_order":"0","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0","filters":{"1":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"2":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"10":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"12":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"BL","filter_tags":"","filter_attributes":""}}}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '23', 'com_config', 'component', 'com_config', '', '1', '1', '0', '1', '{"legacy":false,"name":"com_config","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_CONFIG_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '24', 'com_redirect', 'component', 'com_redirect', '', '1', '1', '0', '1', '{"legacy":false,"name":"com_redirect","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_REDIRECT_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '25', 'com_users', 'component', 'com_users', '', '1', '1', '0', '1', '{"legacy":false,"name":"com_users","type":"component","creationDate":"April 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.\t","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"COM_USERS_XML_DESCRIPTION","group":""}', '{"allowUserRegistration":"1","new_usertype":"2","useractivation":"1","frontend_userparams":"1","mailSubjectPrefix":"","mailBodySuffix":""}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '100', 'PHPMailer', 'library', 'phpmailer', '', '0', '1', '1', '1', '{"legacy":false,"name":"PHPMailer","type":"library","creationDate":"2008","author":"PHPMailer","copyright":"Copyright (C) PHPMailer.","authorEmail":"","authorUrl":"http:\/\/phpmailer.codeworxtech.com\/","version":"1.7.0","description":"LIB_PHPMAILER_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '101', 'SimplePie', 'library', 'simplepie', '', '0', '1', '1', '1', '{"legacy":false,"name":"SimplePie","type":"library","creationDate":"2008","author":"SimplePie","copyright":"Copyright (C) 2008 SimplePie","authorEmail":"","authorUrl":"http:\/\/simplepie.org\/","version":"1.0.1","description":"LIB_SIMPLEPIE_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '102', 'phputf8', 'library', 'phputf8', '', '0', '1', '1', '1', '{"legacy":false,"name":"phputf8","type":"library","creationDate":"2008","author":"Harry Fuecks","copyright":"Copyright various authors","authorEmail":"","authorUrl":"http:\/\/sourceforge.net\/projects\/phputf8","version":"1.7.0","description":"LIB_PHPUTF8_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '103', 'Joomla! Web Application Framework', 'library', 'joomla', '', '0', '1', '1', '1', '{"legacy":false,"name":"Joomla! Web Application Framework","type":"library","creationDate":"2008","author":"Joomla","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"http:\/\/www.joomla.org","version":"1.7.0","description":"LIB_JOOMLA_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '200', 'mod_articles_archive', 'module', 'mod_articles_archive', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_articles_archive","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters.\n\t\tAll rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_ARTICLES_ARCHIVE_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '201', 'mod_articles_latest', 'module', 'mod_articles_latest', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_articles_latest","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LATEST_NEWS_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '202', 'mod_articles_popular', 'module', 'mod_articles_popular', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_articles_popular","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_POPULAR_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '203', 'mod_banners', 'module', 'mod_banners', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_banners","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_BANNERS_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '204', 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_breadcrumbs","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_BREADCRUMBS_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '205', 'mod_custom', 'module', 'mod_custom', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_custom","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_CUSTOM_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '206', 'mod_feed', 'module', 'mod_feed', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_feed","type":"module","creationDate":"July 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_FEED_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '207', 'mod_footer', 'module', 'mod_footer', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_footer","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_FOOTER_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '208', 'mod_login', 'module', 'mod_login', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_login","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LOGIN_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '209', 'mod_menu', 'module', 'mod_menu', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_menu","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_MENU_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '210', 'mod_articles_news', 'module', 'mod_articles_news', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_articles_news","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_ARTICLES_NEWS_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '211', 'mod_random_image', 'module', 'mod_random_image', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_random_image","type":"module","creationDate":"July 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_RANDOM_IMAGE_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '212', 'mod_related_items', 'module', 'mod_related_items', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_related_items","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_RELATED_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '213', 'mod_search', 'module', 'mod_search', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_search","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_SEARCH_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '214', 'mod_stats', 'module', 'mod_stats', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_stats","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_STATS_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '215', 'mod_syndicate', 'module', 'mod_syndicate', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_syndicate","type":"module","creationDate":"May 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_SYNDICATE_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '216', 'mod_users_latest', 'module', 'mod_users_latest', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_users_latest","type":"module","creationDate":"December 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_USERS_LATEST_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '217', 'mod_weblinks', 'module', 'mod_weblinks', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_weblinks","type":"module","creationDate":"July 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_WEBLINKS_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '218', 'mod_whosonline', 'module', 'mod_whosonline', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_whosonline","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_WHOSONLINE_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '219', 'mod_wrapper', 'module', 'mod_wrapper', '', '0', '1', '1', '0', '{"legacy":false,"name":"mod_wrapper","type":"module","creationDate":"October 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_WRAPPER_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '220', 'mod_articles_category', 'module', 'mod_articles_category', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_articles_category","type":"module","creationDate":"February 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_ARTICLES_CATEGORY_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '221', 'mod_articles_categories', 'module', 'mod_articles_categories', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_articles_categories","type":"module","creationDate":"February 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_ARTICLES_CATEGORIES_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '222', 'mod_languages', 'module', 'mod_languages', '', '0', '1', '1', '1', '{"legacy":false,"name":"mod_languages","type":"module","creationDate":"February 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LANGUAGES_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '300', 'mod_custom', 'module', 'mod_custom', '', '1', '1', '1', '1', '{"legacy":false,"name":"mod_custom","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_CUSTOM_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '301', 'mod_feed', 'module', 'mod_feed', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_feed","type":"module","creationDate":"July 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_FEED_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '302', 'mod_latest', 'module', 'mod_latest', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_latest","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LATEST_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '303', 'mod_logged', 'module', 'mod_logged', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_logged","type":"module","creationDate":"January 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LOGGED_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '304', 'mod_login', 'module', 'mod_login', '', '1', '1', '1', '1', '{"legacy":false,"name":"mod_login","type":"module","creationDate":"March 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_LOGIN_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '305', 'mod_menu', 'module', 'mod_menu', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_menu","type":"module","creationDate":"March 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_MENU_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '307', 'mod_popular', 'module', 'mod_popular', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_popular","type":"module","creationDate":"July 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_POPULAR_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '308', 'mod_quickicon', 'module', 'mod_quickicon', '', '1', '1', '1', '1', '{"legacy":false,"name":"mod_quickicon","type":"module","creationDate":"Nov 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_QUICKICON_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '309', 'mod_status', 'module', 'mod_status', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_status","type":"module","creationDate":"Feb 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_STATUS_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '310', 'mod_submenu', 'module', 'mod_submenu', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_submenu","type":"module","creationDate":"Feb 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_SUBMENU_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '311', 'mod_title', 'module', 'mod_title', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_title","type":"module","creationDate":"Nov 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_TITLE_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '312', 'mod_toolbar', 'module', 'mod_toolbar', '', '1', '1', '1', '1', '{"legacy":false,"name":"mod_toolbar","type":"module","creationDate":"Nov 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"MOD_TOOLBAR_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '313', 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', '1', '1', '1', '0', '{"legacy":false,"name":"mod_multilangstatus","type":"module","creationDate":"September 2011","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"MOD_MULTILANGSTATUS_XML_DESCRIPTION","group":""}', '{"cache":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '400', 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', '0', '0', '1', '0', '{"legacy":false,"name":"plg_authentication_gmail","type":"plugin","creationDate":"February 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_GMAIL_XML_DESCRIPTION","group":""}', '{"applysuffix":"0","suffix":"","verifypeer":"1","user_blacklist":""}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
UNION SELECT '401', 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', '0', '1', '1', '1', '{"legacy":false,"name":"plg_authentication_joomla","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_AUTH_JOOMLA_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '402', 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', '0', '0', '1', '0', '{"legacy":false,"name":"plg_authentication_ldap","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_LDAP_XML_DESCRIPTION","group":""}', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', '0', '0000-00-00 00:00:00', '3', '0'
UNION SELECT '404', 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', '0', '1', '1', '0', '{"legacy":false,"name":"plg_content_emailcloak","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTENT_EMAILCLOAK_XML_DESCRIPTION","group":""}', '{"mode":"1"}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
UNION SELECT '405', 'plg_content_geshi', 'plugin', 'geshi', 'content', '0', '0', '1', '0', '{"legacy":false,"name":"plg_content_geshi","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"","authorUrl":"qbnz.com\/highlighter","version":"1.7.0","description":"PLG_CONTENT_GESHI_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '2', '0'
UNION SELECT '406', 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', '0', '1', '1', '0', '{"legacy":false,"name":"plg_content_loadmodule","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_LOADMODULE_XML_DESCRIPTION","group":""}', '{"style":"xhtml"}', '', '', '0', '2011-09-18 15:22:50', '0', '0'
UNION SELECT '407', 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', '0', '1', '1', '1', '{"legacy":false,"name":"plg_content_pagebreak","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTENT_PAGEBREAK_XML_DESCRIPTION","group":""}', '{"title":"1","multipage_toc":"1","showall":"1"}', '', '', '0', '0000-00-00 00:00:00', '4', '0'
UNION SELECT '408', 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', '0', '1', '1', '1', '{"legacy":false,"name":"plg_content_pagenavigation","type":"plugin","creationDate":"January 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_PAGENAVIGATION_XML_DESCRIPTION","group":""}', '{"position":"1"}', '', '', '0', '0000-00-00 00:00:00', '5', '0'
UNION SELECT '409', 'plg_content_vote', 'plugin', 'vote', 'content', '0', '1', '1', '1', '{"legacy":false,"name":"plg_content_vote","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_VOTE_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '6', '0'
UNION SELECT '410', 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', '0', '1', '1', '1', '{"legacy":false,"name":"plg_editors_codemirror","type":"plugin","creationDate":"28 March 2011","author":"Marijn Haverbeke","copyright":"","authorEmail":"N\/A","authorUrl":"","version":"1.0","description":"PLG_CODEMIRROR_XML_DESCRIPTION","group":""}', '{"linenumbers":"0","tabmode":"indent"}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
UNION SELECT '411', 'plg_editors_none', 'plugin', 'none', 'editors', '0', '1', '1', '1', '{"legacy":false,"name":"plg_editors_none","type":"plugin","creationDate":"August 2004","author":"Unknown","copyright":"","authorEmail":"N\/A","authorUrl":"","version":"1.7.0","description":"PLG_NONE_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '2', '0'
UNION SELECT '412', 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', '0', '1', '1', '1', '{"legacy":false,"name":"plg_editors_tinymce","type":"plugin","creationDate":"2005-2011","author":"Moxiecode Systems AB","copyright":"Moxiecode Systems AB","authorEmail":"N\/A","authorUrl":"tinymce.moxiecode.com\/","version":"3.4.7","description":"PLG_TINY_XML_DESCRIPTION","group":""}', '{"mode":"1","skin":"0","compressed":"0","cleanup_startup":"0","cleanup_save":"2","entity_encoding":"raw","lang_mode":"0","lang_code":"en","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","invalid_elements":"script,applet,iframe","extended_elements":"","toolbar":"top","toolbar_align":"left","html_height":"550","html_width":"750","element_path":"1","fonts":"1","paste":"1","searchreplace":"1","insertdate":"1","format_date":"%Y-%m-%d","inserttime":"1","format_time":"%H:%M:%S","colors":"1","table":"1","smilies":"1","media":"1","hr":"1","directionality":"1","fullscreen":"1","style":"1","layer":"1","xhtmlxtras":"1","visualchars":"1","nonbreaking":"1","template":"1","blockquote":"1","wordcount":"1","advimage":"1","advlink":"1","autosave":"1","contextmenu":"1","inlinepopups":"1","safari":"0","custom_plugin":"","custom_button":""}', '', '', '0', '0000-00-00 00:00:00', '3', '0'
UNION SELECT '413', 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', '0', '1', '1', '1', '{"legacy":false,"name":"plg_editors-xtd_article","type":"plugin","creationDate":"October 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_ARTICLE_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
UNION SELECT '414', 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', '0', '1', '1', '0', '{"legacy":false,"name":"plg_editors-xtd_image","type":"plugin","creationDate":"August 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_IMAGE_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '2', '0'
UNION SELECT '415', 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', '0', '1', '1', '0', '{"legacy":false,"name":"plg_editors-xtd_pagebreak","type":"plugin","creationDate":"August 2004","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_EDITORSXTD_PAGEBREAK_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '3', '0'
UNION SELECT '416', 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', '0', '1', '1', '0', '{"legacy":false,"name":"plg_editors-xtd_readmore","type":"plugin","creationDate":"March 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_READMORE_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '4', '0'
UNION SELECT '417', 'plg_search_categories', 'plugin', 'categories', 'search', '0', '1', '1', '0', '{"legacy":false,"name":"plg_search_categories","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_CATEGORIES_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '418', 'plg_search_contacts', 'plugin', 'contacts', 'search', '0', '1', '1', '0', '{"legacy":false,"name":"plg_search_contacts","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_CONTACTS_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '419', 'plg_search_content', 'plugin', 'content', 'search', '0', '1', '1', '0', '{"legacy":false,"name":"plg_search_content","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_CONTENT_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '420', 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', '0', '1', '1', '0', '{"legacy":false,"name":"plg_search_newsfeeds","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_NEWSFEEDS_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '421', 'plg_search_weblinks', 'plugin', 'weblinks', 'search', '0', '1', '1', '0', '{"legacy":false,"name":"plg_search_weblinks","type":"plugin","creationDate":"November 2005","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEARCH_WEBLINKS_XML_DESCRIPTION","group":""}', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '422', 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', '0', '0', '1', '1', '{"legacy":false,"name":"plg_system_languagefilter","type":"plugin","creationDate":"July 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SYSTEM_LANGUAGEFILTER_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
UNION SELECT '423', 'plg_system_p3p', 'plugin', 'p3p', 'system', '0', '1', '1', '1', '{"legacy":false,"name":"plg_system_p3p","type":"plugin","creationDate":"September 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_P3P_XML_DESCRIPTION","group":""}', '{"headers":"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"}', '', '', '0', '0000-00-00 00:00:00', '2', '0'
UNION SELECT '424', 'plg_system_cache', 'plugin', 'cache', 'system', '0', '0', '1', '1', '{"legacy":false,"name":"plg_system_cache","type":"plugin","creationDate":"February 2007","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CACHE_XML_DESCRIPTION","group":""}', '{"browsercache":"0","cachetime":"15"}', '', '', '0', '0000-00-00 00:00:00', '9', '0'
UNION SELECT '425', 'plg_system_debug', 'plugin', 'debug', 'system', '0', '1', '1', '0', '{"legacy":false,"name":"plg_system_debug","type":"plugin","creationDate":"December 2006","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_DEBUG_XML_DESCRIPTION","group":""}', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', '', '0', '0000-00-00 00:00:00', '4', '0'
UNION SELECT '426', 'plg_system_log', 'plugin', 'log', 'system', '0', '1', '1', '1', '{"legacy":false,"name":"plg_system_log","type":"plugin","creationDate":"April 2007","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_LOG_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '5', '0'
UNION SELECT '427', 'plg_system_redirect', 'plugin', 'redirect', 'system', '0', '1', '1', '1', '{"legacy":false,"name":"plg_system_redirect","type":"plugin","creationDate":"April 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_REDIRECT_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '6', '0'
UNION SELECT '428', 'plg_system_remember', 'plugin', 'remember', 'system', '0', '1', '1', '1', '{"legacy":false,"name":"plg_system_remember","type":"plugin","creationDate":"April 2007","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_REMEMBER_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '7', '0'
UNION SELECT '429', 'plg_system_sef', 'plugin', 'sef', 'system', '0', '1', '1', '0', '{"legacy":false,"name":"plg_system_sef","type":"plugin","creationDate":"December 2007","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SEF_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '8', '0'
UNION SELECT '430', 'plg_system_logout', 'plugin', 'logout', 'system', '0', '1', '1', '1', '{"legacy":false,"name":"plg_system_logout","type":"plugin","creationDate":"April 2009","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_SYSTEM_LOGOUT_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '3', '0'
UNION SELECT '431', 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', '0', '0', '1', '1', '{"legacy":false,"name":"plg_user_contactcreator","type":"plugin","creationDate":"August 2009","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTACTCREATOR_XML_DESCRIPTION","group":""}', '{"autowebpage":"","category":"34","autopublish":"0"}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
UNION SELECT '432', 'plg_user_joomla', 'plugin', 'joomla', 'user', '0', '1', '1', '0', '{"legacy":false,"name":"plg_user_joomla","type":"plugin","creationDate":"December 2006","author":"Joomla! Project","copyright":"(C) 2005 - 2009 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_USER_JOOMLA_XML_DESCRIPTION","group":""}', '{"autoregister":"1"}', '', '', '0', '0000-00-00 00:00:00', '2', '0'
UNION SELECT '433', 'plg_user_profile', 'plugin', 'profile', 'user', '0', '0', '1', '1', '{"legacy":false,"name":"plg_user_profile","type":"plugin","creationDate":"January 2008","author":"Joomla! Project","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_USER_PROFILE_XML_DESCRIPTION","group":""}', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '434', 'plg_extension_joomla', 'plugin', 'joomla', 'extension', '0', '1', '1', '1', '{"legacy":false,"name":"plg_extension_joomla","type":"plugin","creationDate":"May 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_EXTENSION_JOOMLA_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
UNION SELECT '435', 'plg_content_joomla', 'plugin', 'joomla', 'content', '0', '1', '1', '0', '{"legacy":false,"name":"plg_content_joomla","type":"plugin","creationDate":"November 2010","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"PLG_CONTENT_JOOMLA_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '500', 'atomic', 'template', 'atomic', '', '0', '1', '1', '0', '{"legacy":false,"name":"atomic","type":"template","creationDate":"10\/10\/09","author":"Ron Severdia","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"contact@kontentdesign.com","authorUrl":"http:\/\/www.kontentdesign.com","version":"1.7.0","description":"TPL_ATOMIC_XML_DESCRIPTION","group":""}', '{}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '502', 'bluestork', 'template', 'bluestork', '', '1', '1', '1', '0', '{"legacy":false,"name":"bluestork","type":"template","creationDate":"07\/02\/09","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.0","description":"TPL_BLUESTORK_XML_DESCRIPTION","group":""}', '{"useRoundedCorners":"1","showSiteName":"0","textBig":"0","highContrast":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '503', 'beez_20', 'template', 'beez_20', '', '0', '1', '1', '0', '{"legacy":false,"name":"beez_20","type":"template","creationDate":"25 November 2009","author":"Angie Radtke","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"a.radtke@derauftritt.de","authorUrl":"http:\/\/www.der-auftritt.de","version":"1.7.0","description":"TPL_BEEZ2_XML_DESCRIPTION","group":""}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '504', 'hathor', 'template', 'hathor', '', '1', '1', '1', '0', '{"legacy":false,"name":"hathor","type":"template","creationDate":"May 2010","author":"Andrea Tarr","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"hathor@tarrconsulting.com","authorUrl":"http:\/\/www.tarrconsulting.com","version":"1.7.0","description":"TPL_HATHOR_XML_DESCRIPTION","group":""}', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '505', 'beez5', 'template', 'beez5', '', '0', '1', '1', '0', '{"legacy":false,"name":"beez5","type":"template","creationDate":"21 May 2010","author":"Angie Radtke","copyright":"Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.","authorEmail":"a.radtke@derauftritt.de","authorUrl":"http:\/\/www.der-auftritt.de","version":"1.7.0","description":"TPL_BEEZ5_XML_DESCRIPTION","group":""}', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","html5":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '600', 'English (United Kingdom)', 'language', 'en-GB', '', '0', '1', '1', '1', '{"legacy":false,"name":"English (United Kingdom)","type":"language","creationDate":"2008-03-15","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"en-GB site language","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '601', 'English (United Kingdom)', 'language', 'en-GB', '', '1', '1', '1', '1', '{"legacy":false,"name":"English (United Kingdom)","type":"language","creationDate":"2008-03-15","author":"Joomla! Project","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.1","description":"en-GB administrator language","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '700', 'files_joomla', 'file', 'joomla', '', '0', '1', '1', '1', '{"legacy":false,"name":"files_joomla","type":"file","creationDate":"November 2011","author":"Joomla!","copyright":"(C) 2005 - 2011 Open Source Matters. All rights reserved","authorEmail":"admin@joomla.org","authorUrl":"www.joomla.org","version":"1.7.3","description":"FILES_JOOMLA_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
UNION SELECT '800', 'PKG_JOOMLA', 'package', 'pkg_joomla', '', '0', '1', '1', '1', '{"legacy":false,"name":"PKG_JOOMLA","type":"package","creationDate":"2006","author":"Joomla!","copyright":"Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.","authorEmail":"admin@joomla.org","authorUrl":"http:\/\/www.joomla.org","version":"1.7.0","description":"PKG_JOOMLA_XML_DESCRIPTION","group":""}', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
;
-- Table data for table #__languages

INSERT INTO #__languages
      SELECT '1' AS lang_id, 'en-GB' AS lang_code, 'English (UK)' AS title, 'English (UK)' AS title_native, 'en' AS sef, 'en' AS image, '' AS description, '' AS metakey, '' AS metadesc, '' AS sitename, '1' AS published, '1' AS ordering
;
-- Table data for table #__menu

INSERT INTO #__menu
      SELECT '1' AS id, '' AS menutype, 'Menu_Item_Root' AS title, 'root' AS alias, '' AS note, '' AS path, '' AS link, '' AS type, '1' AS published, '0' AS parent_id, '0' AS level, '0' AS component_id, '0' AS ordering, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '0' AS browserNav, '0' AS access, '' AS img, '0' AS template_style_id, '' AS params, '0' AS lft, '41' AS rgt, '0' AS home, '*' AS language, '0' AS client_id
UNION SELECT '2', 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', '0', '1', '1', '4', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners', '0', '', '1', '10', '0', '*', '1'
UNION SELECT '3', 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', '0', '2', '2', '4', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners', '0', '', '2', '3', '0', '*', '1'
UNION SELECT '4', 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', '0', '2', '2', '6', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners-cat', '0', '', '4', '5', '0', '*', '1'
UNION SELECT '5', 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', '0', '2', '2', '4', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners-clients', '0', '', '6', '7', '0', '*', '1'
UNION SELECT '6', 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', '0', '2', '2', '4', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners-tracks', '0', '', '8', '9', '0', '*', '1'
UNION SELECT '7', 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', '0', '1', '1', '8', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:contact', '0', '', '11', '16', '0', '*', '1'
UNION SELECT '8', 'menu', 'com_contact', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', '0', '7', '2', '8', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:contact', '0', '', '12', '13', '0', '*', '1'
UNION SELECT '9', 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', '0', '7', '2', '6', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:contact-cat', '0', '', '14', '15', '0', '*', '1'
UNION SELECT '10', 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', '0', '1', '1', '15', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:messages', '0', '', '17', '22', '0', '*', '1'
UNION SELECT '11', 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', '0', '10', '2', '15', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:messages-add', '0', '', '18', '19', '0', '*', '1'
UNION SELECT '12', 'menu', 'com_messages_read', 'Read Private Message', '', 'Messaging/Read Private Message', 'index.php?option=com_messages', 'component', '0', '10', '2', '15', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:messages-read', '0', '', '20', '21', '0', '*', '1'
UNION SELECT '13', 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', '0', '1', '1', '17', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:newsfeeds', '0', '', '23', '28', '0', '*', '1'
UNION SELECT '14', 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', '0', '13', '2', '17', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:newsfeeds', '0', '', '24', '25', '0', '*', '1'
UNION SELECT '15', 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', '0', '13', '2', '6', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:newsfeeds-cat', '0', '', '26', '27', '0', '*', '1'
UNION SELECT '16', 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', '0', '1', '1', '24', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:redirect', '0', '', '37', '38', '0', '*', '1'
UNION SELECT '17', 'menu', 'com_search', 'Search', '', 'Search', 'index.php?option=com_search', 'component', '0', '1', '1', '19', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:search', '0', '', '29', '30', '0', '*', '1'
UNION SELECT '18', 'menu', 'com_weblinks', 'Weblinks', '', 'Weblinks', 'index.php?option=com_weblinks', 'component', '0', '1', '1', '21', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:weblinks', '0', '', '31', '36', '0', '*', '1'
UNION SELECT '19', 'menu', 'com_weblinks_links', 'Links', '', 'Weblinks/Links', 'index.php?option=com_weblinks', 'component', '0', '18', '2', '21', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:weblinks', '0', '', '32', '33', '0', '*', '1'
UNION SELECT '20', 'menu', 'com_weblinks_categories', 'Categories', '', 'Weblinks/Categories', 'index.php?option=com_categories&extension=com_weblinks', 'component', '0', '18', '2', '6', '0', '0', '0000-00-00 00:00:00', '0', '0', 'class:weblinks-cat', '0', '', '34', '35', '0', '*', '1'
UNION SELECT '101', 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', '1', '1', '1', '22', '0', '0', '0000-00-00 00:00:00', '0', '1', '', '0', '{"featured_categories":[""],"num_leading_articles":"1","num_intro_articles":"3","num_columns":"3","num_links":"0","orderby_pri":"","orderby_sec":"front","order_date":"","multi_column_order":"1","show_pagination":"2","show_pagination_results":"1","show_noauth":"","article-allow_ratings":"","article-allow_comments":"","show_feed_link":"1","feed_summary":"","show_title":"","link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_readmore":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","show_page_heading":1,"page_title":"","page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', '39', '40', '1', '*', '0'
;
-- Table data for table #__menu_types

INSERT INTO #__menu_types
      SELECT '1' AS id, 'mainmenu' AS menutype, 'Main Menu' AS title, 'The main menu for the site' AS description
;
-- Table data for table #__modules

INSERT INTO #__modules
      SELECT '1' AS id, 'Main Menu' AS title, '' AS note, '' AS content, '1' AS ordering, 'position-7' AS position, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '0000-00-00 00:00:00' AS publish_up, '0000-00-00 00:00:00' AS publish_down, '1' AS published, 'mod_menu' AS module, '1' AS access, '1' AS showtitle, '{"menutype":"mainmenu","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}' AS params, '0' AS client_id, '*' AS language
UNION SELECT '2', 'Login', '', '', '1', 'login', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_login', '1', '1', '', '1', '*'
UNION SELECT '3', 'Popular Articles', '', '', '3', 'cpanel', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_popular', '3', '1', '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', '1', '*'
UNION SELECT '4', 'Recently Added Articles', '', '', '4', 'cpanel', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_latest', '3', '1', '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', '1', '*'
UNION SELECT '8', 'Toolbar', '', '', '1', 'toolbar', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_toolbar', '3', '1', '', '1', '*'
UNION SELECT '9', 'Quick Icons', '', '', '1', 'icon', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_quickicon', '3', '1', '', '1', '*'
UNION SELECT '10', 'Logged-in Users', '', '', '2', 'cpanel', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_logged', '3', '1', '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', '1', '*'
UNION SELECT '12', 'Admin Menu', '', '', '1', 'menu', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_menu', '3', '1', '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', '1', '*'
UNION SELECT '13', 'Admin Submenu', '', '', '1', 'submenu', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_submenu', '3', '1', '', '1', '*'
UNION SELECT '14', 'User Status', '', '', '2', 'status', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_status', '3', '1', '', '1', '*'
UNION SELECT '15', 'Title', '', '', '1', 'title', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_title', '3', '1', '', '1', '*'
UNION SELECT '16', 'Login Form', '', '', '7', 'position-7', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_login', '1', '1', '{"greeting":"1","name":"0"}', '0', '*'
UNION SELECT '17', 'Breadcrumbs', '', '', '1', 'position-2', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_breadcrumbs', '1', '1', '{"moduleclass_sfx":"","showHome":"1","homeText":"Home","showComponent":"1","separator":"","cache":"1","cache_time":"900","cachemode":"itemid"}', '0', '*'
UNION SELECT '79', 'Multilanguage status', '', '', '1', 'status', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0', 'mod_multilangstatus', '3', '1', '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', '1', '*'
;
-- Table data for table #__modules_menu

INSERT INTO #__modules_menu
      SELECT '1' AS moduleid, '0' AS menuid
UNION SELECT '2', '0'
UNION SELECT '3', '0'
UNION SELECT '4', '0'
UNION SELECT '6', '0'
UNION SELECT '7', '0'
UNION SELECT '8', '0'
UNION SELECT '9', '0'
UNION SELECT '10', '0'
UNION SELECT '12', '0'
UNION SELECT '13', '0'
UNION SELECT '14', '0'
UNION SELECT '15', '0'
UNION SELECT '16', '0'
UNION SELECT '17', '0'
UNION SELECT '79', '0'
;
-- Table data for table #__schemas

INSERT INTO #__schemas
      SELECT '700' AS extension_id, '1.7.4-2011-11-23' AS version_id
;
-- Table data for table #__template_styles

INSERT INTO #__template_styles
      SELECT '2' AS id, 'bluestork' AS template, '1' AS client_id, '1' AS home, 'Bluestork - Default' AS title, '{"useRoundedCorners":"1","showSiteName":"0"}' AS params
UNION SELECT '3', 'atomic', '0', '0', 'Atomic - Default', '{}'
UNION SELECT '4', 'beez_20', '0', '1', 'Beez2 - Default', '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\/joomla_black.gif","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","templatecolor":"personal","html5":"0"}'
UNION SELECT '5', 'hathor', '1', '0', 'Hathor - Default', '{"showSiteName":"0","colourChoice":"","boldText":"0"}'
UNION SELECT '6', 'beez5', '0', '0', 'Beez5 - Default', '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\/sampledata\/fruitshop\/fruits.gif","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","html5":"0"}'
;
-- Table data for table #__update_sites

INSERT INTO #__update_sites
      SELECT '1' AS update_site_id, 'Joomla Core' AS name, 'collection' AS type, 'http://update.joomla.org/core/list.xml' AS location, '1' AS enabled
UNION SELECT '2', 'Joomla Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', '1'
;
-- Table data for table #__update_sites_extensions

INSERT INTO #__update_sites_extensions
      SELECT '1' AS update_site_id, '700' AS extension_id
UNION SELECT '2', '700'
;
-- Table data for table #__user_usergroup_map

INSERT INTO #__user_usergroup_map
      SELECT '42' AS user_id, '8' AS group_id
;
-- Table data for table #__usergroups

INSERT INTO #__usergroups
      SELECT '1' AS id, '0' AS parent_id, '1' AS lft, '20' AS rgt, 'Public' AS title
UNION SELECT '2', '1', '6', '17', 'Registered'
UNION SELECT '3', '2', '7', '14', 'Author'
UNION SELECT '4', '3', '8', '11', 'Editor'
UNION SELECT '5', '4', '9', '10', 'Publisher'
UNION SELECT '6', '1', '2', '5', 'Manager'
UNION SELECT '7', '6', '3', '4', 'Administrator'
UNION SELECT '8', '1', '18', '19', 'Super Users'
;
-- Table data for table #__users

INSERT INTO #__users
      SELECT '42' AS id, 'Super User' AS name, 'admin' AS username, 'test@nik-it.de' AS email, '75c71b24cd4b7e618a49e235c212ad6d:nQ9LhC9MiIV0Dm0PTH7bZfwgv9Nq0bQk' AS password, 'deprecated' AS usertype, '0' AS block, '1' AS sendEmail, '2011-12-10 00:45:07' AS registerDate, '0000-00-00 00:00:00' AS lastvisitDate, '' AS activation, '' AS params
;
-- Table data for table #__viewlevels

INSERT INTO #__viewlevels
      SELECT '1' AS id, 'Public' AS title, '0' AS ordering, '[1]' AS rules
UNION SELECT '2', 'Registered', '1', '[6,2,8]'
UNION SELECT '3', 'Special', '2', '[6,3,8]'
;