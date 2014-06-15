

-- Table structure for table #__assets

CREATE TABLE IF NOT EXISTS #__assets (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  parent_id INTEGER DEFAULT '0',
  lft INTEGER DEFAULT '0',
  rgt INTEGER DEFAULT '0',
  level INTEGER,
  name TEXT,
  title TEXT,
  rules TEXT
);

-- Table structure for table #__associations

CREATE TABLE IF NOT EXISTS #__associations (
  id INTEGER PRIMARY KEY,
  context TEXT,
  key TEXT
);

-- Table structure for table #__banner_clients

CREATE TABLE IF NOT EXISTS #__banner_clients (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  contact TEXT,
  email TEXT,
  extrainfo TEXT,
  state INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  metakey TEXT,
  own_prefix INTEGER DEFAULT '0',
  metakey_prefix TEXT,
  purchase_type INTEGER DEFAULT '-1',
  track_clicks INTEGER DEFAULT '-1',
  track_impressions INTEGER DEFAULT '-1'
);

-- Table structure for table #__banner_tracks

CREATE TABLE IF NOT EXISTS #__banner_tracks (
  track_date NUMERIC PRIMARY KEY,
  track_type INTEGER,
  banner_id INTEGER,
  count INTEGER DEFAULT '0'
);

-- Table structure for table #__banners

CREATE TABLE IF NOT EXISTS #__banners (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  cid INTEGER DEFAULT '0',
  type INTEGER DEFAULT '0',
  name TEXT,
  alias TEXT,
  imptotal INTEGER DEFAULT '0',
  impmade INTEGER DEFAULT '0',
  clicks INTEGER DEFAULT '0',
  clickurl TEXT,
  state INTEGER DEFAULT '0',
  catid INTEGER DEFAULT '0',
  description TEXT,
  custombannercode TEXT,
  sticky INTEGER DEFAULT '0',
  ordering INTEGER DEFAULT '0',
  metakey TEXT,
  params TEXT,
  own_prefix INTEGER DEFAULT '0',
  metakey_prefix TEXT,
  purchase_type INTEGER DEFAULT '-1',
  track_clicks INTEGER DEFAULT '-1',
  track_impressions INTEGER DEFAULT '-1',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_up NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_down NUMERIC DEFAULT '0000-00-00 00:00:00',
  reset NUMERIC DEFAULT '0000-00-00 00:00:00',
  created NUMERIC DEFAULT '0000-00-00 00:00:00',
  language TEXT,
  created_by INTEGER DEFAULT '0',
  created_by_alias TEXT,
  modified NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_by INTEGER DEFAULT '0',
  version INTEGER DEFAULT '1'
);

-- Table structure for table #__categories

CREATE TABLE IF NOT EXISTS #__categories (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  asset_id INTEGER DEFAULT '0',
  parent_id INTEGER DEFAULT '0',
  lft INTEGER DEFAULT '0',
  rgt INTEGER DEFAULT '0',
  level INTEGER DEFAULT '0',
  path TEXT,
  extension TEXT,
  title TEXT,
  alias TEXT,
  note TEXT,
  description TEXT,
  published INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  access INTEGER DEFAULT '0',
  params TEXT,
  metadesc TEXT,
  metakey TEXT,
  metadata TEXT,
  created_user_id INTEGER DEFAULT '0',
  created_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_user_id INTEGER DEFAULT '0',
  modified_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  hits INTEGER DEFAULT '0',
  language TEXT,
  version INTEGER DEFAULT '1'
);

-- Table structure for table #__contact_details

CREATE TABLE IF NOT EXISTS #__contact_details (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  alias TEXT,
  con_position TEXT,
  address TEXT,
  suburb TEXT,
  state TEXT,
  country TEXT,
  postcode TEXT,
  telephone TEXT,
  fax TEXT,
  misc TEXT,
  image TEXT,
  email_to TEXT,
  default_con INTEGER DEFAULT '0',
  published INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  ordering INTEGER DEFAULT '0',
  params TEXT,
  user_id INTEGER DEFAULT '0',
  catid INTEGER DEFAULT '0',
  access INTEGER DEFAULT '0',
  mobile TEXT,
  webpage TEXT,
  sortname1 TEXT,
  sortname2 TEXT,
  sortname3 TEXT,
  language TEXT,
  created NUMERIC DEFAULT '0000-00-00 00:00:00',
  created_by INTEGER DEFAULT '0',
  created_by_alias TEXT,
  modified NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_by INTEGER DEFAULT '0',
  metakey TEXT,
  metadesc TEXT,
  metadata TEXT,
  featured INTEGER DEFAULT '0',
  xreference TEXT,
  publish_up NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_down NUMERIC DEFAULT '0000-00-00 00:00:00',
  version INTEGER DEFAULT '1',
  hits INTEGER DEFAULT '0'
);

-- Table structure for table #__content

CREATE TABLE IF NOT EXISTS #__content (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  asset_id INTEGER DEFAULT '0',
  title TEXT,
  alias TEXT,
  introtext TEXT,
  fulltext TEXT,
  state INTEGER DEFAULT '0',
  catid INTEGER DEFAULT '0',
  created NUMERIC DEFAULT '0000-00-00 00:00:00',
  created_by INTEGER DEFAULT '0',
  created_by_alias TEXT,
  modified NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_by INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_up NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_down NUMERIC DEFAULT '0000-00-00 00:00:00',
  images TEXT,
  urls TEXT,
  attribs TEXT,
  version INTEGER DEFAULT '1',
  ordering INTEGER DEFAULT '0',
  metakey TEXT,
  metadesc TEXT,
  access INTEGER DEFAULT '0',
  hits INTEGER DEFAULT '0',
  metadata TEXT,
  featured INTEGER DEFAULT '0',
  language TEXT,
  xreference TEXT
);

-- Table structure for table #__content_frontpage

CREATE TABLE IF NOT EXISTS #__content_frontpage (
  content_id INTEGER PRIMARY KEY DEFAULT '0',
  ordering INTEGER DEFAULT '0'
);

-- Table structure for table #__content_rating

CREATE TABLE IF NOT EXISTS #__content_rating (
  content_id INTEGER PRIMARY KEY DEFAULT '0',
  rating_sum INTEGER DEFAULT '0',
  rating_count INTEGER DEFAULT '0',
  lastip TEXT
);

-- Table structure for table #__content_types

CREATE TABLE IF NOT EXISTS #__content_types (
  type_id INTEGER PRIMARY KEY AUTOINCREMENT,
  type_title TEXT,
  type_alias TEXT,
  'table' TEXT,
  rules TEXT,
  field_mappings TEXT,
  router TEXT,
  content_history_options TEXT
);

-- Table structure for table #__contentitem_tag_map

CREATE TABLE IF NOT EXISTS #__contentitem_tag_map (
  type_alias TEXT,
  core_content_id INTEGER,
  content_item_id INTEGER PRIMARY KEY,
  tag_id INTEGER,
  tag_date NUMERIC DEFAULT 'CURRENT_TIMESTAMP',
  type_id INTEGER
);

-- Table structure for table #__core_log_searches

CREATE TABLE IF NOT EXISTS #__core_log_searches (
  search_term TEXT,
  hits INTEGER DEFAULT '0'
);

-- Table structure for table #__extensions

CREATE TABLE IF NOT EXISTS #__extensions (
  extension_id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  type TEXT,
  element TEXT,
  folder TEXT,
  client_id INTEGER,
  enabled INTEGER DEFAULT '1',
  access INTEGER DEFAULT '1',
  protected INTEGER DEFAULT '0',
  manifest_cache TEXT,
  params TEXT,
  custom_data TEXT,
  system_data TEXT,
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  ordering INTEGER DEFAULT '0',
  state INTEGER DEFAULT '0'
);

-- Table structure for table #__finder_filters

CREATE TABLE IF NOT EXISTS #__finder_filters (
  filter_id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  alias TEXT,
  state INTEGER DEFAULT '1',
  created NUMERIC DEFAULT '0000-00-00 00:00:00',
  created_by INTEGER,
  created_by_alias TEXT,
  modified NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_by INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  map_count INTEGER DEFAULT '0',
  data TEXT,
  params TEXT
);

-- Table structure for table #__finder_links

CREATE TABLE IF NOT EXISTS #__finder_links (
  link_id INTEGER PRIMARY KEY AUTOINCREMENT,
  url TEXT,
  route TEXT,
  title TEXT,
  description TEXT,
  indexdate NUMERIC DEFAULT '0000-00-00 00:00:00',
  md5sum TEXT,
  published INTEGER DEFAULT '1',
  state INTEGER DEFAULT '1',
  access INTEGER DEFAULT '0',
  language TEXT,
  publish_start_date NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_end_date NUMERIC DEFAULT '0000-00-00 00:00:00',
  start_date NUMERIC DEFAULT '0000-00-00 00:00:00',
  end_date NUMERIC DEFAULT '0000-00-00 00:00:00',
  list_price REAL DEFAULT '0',
  sale_price REAL DEFAULT '0',
  type_id INTEGER,
  object NONE
);

-- Table structure for table #__finder_links_terms0

CREATE TABLE IF NOT EXISTS #__finder_links_terms0 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms1

CREATE TABLE IF NOT EXISTS #__finder_links_terms1 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms2

CREATE TABLE IF NOT EXISTS #__finder_links_terms2 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms3

CREATE TABLE IF NOT EXISTS #__finder_links_terms3 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms4

CREATE TABLE IF NOT EXISTS #__finder_links_terms4 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms5

CREATE TABLE IF NOT EXISTS #__finder_links_terms5 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms6

CREATE TABLE IF NOT EXISTS #__finder_links_terms6 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms7

CREATE TABLE IF NOT EXISTS #__finder_links_terms7 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms8

CREATE TABLE IF NOT EXISTS #__finder_links_terms8 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_terms9

CREATE TABLE IF NOT EXISTS #__finder_links_terms9 (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_termsa

CREATE TABLE IF NOT EXISTS #__finder_links_termsa (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_termsb

CREATE TABLE IF NOT EXISTS #__finder_links_termsb (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_termsc

CREATE TABLE IF NOT EXISTS #__finder_links_termsc (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_termsd

CREATE TABLE IF NOT EXISTS #__finder_links_termsd (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_termse

CREATE TABLE IF NOT EXISTS #__finder_links_termse (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_links_termsf

CREATE TABLE IF NOT EXISTS #__finder_links_termsf (
  link_id INTEGER PRIMARY KEY,
  term_id INTEGER,
  weight REAL
);

-- Table structure for table #__finder_taxonomy

CREATE TABLE IF NOT EXISTS #__finder_taxonomy (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  parent_id INTEGER DEFAULT '0',
  title TEXT,
  state INTEGER DEFAULT '1',
  access INTEGER DEFAULT '0',
  ordering INTEGER DEFAULT '0'
);

-- Table structure for table #__finder_taxonomy_map

CREATE TABLE IF NOT EXISTS #__finder_taxonomy_map (
  link_id INTEGER,
  node_id INTEGER
);

-- Table structure for table #__finder_terms

CREATE TABLE IF NOT EXISTS #__finder_terms (
  term_id INTEGER PRIMARY KEY AUTOINCREMENT,
  term TEXT,
  stem TEXT,
  common INTEGER DEFAULT '0',
  phrase INTEGER DEFAULT '0',
  weight REAL DEFAULT '0',
  soundex TEXT,
  links INTEGER DEFAULT '0',
  language TEXT
);

-- Table structure for table #__finder_terms_common

CREATE TABLE IF NOT EXISTS #__finder_terms_common (
  term TEXT,
  language TEXT
);

-- Table structure for table #__finder_tokens

CREATE TABLE IF NOT EXISTS #__finder_tokens (
  term TEXT,
  stem TEXT,
  common INTEGER DEFAULT '0',
  phrase INTEGER DEFAULT '0',
  weight REAL DEFAULT '1',
  context INTEGER DEFAULT '2',
  language TEXT
);

-- Table structure for table #__finder_tokens_aggregate

CREATE TABLE IF NOT EXISTS #__finder_tokens_aggregate (
  term_id INTEGER,
  map_suffix TEXT,
  term TEXT,
  stem TEXT,
  common INTEGER DEFAULT '0',
  phrase INTEGER DEFAULT '0',
  term_weight REAL,
  context INTEGER DEFAULT '2',
  context_weight REAL,
  total_weight REAL,
  language TEXT
);

-- Table structure for table #__finder_types

CREATE TABLE IF NOT EXISTS #__finder_types (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  mime TEXT
);

-- Table structure for table #__languages

CREATE TABLE IF NOT EXISTS #__languages (
  lang_id INTEGER PRIMARY KEY AUTOINCREMENT,
  lang_code TEXT,
  title TEXT,
  title_native TEXT,
  sef TEXT,
  image TEXT,
  description TEXT,
  metakey TEXT,
  metadesc TEXT,
  sitename TEXT,
  published INTEGER DEFAULT '0',
  access INTEGER DEFAULT '0',
  ordering INTEGER DEFAULT '0'
);

-- Table structure for table #__menu

CREATE TABLE IF NOT EXISTS #__menu (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  menutype TEXT,
  title TEXT,
  alias TEXT,
  note TEXT,
  path TEXT,
  link TEXT,
  type TEXT,
  published INTEGER DEFAULT '0',
  parent_id INTEGER DEFAULT '1',
  level INTEGER DEFAULT '0',
  component_id INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  browserNav INTEGER DEFAULT '0',
  access INTEGER DEFAULT '0',
  img TEXT,
  template_style_id INTEGER DEFAULT '0',
  params TEXT,
  lft INTEGER DEFAULT '0',
  rgt INTEGER DEFAULT '0',
  home INTEGER DEFAULT '0',
  language TEXT,
  client_id INTEGER DEFAULT '0'
);

-- Table structure for table #__menu_types

CREATE TABLE IF NOT EXISTS #__menu_types (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  menutype TEXT,
  title TEXT,
  description TEXT
);

-- Table structure for table #__messages

CREATE TABLE IF NOT EXISTS #__messages (
  message_id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id_from INTEGER DEFAULT '0',
  user_id_to INTEGER DEFAULT '0',
  folder_id INTEGER DEFAULT '0',
  date_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  state INTEGER DEFAULT '0',
  priority INTEGER DEFAULT '0',
  subject TEXT,
  message TEXT
);

-- Table structure for table #__messages_cfg

CREATE TABLE IF NOT EXISTS #__messages_cfg (
  user_id INTEGER PRIMARY KEY DEFAULT '0',
  cfg_name TEXT,
  cfg_value TEXT
);

-- Table structure for table #__modules

CREATE TABLE IF NOT EXISTS #__modules (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  asset_id INTEGER DEFAULT '0',
  title TEXT,
  note TEXT,
  content TEXT,
  ordering INTEGER DEFAULT '0',
  position TEXT,
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_up NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_down NUMERIC DEFAULT '0000-00-00 00:00:00',
  published INTEGER DEFAULT '0',
  module TEXT,
  access INTEGER DEFAULT '0',
  showtitle INTEGER DEFAULT '1',
  params TEXT,
  client_id INTEGER DEFAULT '0',
  language TEXT
);

-- Table structure for table #__modules_menu

CREATE TABLE IF NOT EXISTS #__modules_menu (
  moduleid INTEGER PRIMARY KEY DEFAULT '0',
  menuid INTEGER DEFAULT '0'
);

-- Table structure for table #__newsfeeds

CREATE TABLE IF NOT EXISTS #__newsfeeds (
  catid INTEGER DEFAULT '0',
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  alias TEXT,
  link TEXT,
  published INTEGER DEFAULT '0',
  numarticles INTEGER DEFAULT '1',
  cache_time INTEGER DEFAULT '3600',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  ordering INTEGER DEFAULT '0',
  rtl INTEGER DEFAULT '0',
  access INTEGER DEFAULT '0',
  language TEXT,
  params TEXT,
  created NUMERIC DEFAULT '0000-00-00 00:00:00',
  created_by INTEGER DEFAULT '0',
  created_by_alias TEXT,
  modified NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_by INTEGER DEFAULT '0',
  metakey TEXT,
  metadesc TEXT,
  metadata TEXT,
  xreference TEXT,
  publish_up NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_down NUMERIC DEFAULT '0000-00-00 00:00:00',
  description TEXT,
  version INTEGER DEFAULT '1',
  hits INTEGER DEFAULT '0',
  images TEXT
);

-- Table structure for table #__overrider

CREATE TABLE IF NOT EXISTS #__overrider (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  constant TEXT,
  string TEXT,
  file TEXT
);

-- Table structure for table #__postinstall_messages

CREATE TABLE IF NOT EXISTS #__postinstall_messages (
  postinstall_message_id INTEGER PRIMARY KEY AUTOINCREMENT,
  extension_id INTEGER DEFAULT '700',
  title_key TEXT,
  description_key TEXT,
  action_key TEXT,
  language_extension TEXT DEFAULT 'com_postinstall',
  language_client_id INTEGER DEFAULT '1',
  type TEXT DEFAULT 'link',
  action_file TEXT,
  action TEXT,
  condition_file TEXT,
  condition_method TEXT,
  version_introduced TEXT DEFAULT '3.2.0',
  enabled INTEGER DEFAULT '1'
);

-- Table structure for table #__redirect_links

CREATE TABLE IF NOT EXISTS #__redirect_links (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  old_url TEXT,
  new_url TEXT,
  referer TEXT,
  comment TEXT,
  hits INTEGER DEFAULT '0',
  published INTEGER,
  created_date NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_date NUMERIC DEFAULT '0000-00-00 00:00:00'
);

-- Table structure for table #__schemas

CREATE TABLE IF NOT EXISTS #__schemas (
  extension_id INTEGER PRIMARY KEY,
  version_id TEXT
);

-- Table structure for table #__session

CREATE TABLE IF NOT EXISTS #__session (
  session_id TEXT PRIMARY KEY,
  client_id INTEGER DEFAULT '0',
  guest INTEGER DEFAULT '1',
  time TEXT,
  data TEXT,
  userid INTEGER DEFAULT '0',
  username TEXT
);

-- Table structure for table #__tags

CREATE TABLE IF NOT EXISTS #__tags (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  parent_id INTEGER DEFAULT '0',
  lft INTEGER DEFAULT '0',
  rgt INTEGER DEFAULT '0',
  level INTEGER DEFAULT '0',
  path TEXT,
  title TEXT,
  alias TEXT,
  note TEXT,
  description TEXT,
  published INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  access INTEGER DEFAULT '0',
  params TEXT,
  metadesc TEXT,
  metakey TEXT,
  metadata TEXT,
  created_user_id INTEGER DEFAULT '0',
  created_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  created_by_alias TEXT,
  modified_user_id INTEGER DEFAULT '0',
  modified_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  images TEXT,
  urls TEXT,
  hits INTEGER DEFAULT '0',
  language TEXT,
  version INTEGER DEFAULT '1',
  publish_up NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_down NUMERIC DEFAULT '0000-00-00 00:00:00'
);

-- Table structure for table #__template_styles

CREATE TABLE IF NOT EXISTS #__template_styles (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  template TEXT,
  client_id INTEGER DEFAULT '0',
  home TEXT DEFAULT '0',
  title TEXT,
  params TEXT
);

-- Table structure for table #__ucm_base

CREATE TABLE IF NOT EXISTS #__ucm_base (
  ucm_id INTEGER PRIMARY KEY,
  ucm_item_id INTEGER,
  ucm_type_id INTEGER,
  ucm_language_id INTEGER
);

-- Table structure for table #__ucm_content

CREATE TABLE IF NOT EXISTS #__ucm_content (
  core_content_id INTEGER PRIMARY KEY AUTOINCREMENT,
  core_type_alias TEXT,
  core_title TEXT,
  core_alias TEXT,
  core_body TEXT,
  core_state INTEGER DEFAULT '0',
  core_checked_out_time TEXT,
  core_checked_out_user_id INTEGER DEFAULT '0',
  core_access INTEGER DEFAULT '0',
  core_params TEXT,
  core_featured INTEGER DEFAULT '0',
  core_metadata TEXT,
  core_created_user_id INTEGER DEFAULT '0',
  core_created_by_alias TEXT,
  core_created_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  core_modified_user_id INTEGER DEFAULT '0',
  core_modified_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  core_language TEXT,
  core_publish_up NUMERIC,
  core_publish_down NUMERIC,
  core_content_item_id INTEGER,
  asset_id INTEGER,
  core_images TEXT,
  core_urls TEXT,
  core_hits INTEGER DEFAULT '0',
  core_version INTEGER DEFAULT '1',
  core_ordering INTEGER DEFAULT '0',
  core_metakey TEXT,
  core_metadesc TEXT,
  core_catid INTEGER DEFAULT '0',
  core_xreference TEXT,
  core_type_id INTEGER
);

-- Table structure for table #__ucm_history

CREATE TABLE IF NOT EXISTS #__ucm_history (
  version_id INTEGER PRIMARY KEY AUTOINCREMENT,
  ucm_item_id INTEGER,
  ucm_type_id INTEGER,
  version_note TEXT,
  save_date NUMERIC DEFAULT '0000-00-00 00:00:00',
  editor_user_id INTEGER DEFAULT '0',
  character_count INTEGER DEFAULT '0',
  sha1_hash TEXT,
  version_data TEXT,
  keep_forever INTEGER DEFAULT '0'
);

-- Table structure for table #__update_sites

CREATE TABLE IF NOT EXISTS #__update_sites (
  update_site_id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  type TEXT,
  location TEXT,
  enabled INTEGER DEFAULT '0',
  last_check_timestamp INTEGER DEFAULT '0',
  extra_query TEXT
);

-- Table structure for table #__update_sites_extensions

CREATE TABLE IF NOT EXISTS #__update_sites_extensions (
  update_site_id INTEGER PRIMARY KEY DEFAULT '0',
  extension_id INTEGER DEFAULT '0'
);

-- Table structure for table #__updates

CREATE TABLE IF NOT EXISTS #__updates (
  update_id INTEGER PRIMARY KEY AUTOINCREMENT,
  update_site_id INTEGER DEFAULT '0',
  extension_id INTEGER DEFAULT '0',
  name TEXT,
  description TEXT,
  element TEXT,
  type TEXT,
  folder TEXT,
  client_id INTEGER DEFAULT '0',
  version TEXT,
  data TEXT,
  detailsurl TEXT,
  infourl TEXT,
  extra_query TEXT
);

-- Table structure for table #__user_keys

CREATE TABLE IF NOT EXISTS #__user_keys (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id TEXT,
  token TEXT,
  series TEXT,
  invalid INTEGER,
  time TEXT,
  uastring TEXT
);

-- Table structure for table #__user_notes

CREATE TABLE IF NOT EXISTS #__user_notes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER DEFAULT '0',
  catid INTEGER DEFAULT '0',
  subject TEXT,
  body TEXT,
  state INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  created_user_id INTEGER DEFAULT '0',
  created_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_user_id INTEGER,
  modified_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  review_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_up NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_down NUMERIC DEFAULT '0000-00-00 00:00:00'
);

-- Table structure for table #__user_profiles

CREATE TABLE IF NOT EXISTS #__user_profiles (
  user_id INTEGER PRIMARY KEY,
  profile_key TEXT,
  profile_value TEXT,
  ordering INTEGER DEFAULT '0'
);

-- Table structure for table #__user_usergroup_map

CREATE TABLE IF NOT EXISTS #__user_usergroup_map (
  user_id INTEGER PRIMARY KEY DEFAULT '0',
  group_id INTEGER DEFAULT '0'
);

-- Table structure for table #__usergroups

CREATE TABLE IF NOT EXISTS #__usergroups (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  parent_id INTEGER DEFAULT '0',
  lft INTEGER DEFAULT '0',
  rgt INTEGER DEFAULT '0',
  title TEXT
);

-- Table structure for table #__users

CREATE TABLE IF NOT EXISTS #__users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  username TEXT,
  email TEXT,
  password TEXT,
  block INTEGER DEFAULT '0',
  sendEmail INTEGER DEFAULT '0',
  registerDate NUMERIC DEFAULT '0000-00-00 00:00:00',
  lastvisitDate NUMERIC DEFAULT '0000-00-00 00:00:00',
  activation TEXT,
  params TEXT,
  lastResetTime NUMERIC DEFAULT '0000-00-00 00:00:00',
  resetCount INTEGER DEFAULT '0',
  otpKey TEXT,
  otep TEXT,
  requireReset INTEGER DEFAULT '0'
);

-- Table structure for table #__viewlevels

CREATE TABLE IF NOT EXISTS #__viewlevels (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT,
  ordering INTEGER DEFAULT '0',
  rules TEXT
);

-- Table structure for table #__weblinks

CREATE TABLE IF NOT EXISTS #__weblinks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  catid INTEGER DEFAULT '0',
  title TEXT,
  alias TEXT,
  url TEXT,
  description TEXT,
  hits INTEGER DEFAULT '0',
  state INTEGER DEFAULT '0',
  checked_out INTEGER DEFAULT '0',
  checked_out_time NUMERIC DEFAULT '0000-00-00 00:00:00',
  ordering INTEGER DEFAULT '0',
  access INTEGER DEFAULT '1',
  params TEXT,
  language TEXT,
  created NUMERIC DEFAULT '0000-00-00 00:00:00',
  created_by INTEGER DEFAULT '0',
  created_by_alias TEXT,
  modified NUMERIC DEFAULT '0000-00-00 00:00:00',
  modified_by INTEGER DEFAULT '0',
  metakey TEXT,
  metadesc TEXT,
  metadata TEXT,
  featured INTEGER DEFAULT '0',
  xreference TEXT,
  publish_up NUMERIC DEFAULT '0000-00-00 00:00:00',
  publish_down NUMERIC DEFAULT '0000-00-00 00:00:00',
  version INTEGER DEFAULT '1',
  images TEXT
);

-- Table data for table #__assets

INSERT INTO #__assets
  SELECT '1' AS id, '0' AS parent_id, '0' AS lft, '105' AS rgt, '0' AS level, 'root.1' AS name, 'Root Asset' AS title, '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1}}' AS rules
UNION SELECT '2', '1', '1', '2', '1', 'com_admin', 'com_admin', '{}'
      UNION SELECT '3', '1', '3', '6', '1', 'com_banners', 'com_banners', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '4', '1', '7', '8', '1', 'com_cache', 'com_cache', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
      UNION SELECT '5', '1', '9', '10', '1', 'com_checkin', 'com_checkin', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
      UNION SELECT '6', '1', '11', '12', '1', 'com_config', 'com_config', '{}'
      UNION SELECT '7', '1', '13', '16', '1', 'com_contact', 'com_contact', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
      UNION SELECT '8', '1', '17', '20', '1', 'com_content', 'com_content', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'
      UNION SELECT '9', '1', '21', '22', '1', 'com_cpanel', 'com_cpanel', '{}'
      UNION SELECT '10', '1', '23', '24', '1', 'com_installer', 'com_installer', '{"core.admin":[],"core.manage":{"7":0},"core.delete":{"7":0},"core.edit.state":{"7":0}}'
      UNION SELECT '11', '1', '25', '26', '1', 'com_languages', 'com_languages', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '12', '1', '27', '28', '1', 'com_login', 'com_login', '{}'
      UNION SELECT '13', '1', '29', '30', '1', 'com_mailto', 'com_mailto', '{}'
      UNION SELECT '14', '1', '31', '32', '1', 'com_massmail', 'com_massmail', '{}'
      UNION SELECT '15', '1', '33', '34', '1', 'com_media', 'com_media', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":{"5":1}}'
      UNION SELECT '16', '1', '35', '36', '1', 'com_menus', 'com_menus', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '17', '1', '37', '38', '1', 'com_messages', 'com_messages', '{"core.admin":{"7":1},"core.manage":{"7":1}}'
      UNION SELECT '18', '1', '39', '70', '1', 'com_modules', 'com_modules', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '19', '1', '71', '74', '1', 'com_newsfeeds', 'com_newsfeeds', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
      UNION SELECT '20', '1', '75', '76', '1', 'com_plugins', 'com_plugins', '{"core.admin":{"7":1},"core.manage":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '21', '1', '77', '78', '1', 'com_redirect', 'com_redirect', '{"core.admin":{"7":1},"core.manage":[]}'
      UNION SELECT '22', '1', '79', '80', '1', 'com_search', 'com_search', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
      UNION SELECT '23', '1', '81', '82', '1', 'com_templates', 'com_templates', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '24', '1', '83', '86', '1', 'com_users', 'com_users', '{"core.admin":{"7":1},"core.manage":[],"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '25', '1', '87', '90', '1', 'com_weblinks', 'com_weblinks', '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.delete":[],"core.edit":{"4":1},"core.edit.state":{"5":1},"core.edit.own":[]}'
      UNION SELECT '26', '1', '91', '92', '1', 'com_wrapper', 'com_wrapper', '{}'
      UNION SELECT '27', '8', '18', '19', '2', 'com_content.category.2', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
      UNION SELECT '28', '3', '4', '5', '2', 'com_banners.category.3', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '29', '7', '14', '15', '2', 'com_contact.category.4', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
      UNION SELECT '30', '19', '72', '73', '2', 'com_newsfeeds.category.5', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
      UNION SELECT '31', '25', '88', '89', '2', 'com_weblinks.category.6', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'
      UNION SELECT '32', '24', '84', '85', '1', 'com_users.category.7', 'Uncategorised', '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '33', '1', '93', '94', '1', 'com_finder', 'com_finder', '{"core.admin":{"7":1},"core.manage":{"6":1}}'
      UNION SELECT '34', '1', '95', '96', '1', 'com_joomlaupdate', 'com_joomlaupdate', '{"core.admin":[],"core.manage":[],"core.delete":[],"core.edit.state":[]}'
      UNION SELECT '35', '1', '97', '98', '1', 'com_tags', 'com_tags', '{"core.admin":[],"core.manage":[],"core.manage":[],"core.delete":[],"core.edit.state":[]}'
      UNION SELECT '36', '1', '99', '100', '1', 'com_contenthistory', 'com_contenthistory', '{}'
      UNION SELECT '37', '1', '101', '102', '1', 'com_ajax', 'com_ajax', '{}'
      UNION SELECT '38', '1', '103', '104', '1', 'com_postinstall', 'com_postinstall', '{}'
      UNION SELECT '39', '18', '40', '41', '2', 'com_modules.module.1', 'Main Menu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '40', '18', '42', '43', '2', 'com_modules.module.2', 'Login', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '41', '18', '44', '45', '2', 'com_modules.module.3', 'Popular Articles', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '42', '18', '46', '47', '2', 'com_modules.module.4', 'Recently Added Articles', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '43', '18', '48', '49', '2', 'com_modules.module.8', 'Toolbar', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '44', '18', '50', '51', '2', 'com_modules.module.9', 'Quick Icons', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '45', '18', '52', '53', '2', 'com_modules.module.10', 'Logged-in Users', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '46', '18', '54', '55', '2', 'com_modules.module.12', 'Admin Menu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '47', '18', '56', '57', '2', 'com_modules.module.13', 'Admin Submenu', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '48', '18', '58', '59', '2', 'com_modules.module.14', 'User Status', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '49', '18', '60', '61', '2', 'com_modules.module.15', 'Title', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '50', '18', '62', '63', '2', 'com_modules.module.16', 'Login Form', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '51', '18', '64', '65', '2', 'com_modules.module.17', 'Breadcrumbs', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '52', '18', '66', '67', '2', 'com_modules.module.79', 'Multilanguage status', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
      UNION SELECT '53', '18', '68', '69', '2', 'com_modules.module.86', 'Joomla Version', '{"core.delete":[],"core.edit":[],"core.edit.state":[]}'
;

-- Table data for table #__categories

INSERT INTO #__categories
  SELECT '1' AS id, '0' AS asset_id, '0' AS parent_id, '0' AS lft, '13' AS rgt, '0' AS level, '' AS path, 'system' AS extension, 'ROOT' AS title, 'root' AS alias, '' AS note, '' AS description, '1' AS published, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '1' AS access, '{}' AS params, '' AS metadesc, '' AS metakey, '{}' AS metadata, '42' AS created_user_id, '2011-01-01 00:00:01' AS created_time, '0' AS modified_user_id, '0000-00-00 00:00:00' AS modified_time, '0' AS hits, '*' AS language, '1' AS version
UNION SELECT '2', '27', '1', '1', '2', '1', 'uncategorised', 'com_content', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2011-01-01 00:00:01', '0', '0000-00-00 00:00:00', '0', '*', '1'
      UNION SELECT '3', '28', '1', '3', '4', '1', 'uncategorised', 'com_banners', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2011-01-01 00:00:01', '0', '0000-00-00 00:00:00', '0', '*', '1'
      UNION SELECT '4', '29', '1', '5', '6', '1', 'uncategorised', 'com_contact', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2011-01-01 00:00:01', '0', '0000-00-00 00:00:00', '0', '*', '1'
      UNION SELECT '5', '30', '1', '7', '8', '1', 'uncategorised', 'com_newsfeeds', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2011-01-01 00:00:01', '0', '0000-00-00 00:00:00', '0', '*', '1'
      UNION SELECT '6', '31', '1', '9', '10', '1', 'uncategorised', 'com_weblinks', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2011-01-01 00:00:01', '0', '0000-00-00 00:00:00', '0', '*', '1'
      UNION SELECT '7', '32', '1', '11', '12', '1', 'uncategorised', 'com_users', 'Uncategorised', 'uncategorised', '', '', '1', '0', '0000-00-00 00:00:00', '1', '{"category_layout":"","image":""}', '', '', '{"author":"","robots":""}', '42', '2011-01-01 00:00:01', '0', '0000-00-00 00:00:00', '0', '*', '1'
;

-- Table data for table #__content_types

INSERT INTO #__content_types
  SELECT '1' AS type_id, 'Article' AS type_title, 'com_content.article' AS type_alias, '{"special":{"dbtable":"#__content","key":"id","type":"Content","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}' AS 'table', '' AS rules, '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"introtext", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"attribs", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"asset_id"}, "special":{"fulltext":"fulltext"}}' AS field_mappings, 'ContentHelperRoute::getArticleRoute' AS router, '{"formFile":"administrator\/components\/com_content\/models\/forms\/article.xml", "hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}' AS content_history_options
UNION SELECT '2', 'Weblink', 'com_weblinks.weblink', '{"special":{"dbtable":"#__weblinks","key":"id","type":"Weblink","prefix":"WeblinksTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"state","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{}}', 'WeblinksHelperRoute::getWeblinkRoute', '{"formFile":"administrator\/components\/com_weblinks\/models\/forms\/weblink.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","featured","images"], "ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"], "convertToInt":["publish_up", "publish_down", "featured", "ordering"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'
      UNION SELECT '3', 'Contact', 'com_contact.contact', '{"special":{"dbtable":"#__contact_details","key":"id","type":"Contact","prefix":"ContactTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"address", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"image", "core_urls":"webpage", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"con_position":"con_position","suburb":"suburb","state":"state","country":"country","postcode":"postcode","telephone":"telephone","fax":"fax","misc":"misc","email_to":"email_to","default_con":"default_con","user_id":"user_id","mobile":"mobile","sortname1":"sortname1","sortname2":"sortname2","sortname3":"sortname3"}}', 'ContactHelperRoute::getContactRoute', '{"formFile":"administrator\/components\/com_contact\/models\/forms\/contact.xml","hideFields":["default_con","checked_out","checked_out_time","version","xreference"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"], "displayLookup":[ {"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ] }'
      UNION SELECT '4', 'Newsfeed', 'com_newsfeeds.newsfeed', '{"special":{"dbtable":"#__newsfeeds","key":"id","type":"Newsfeed","prefix":"NewsfeedsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"hits","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"xreference", "asset_id":"null"}, "special":{"numarticles":"numarticles","cache_time":"cache_time","rtl":"rtl"}}', 'NewsfeedsHelperRoute::getNewsfeedRoute', '{"formFile":"administrator\/components\/com_newsfeeds\/models\/forms\/newsfeed.xml","hideFields":["asset_id","checked_out","checked_out_time","version"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "hits"],"convertToInt":["publish_up", "publish_down", "featured", "ordering"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'
      UNION SELECT '5', 'User', 'com_users.user', '{"special":{"dbtable":"#__users","key":"id","type":"User","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"null","core_alias":"username","core_created_time":"registerdate","core_modified_time":"lastvisitDate","core_body":"null", "core_hits":"null","core_publish_up":"null","core_publish_down":"null","access":"null", "core_params":"params", "core_featured":"null", "core_metadata":"null", "core_language":"null", "core_images":"null", "core_urls":"null", "core_version":"null", "core_ordering":"null", "core_metakey":"null", "core_metadesc":"null", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{}}', 'UsersHelperRoute::getUserRoute', ''
      UNION SELECT '6', 'Article Category', 'com_content.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContentHelperRoute::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
      UNION SELECT '7', 'Contact Category', 'com_contact.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'ContactHelperRoute::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
      UNION SELECT '8', 'Newsfeeds Category', 'com_newsfeeds.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'NewsfeedsHelperRoute::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
      UNION SELECT '9', 'Weblinks Category', 'com_weblinks.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', 'WeblinksHelperRoute::getCategoryRoute', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
      UNION SELECT '10', 'Tag', 'com_tags.tag', '{"special":{"dbtable":"#__tags","key":"tag_id","type":"Tag","prefix":"TagsTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"featured", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"urls", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"null", "core_xreference":"null", "asset_id":"null"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path"}}', 'TagsHelperRoute::getTagRoute', '{"formFile":"administrator\/components\/com_tags\/models\/forms\/tag.xml", "hideFields":["checked_out","checked_out_time","version", "lft", "rgt", "level", "path", "urls", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"],"convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}'
      UNION SELECT '11', 'Banner', 'com_banners.banner', '{"special":{"dbtable":"#__banners","key":"id","type":"Banner","prefix":"BannersTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"name","core_state":"published","core_alias":"alias","core_created_time":"created","core_modified_time":"modified","core_body":"description", "core_hits":"null","core_publish_up":"publish_up","core_publish_down":"publish_down","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"images", "core_urls":"link", "core_version":"version", "core_ordering":"ordering", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"catid", "core_xreference":"null", "asset_id":"null"}, "special":{"imptotal":"imptotal", "impmade":"impmade", "clicks":"clicks", "clickurl":"clickurl", "custombannercode":"custombannercode", "cid":"cid", "purchase_type":"purchase_type", "track_impressions":"track_impressions", "track_clicks":"track_clicks"}}', '', '{"formFile":"administrator\/components\/com_banners\/models\/forms\/banner.xml", "hideFields":["checked_out","checked_out_time","version", "reset"],"ignoreChanges":["modified_by", "modified", "checked_out", "checked_out_time", "version", "imptotal", "impmade", "reset"], "convertToInt":["publish_up", "publish_down", "ordering"], "displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"cid","targetTable":"#__banner_clients","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"created_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"modified_by","targetTable":"#__users","targetColumn":"id","displayColumn":"name"} ]}'
      UNION SELECT '12', 'Banners Category', 'com_banners.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special": {"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["asset_id","checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
      UNION SELECT '13', 'Banner Client', 'com_banners.client', '{"special":{"dbtable":"#__banner_clients","key":"id","type":"Client","prefix":"BannersTable"}}', '', '', '', '{"formFile":"administrator\/components\/com_banners\/models\/forms\/client.xml", "hideFields":["checked_out","checked_out_time"], "ignoreChanges":["checked_out", "checked_out_time"], "convertToInt":[], "displayLookup":[]}'
      UNION SELECT '14', 'User Notes', 'com_users.note', '{"special":{"dbtable":"#__user_notes","key":"id","type":"Note","prefix":"UsersTable"}}', '', '', '', '{"formFile":"administrator\/components\/com_users\/models\/forms\/note.xml", "hideFields":["checked_out","checked_out_time", "publish_up", "publish_down"],"ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time"], "convertToInt":["publish_up", "publish_down"],"displayLookup":[{"sourceColumn":"catid","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}, {"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}]}'
      UNION SELECT '15', 'User Notes Category', 'com_users.category', '{"special":{"dbtable":"#__categories","key":"id","type":"Category","prefix":"JTable","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"JTable","config":"array()"}}', '', '{"common":{"core_content_item_id":"id","core_title":"title","core_state":"published","core_alias":"alias","core_created_time":"created_time","core_modified_time":"modified_time","core_body":"description", "core_hits":"hits","core_publish_up":"null","core_publish_down":"null","core_access":"access", "core_params":"params", "core_featured":"null", "core_metadata":"metadata", "core_language":"language", "core_images":"null", "core_urls":"null", "core_version":"version", "core_ordering":"null", "core_metakey":"metakey", "core_metadesc":"metadesc", "core_catid":"parent_id", "core_xreference":"null", "asset_id":"asset_id"}, "special":{"parent_id":"parent_id","lft":"lft","rgt":"rgt","level":"level","path":"path","extension":"extension","note":"note"}}', '', '{"formFile":"administrator\/components\/com_categories\/models\/forms\/category.xml", "hideFields":["checked_out","checked_out_time","version","lft","rgt","level","path","extension"], "ignoreChanges":["modified_user_id", "modified_time", "checked_out", "checked_out_time", "version", "hits", "path"], "convertToInt":["publish_up", "publish_down"], "displayLookup":[{"sourceColumn":"created_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"}, {"sourceColumn":"access","targetTable":"#__viewlevels","targetColumn":"id","displayColumn":"title"},{"sourceColumn":"modified_user_id","targetTable":"#__users","targetColumn":"id","displayColumn":"name"},{"sourceColumn":"parent_id","targetTable":"#__categories","targetColumn":"id","displayColumn":"title"}]}'
;

-- Table data for table #__extensions

INSERT INTO #__extensions
  SELECT '1' AS extension_id, 'com_mailto' AS name, 'component' AS type, 'com_mailto' AS element, '' AS folder, '0' AS client_id, '1' AS enabled, '1' AS access, '1' AS protected, '' AS manifest_cache, '' AS params, '' AS custom_data, '' AS system_data, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '0' AS ordering, '0' AS state
UNION SELECT '2', 'com_wrapper', 'component', 'com_wrapper', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '3', 'com_admin', 'component', 'com_admin', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '4', 'com_banners', 'component', 'com_banners', '', '1', '1', '1', '0', '', '{"purchase_type":"3","track_impressions":"0","track_clicks":"0","metakey_prefix":"","save_history":"1","history_limit":10}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '5', 'com_cache', 'component', 'com_cache', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '6', 'com_categories', 'component', 'com_categories', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '7', 'com_checkin', 'component', 'com_checkin', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '8', 'com_contact', 'component', 'com_contact', '', '1', '1', '1', '0', '', '{"show_contact_category":"hide","save_history":"1","history_limit":10,"show_contact_list":"0","presentation_style":"sliders","show_name":"1","show_position":"1","show_email":"0","show_street_address":"1","show_suburb":"1","show_state":"1","show_postcode":"1","show_country":"1","show_telephone":"1","show_mobile":"1","show_fax":"1","show_webpage":"1","show_misc":"1","show_image":"1","image":"","allow_vcard":"0","show_articles":"0","show_profile":"0","show_links":"0","linka_name":"","linkb_name":"","linkc_name":"","linkd_name":"","linke_name":"","contact_icons":"0","icon_address":"","icon_email":"","icon_telephone":"","icon_mobile":"","icon_fax":"","icon_misc":"","show_headings":"1","show_position_headings":"1","show_email_headings":"0","show_telephone_headings":"1","show_mobile_headings":"0","show_fax_headings":"0","allow_vcard_headings":"0","show_suburb_headings":"1","show_state_headings":"1","show_country_headings":"1","show_email_form":"1","show_email_copy":"1","banned_email":"","banned_subject":"","banned_text":"","validate_session":"1","custom_reply":"0","redirect":"","show_category_crumb":"0","metakey":"","metadesc":"","robots":"","author":"","rights":"","xreference":""}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '9', 'com_cpanel', 'component', 'com_cpanel', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '10', 'com_installer', 'component', 'com_installer', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '11', 'com_languages', 'component', 'com_languages', '', '1', '1', '1', '1', '', '{"administrator":"en-GB","site":"en-GB"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '12', 'com_login', 'component', 'com_login', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '13', 'com_media', 'component', 'com_media', '', '1', '1', '0', '1', '', '{"upload_extensions":"bmp,csv,doc,gif,ico,jpg,jpeg,odg,odp,ods,odt,pdf,png,ppt,swf,txt,xcf,xls,BMP,CSV,DOC,GIF,ICO,JPG,JPEG,ODG,ODP,ODS,ODT,PDF,PNG,PPT,SWF,TXT,XCF,XLS","upload_maxsize":"10","file_path":"images","image_path":"images","restrict_uploads":"1","allowed_media_usergroup":"3","check_mime":"1","image_extensions":"bmp,gif,jpg,png","ignore_extensions":"","upload_mime":"image\/jpeg,image\/gif,image\/png,image\/bmp,application\/x-shockwave-flash,application\/msword,application\/excel,application\/pdf,application\/powerpoint,text\/plain,application\/x-zip","upload_mime_illegal":"text\/html"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '14', 'com_menus', 'component', 'com_menus', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '15', 'com_messages', 'component', 'com_messages', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '16', 'com_modules', 'component', 'com_modules', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '17', 'com_newsfeeds', 'component', 'com_newsfeeds', '', '1', '1', '1', '0', '', '{"newsfeed_layout":"_:default","save_history":"1","history_limit":5,"show_feed_image":"1","show_feed_description":"1","show_item_description":"1","feed_character_count":"0","feed_display_order":"des","float_first":"right","float_second":"right","show_tags":"1","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"1","maxLevel":"-1","show_empty_categories":"0","show_subcat_desc":"1","show_cat_items":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_items_cat":"1","filter_field":"1","show_pagination_limit":"1","show_headings":"1","show_articles":"0","show_link":"1","show_pagination":"1","show_pagination_results":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '18', 'com_plugins', 'component', 'com_plugins', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '19', 'com_search', 'component', 'com_search', '', '1', '1', '1', '0', '', '{"enabled":"0","show_date":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '20', 'com_templates', 'component', 'com_templates', '', '1', '1', '1', '1', '', '{"template_positions_display":"0","upload_limit":"2","image_formats":"gif,bmp,jpg,jpeg,png","source_formats":"txt,less,ini,xml,js,php,css","font_formats":"woff,ttf,otf","compressed_formats":"zip"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '21', 'com_weblinks', 'component', 'com_weblinks', '', '1', '1', '1', '0', '', '{"target":"0","save_history":"1","history_limit":5,"count_clicks":"1","icons":1,"link_icons":"","float_first":"right","float_second":"right","show_tags":"1","category_layout":"_:default","show_category_title":"1","show_description":"1","show_description_image":"1","maxLevel":"-1","show_empty_categories":"0","show_subcat_desc":"1","show_cat_num_links":"1","show_cat_tags":"1","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_links_cat":"1","filter_field":"1","show_pagination_limit":"1","show_headings":"0","show_link_description":"1","show_link_hits":"1","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '22', 'com_content', 'component', 'com_content', '', '1', '1', '0', '1', '', '{"article_layout":"_:default","show_title":"1","link_titles":"1","show_intro":"1","show_category":"1","link_category":"1","show_parent_category":"0","link_parent_category":"0","show_author":"1","link_author":"0","show_create_date":"0","show_modify_date":"0","show_publish_date":"1","show_item_navigation":"1","show_vote":"0","show_readmore":"1","show_readmore_title":"1","readmore_limit":"100","show_icons":"1","show_print_icon":"1","show_email_icon":"1","show_hits":"1","show_noauth":"0","show_publishing_options":"1","show_article_options":"1","save_history":"1","history_limit":10,"show_urls_images_frontend":"0","show_urls_images_backend":"1","targeta":0,"targetb":0,"targetc":0,"float_intro":"left","float_fulltext":"left","category_layout":"_:blog","show_category_title":"0","show_description":"0","show_description_image":"0","maxLevel":"1","show_empty_categories":"0","show_no_articles":"1","show_subcat_desc":"1","show_cat_num_articles":"0","show_base_description":"1","maxLevelcat":"-1","show_empty_categories_cat":"0","show_subcat_desc_cat":"1","show_cat_num_articles_cat":"1","num_leading_articles":"1","num_intro_articles":"4","num_columns":"2","num_links":"4","multi_column_order":"0","show_subcategory_content":"0","show_pagination_limit":"1","filter_field":"hide","show_headings":"1","list_show_date":"0","date_format":"","list_show_hits":"1","list_show_author":"1","orderby_pri":"order","orderby_sec":"rdate","order_date":"published","show_pagination":"2","show_pagination_results":"1","show_feed_link":"1","feed_summary":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '23', 'com_config', 'component', 'com_config', '', '1', '1', '0', '1', '', '{"filters":{"1":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"6":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"7":{"filter_type":"NONE","filter_tags":"","filter_attributes":""},"2":{"filter_type":"NH","filter_tags":"","filter_attributes":""},"3":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"4":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"5":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"10":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"12":{"filter_type":"BL","filter_tags":"","filter_attributes":""},"8":{"filter_type":"NONE","filter_tags":"","filter_attributes":""}}}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '24', 'com_redirect', 'component', 'com_redirect', '', '1', '1', '0', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '25', 'com_users', 'component', 'com_users', '', '1', '1', '0', '1', '', '{"allowUserRegistration":"1","new_usertype":"2","guest_usergroup":"9","sendpassword":"1","useractivation":"1","mail_to_admin":"0","captcha":"","frontend_userparams":"1","site_language":"0","change_login_name":"0","reset_count":"10","reset_time":"1","minimum_length":"4","minimum_integers":"0","minimum_symbols":"0","minimum_uppercase":"0","save_history":"1","history_limit":5,"mailSubjectPrefix":"","mailBodySuffix":""}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '27', 'com_finder', 'component', 'com_finder', '', '1', '1', '0', '0', '', '{"show_description":"1","description_length":255,"allow_empty_query":"0","show_url":"1","show_advanced":"1","expand_advanced":"0","show_date_filters":"0","highlight_terms":"1","opensearch_name":"","opensearch_description":"","batch_size":"50","memory_table_limit":30000,"title_multiplier":"1.7","text_multiplier":"0.7","meta_multiplier":"1.2","path_multiplier":"2.0","misc_multiplier":"0.3","stemmer":"snowball"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '28', 'com_joomlaupdate', 'component', 'com_joomlaupdate', '', '1', '1', '0', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '29', 'com_tags', 'component', 'com_tags', '', '1', '1', '1', '1', '', '{"tag_layout":"_:default","save_history":"1","history_limit":5,"show_tag_title":"0","tag_list_show_tag_image":"0","tag_list_show_tag_description":"0","tag_list_image":"","show_tag_num_items":"0","tag_list_orderby":"title","tag_list_orderby_direction":"ASC","show_headings":"0","tag_list_show_date":"0","tag_list_show_item_image":"0","tag_list_show_item_description":"0","tag_list_item_maximum_characters":0,"return_any_or_all":"1","include_children":"0","maximum":200,"tag_list_language_filter":"all","tags_layout":"_:default","all_tags_orderby":"title","all_tags_orderby_direction":"ASC","all_tags_show_tag_image":"0","all_tags_show_tag_descripion":"0","all_tags_tag_maximum_characters":20,"all_tags_show_tag_hits":"0","filter_field":"1","show_pagination_limit":"1","show_pagination":"2","show_pagination_results":"1","tag_field_ajax_mode":"1","show_feed_link":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '30', 'com_contenthistory', 'component', 'com_contenthistory', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '31', 'com_ajax', 'component', 'com_ajax', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '32', 'com_postinstall', 'component', 'com_postinstall', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '100', 'PHPMailer', 'library', 'phpmailer', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '101', 'SimplePie', 'library', 'simplepie', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '102', 'phputf8', 'library', 'phputf8', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '103', 'Joomla! Platform', 'library', 'joomla', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '104', 'IDNA Convert', 'library', 'idna_convert', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '105', 'FOF', 'library', 'fof', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '106', 'PHPass', 'library', 'phpass', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '200', 'mod_articles_archive', 'module', 'mod_articles_archive', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '201', 'mod_articles_latest', 'module', 'mod_articles_latest', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '202', 'mod_articles_popular', 'module', 'mod_articles_popular', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '203', 'mod_banners', 'module', 'mod_banners', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '204', 'mod_breadcrumbs', 'module', 'mod_breadcrumbs', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '205', 'mod_custom', 'module', 'mod_custom', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '206', 'mod_feed', 'module', 'mod_feed', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '207', 'mod_footer', 'module', 'mod_footer', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '208', 'mod_login', 'module', 'mod_login', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '209', 'mod_menu', 'module', 'mod_menu', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '210', 'mod_articles_news', 'module', 'mod_articles_news', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '211', 'mod_random_image', 'module', 'mod_random_image', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '212', 'mod_related_items', 'module', 'mod_related_items', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '213', 'mod_search', 'module', 'mod_search', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '214', 'mod_stats', 'module', 'mod_stats', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '215', 'mod_syndicate', 'module', 'mod_syndicate', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '216', 'mod_users_latest', 'module', 'mod_users_latest', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '217', 'mod_weblinks', 'module', 'mod_weblinks', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '218', 'mod_whosonline', 'module', 'mod_whosonline', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '219', 'mod_wrapper', 'module', 'mod_wrapper', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '220', 'mod_articles_category', 'module', 'mod_articles_category', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '221', 'mod_articles_categories', 'module', 'mod_articles_categories', '', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '222', 'mod_languages', 'module', 'mod_languages', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '223', 'mod_finder', 'module', 'mod_finder', '', '0', '1', '0', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '300', 'mod_custom', 'module', 'mod_custom', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '301', 'mod_feed', 'module', 'mod_feed', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '302', 'mod_latest', 'module', 'mod_latest', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '303', 'mod_logged', 'module', 'mod_logged', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '304', 'mod_login', 'module', 'mod_login', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '305', 'mod_menu', 'module', 'mod_menu', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '307', 'mod_popular', 'module', 'mod_popular', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '308', 'mod_quickicon', 'module', 'mod_quickicon', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '309', 'mod_status', 'module', 'mod_status', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '310', 'mod_submenu', 'module', 'mod_submenu', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '311', 'mod_title', 'module', 'mod_title', '', '1', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '312', 'mod_toolbar', 'module', 'mod_toolbar', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '313', 'mod_multilangstatus', 'module', 'mod_multilangstatus', '', '1', '1', '1', '0', '', '{"cache":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '314', 'mod_version', 'module', 'mod_version', '', '1', '1', '1', '0', '', '{"format":"short","product":"1","cache":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '315', 'mod_stats_admin', 'module', 'mod_stats_admin', '', '1', '1', '1', '0', '', '{"serverinfo":"0","siteinfo":"0","counter":"0","increase":"0","cache":"1","cache_time":"900","cachemode":"static"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '316', 'mod_tags_popular', 'module', 'mod_tags_popular', '', '0', '1', '1', '0', '', '{"maximum":"5","timeframe":"alltime","owncache":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '317', 'mod_tags_similar', 'module', 'mod_tags_similar', '', '0', '1', '1', '0', '', '{"maximum":"5","matchtype":"any","owncache":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '400', 'plg_authentication_gmail', 'plugin', 'gmail', 'authentication', '0', '0', '1', '0', '', '{"applysuffix":"0","suffix":"","verifypeer":"1","user_blacklist":""}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '401', 'plg_authentication_joomla', 'plugin', 'joomla', 'authentication', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '402', 'plg_authentication_ldap', 'plugin', 'ldap', 'authentication', '0', '0', '1', '0', '', '{"host":"","port":"389","use_ldapV3":"0","negotiate_tls":"0","no_referrals":"0","auth_method":"bind","base_dn":"","search_string":"","users_dn":"","username":"admin","password":"bobby7","ldap_fullname":"fullName","ldap_email":"mail","ldap_uid":"uid"}', '', '', '0', '0000-00-00 00:00:00', '3', '0'
      UNION SELECT '403', 'plg_content_contact', 'plugin', 'contact', 'content', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '404', 'plg_content_emailcloak', 'plugin', 'emailcloak', 'content', '0', '1', '1', '0', '', '{"mode":"1"}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '406', 'plg_content_loadmodule', 'plugin', 'loadmodule', 'content', '0', '1', '1', '0', '', '{"style":"xhtml"}', '', '', '0', '2011-09-18 15:22:50', '0', '0'
      UNION SELECT '407', 'plg_content_pagebreak', 'plugin', 'pagebreak', 'content', '0', '1', '1', '0', '', '{"title":"1","multipage_toc":"1","showall":"1"}', '', '', '0', '0000-00-00 00:00:00', '4', '0'
      UNION SELECT '408', 'plg_content_pagenavigation', 'plugin', 'pagenavigation', 'content', '0', '1', '1', '0', '', '{"position":"1"}', '', '', '0', '0000-00-00 00:00:00', '5', '0'
      UNION SELECT '409', 'plg_content_vote', 'plugin', 'vote', 'content', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '6', '0'
      UNION SELECT '410', 'plg_editors_codemirror', 'plugin', 'codemirror', 'editors', '0', '1', '1', '1', '', '{"lineNumbers":"1","lineWrapping":"1","matchTags":"1","matchBrackets":"1","marker-gutter":"1","autoCloseTags":"1","autoCloseBrackets":"1","autoFocus":"1","theme":"default","tabmode":"indent"}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '411', 'plg_editors_none', 'plugin', 'none', 'editors', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '2', '0'
      UNION SELECT '412', 'plg_editors_tinymce', 'plugin', 'tinymce', 'editors', '0', '1', '1', '0', '', '{"mode":"1","skin":"0","mobile":"0","entity_encoding":"raw","lang_mode":"1","text_direction":"ltr","content_css":"1","content_css_custom":"","relative_urls":"1","newlines":"0","invalid_elements":"script,applet,iframe","extended_elements":"","html_height":"550","html_width":"750","resizing":"1","element_path":"1","fonts":"1","paste":"1","searchreplace":"1","insertdate":"1","colors":"1","table":"1","smilies":"1","hr":"1","link":"1","media":"1","print":"1","directionality":"1","fullscreen":"1","alignment":"1","visualchars":"1","visualblocks":"1","nonbreaking":"1","template":"1","blockquote":"1","wordcount":"1","advlist":"1","autosave":"1","contextmenu":"1","inlinepopups":"1","custom_plugin":"","custom_button":""}', '', '', '0', '0000-00-00 00:00:00', '3', '0'
      UNION SELECT '413', 'plg_editors-xtd_article', 'plugin', 'article', 'editors-xtd', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '414', 'plg_editors-xtd_image', 'plugin', 'image', 'editors-xtd', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '2', '0'
      UNION SELECT '415', 'plg_editors-xtd_pagebreak', 'plugin', 'pagebreak', 'editors-xtd', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '3', '0'
      UNION SELECT '416', 'plg_editors-xtd_readmore', 'plugin', 'readmore', 'editors-xtd', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '4', '0'
      UNION SELECT '417', 'plg_search_categories', 'plugin', 'categories', 'search', '0', '1', '1', '0', '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '418', 'plg_search_contacts', 'plugin', 'contacts', 'search', '0', '1', '1', '0', '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '419', 'plg_search_content', 'plugin', 'content', 'search', '0', '1', '1', '0', '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '420', 'plg_search_newsfeeds', 'plugin', 'newsfeeds', 'search', '0', '1', '1', '0', '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '421', 'plg_search_weblinks', 'plugin', 'weblinks', 'search', '0', '1', '1', '0', '', '{"search_limit":"50","search_content":"1","search_archived":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '422', 'plg_system_languagefilter', 'plugin', 'languagefilter', 'system', '0', '0', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '423', 'plg_system_p3p', 'plugin', 'p3p', 'system', '0', '1', '1', '0', '', '{"headers":"NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"}', '', '', '0', '0000-00-00 00:00:00', '2', '0'
      UNION SELECT '424', 'plg_system_cache', 'plugin', 'cache', 'system', '0', '0', '1', '1', '', '{"browsercache":"0","cachetime":"15"}', '', '', '0', '0000-00-00 00:00:00', '9', '0'
      UNION SELECT '425', 'plg_system_debug', 'plugin', 'debug', 'system', '0', '1', '1', '0', '', '{"profile":"1","queries":"1","memory":"1","language_files":"1","language_strings":"1","strip-first":"1","strip-prefix":"","strip-suffix":""}', '', '', '0', '0000-00-00 00:00:00', '4', '0'
      UNION SELECT '426', 'plg_system_log', 'plugin', 'log', 'system', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '5', '0'
      UNION SELECT '427', 'plg_system_redirect', 'plugin', 'redirect', 'system', '0', '0', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '6', '0'
      UNION SELECT '428', 'plg_system_remember', 'plugin', 'remember', 'system', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '7', '0'
      UNION SELECT '429', 'plg_system_sef', 'plugin', 'sef', 'system', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '8', '0'
      UNION SELECT '430', 'plg_system_logout', 'plugin', 'logout', 'system', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '3', '0'
      UNION SELECT '431', 'plg_user_contactcreator', 'plugin', 'contactcreator', 'user', '0', '0', '1', '0', '', '{"autowebpage":"","category":"34","autopublish":"0"}', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '432', 'plg_user_joomla', 'plugin', 'joomla', 'user', '0', '1', '1', '0', '', '{"strong_passwords":"1","autoregister":"1"}', '', '', '0', '0000-00-00 00:00:00', '2', '0'
      UNION SELECT '433', 'plg_user_profile', 'plugin', 'profile', 'user', '0', '0', '1', '0', '', '{"register-require_address1":"1","register-require_address2":"1","register-require_city":"1","register-require_region":"1","register-require_country":"1","register-require_postal_code":"1","register-require_phone":"1","register-require_website":"1","register-require_favoritebook":"1","register-require_aboutme":"1","register-require_tos":"1","register-require_dob":"1","profile-require_address1":"1","profile-require_address2":"1","profile-require_city":"1","profile-require_region":"1","profile-require_country":"1","profile-require_postal_code":"1","profile-require_phone":"1","profile-require_website":"1","profile-require_favoritebook":"1","profile-require_aboutme":"1","profile-require_tos":"1","profile-require_dob":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '434', 'plg_extension_joomla', 'plugin', 'joomla', 'extension', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '435', 'plg_content_joomla', 'plugin', 'joomla', 'content', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '436', 'plg_system_languagecode', 'plugin', 'languagecode', 'system', '0', '0', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '10', '0'
      UNION SELECT '437', 'plg_quickicon_joomlaupdate', 'plugin', 'joomlaupdate', 'quickicon', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '438', 'plg_quickicon_extensionupdate', 'plugin', 'extensionupdate', 'quickicon', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '439', 'plg_captcha_recaptcha', 'plugin', 'recaptcha', 'captcha', '0', '0', '1', '0', '', '{"public_key":"","private_key":"","theme":"clean"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '440', 'plg_system_highlight', 'plugin', 'highlight', 'system', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '7', '0'
      UNION SELECT '441', 'plg_content_finder', 'plugin', 'finder', 'content', '0', '0', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '442', 'plg_finder_categories', 'plugin', 'categories', 'finder', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '1', '0'
      UNION SELECT '443', 'plg_finder_contacts', 'plugin', 'contacts', 'finder', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '2', '0'
      UNION SELECT '444', 'plg_finder_content', 'plugin', 'content', 'finder', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '3', '0'
      UNION SELECT '445', 'plg_finder_newsfeeds', 'plugin', 'newsfeeds', 'finder', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '4', '0'
      UNION SELECT '446', 'plg_finder_weblinks', 'plugin', 'weblinks', 'finder', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '5', '0'
      UNION SELECT '447', 'plg_finder_tags', 'plugin', 'tags', 'finder', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '448', 'plg_twofactorauth_totp', 'plugin', 'totp', 'twofactorauth', '0', '0', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '449', 'plg_authentication_cookie', 'plugin', 'cookie', 'authentication', '0', '1', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '450', 'plg_twofactorauth_yubikey', 'plugin', 'yubikey', 'twofactorauth', '0', '0', '1', '0', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '451', 'plg_search_tags', 'plugin', 'tags', 'search', '0', '1', '1', '0', '', '{"search_limit":"50","show_tagged_items":"1"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '503', 'beez3', 'template', 'beez3', '', '0', '1', '1', '0', '', '{"wrapperSmall":"53","wrapperLarge":"72","sitetitle":"","sitedescription":"","navposition":"center","templatecolor":"nature"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '504', 'hathor', 'template', 'hathor', '', '1', '1', '1', '0', '', '{"showSiteName":"0","colourChoice":"0","boldText":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '506', 'protostar', 'template', 'protostar', '', '0', '1', '1', '0', '', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '507', 'isis', 'template', 'isis', '', '1', '1', '1', '0', '', '{"templateColor":"","logoFile":""}', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '600', 'English (United Kingdom)', 'language', 'en-GB', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '601', 'English (United Kingdom)', 'language', 'en-GB', '', '1', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
      UNION SELECT '700', 'files_joomla', 'file', 'joomla', '', '0', '1', '1', '1', '', '', '', '', '0', '0000-00-00 00:00:00', '0', '0'
;

-- Table data for table #__finder_taxonomy

INSERT INTO #__finder_taxonomy
  SELECT '1' AS id, '0' AS parent_id, 'ROOT' AS title, '0' AS state, '0' AS access, '0' AS ordering
;

-- Table data for table #__finder_terms_common

INSERT INTO #__finder_terms_common
  SELECT 'a' AS term, 'en' AS language
UNION SELECT 'about', 'en'
      UNION SELECT 'after', 'en'
      UNION SELECT 'ago', 'en'
      UNION SELECT 'all', 'en'
      UNION SELECT 'am', 'en'
      UNION SELECT 'an', 'en'
      UNION SELECT 'and', 'en'
      UNION SELECT 'ani', 'en'
      UNION SELECT 'any', 'en'
      UNION SELECT 'are', 'en'
      UNION SELECT 'aren''t', 'en'
      UNION SELECT 'as', 'en'
      UNION SELECT 'at', 'en'
      UNION SELECT 'be', 'en'
      UNION SELECT 'but', 'en'
      UNION SELECT 'by', 'en'
      UNION SELECT 'for', 'en'
      UNION SELECT 'from', 'en'
      UNION SELECT 'get', 'en'
      UNION SELECT 'go', 'en'
      UNION SELECT 'how', 'en'
      UNION SELECT 'if', 'en'
      UNION SELECT 'in', 'en'
      UNION SELECT 'into', 'en'
      UNION SELECT 'is', 'en'
      UNION SELECT 'isn''t', 'en'
      UNION SELECT 'it', 'en'
      UNION SELECT 'its', 'en'
      UNION SELECT 'me', 'en'
      UNION SELECT 'more', 'en'
      UNION SELECT 'most', 'en'
      UNION SELECT 'must', 'en'
      UNION SELECT 'my', 'en'
      UNION SELECT 'new', 'en'
      UNION SELECT 'no', 'en'
      UNION SELECT 'none', 'en'
      UNION SELECT 'not', 'en'
      UNION SELECT 'noth', 'en'
      UNION SELECT 'nothing', 'en'
      UNION SELECT 'of', 'en'
      UNION SELECT 'off', 'en'
      UNION SELECT 'often', 'en'
      UNION SELECT 'old', 'en'
      UNION SELECT 'on', 'en'
      UNION SELECT 'onc', 'en'
      UNION SELECT 'once', 'en'
      UNION SELECT 'onli', 'en'
      UNION SELECT 'only', 'en'
      UNION SELECT 'or', 'en'
      UNION SELECT 'other', 'en'
      UNION SELECT 'our', 'en'
      UNION SELECT 'ours', 'en'
      UNION SELECT 'out', 'en'
      UNION SELECT 'over', 'en'
      UNION SELECT 'page', 'en'
      UNION SELECT 'she', 'en'
      UNION SELECT 'should', 'en'
      UNION SELECT 'small', 'en'
      UNION SELECT 'so', 'en'
      UNION SELECT 'some', 'en'
      UNION SELECT 'than', 'en'
      UNION SELECT 'thank', 'en'
      UNION SELECT 'that', 'en'
      UNION SELECT 'the', 'en'
      UNION SELECT 'their', 'en'
      UNION SELECT 'theirs', 'en'
      UNION SELECT 'them', 'en'
      UNION SELECT 'then', 'en'
      UNION SELECT 'there', 'en'
      UNION SELECT 'these', 'en'
      UNION SELECT 'they', 'en'
      UNION SELECT 'this', 'en'
      UNION SELECT 'those', 'en'
      UNION SELECT 'thus', 'en'
      UNION SELECT 'time', 'en'
      UNION SELECT 'times', 'en'
      UNION SELECT 'to', 'en'
      UNION SELECT 'too', 'en'
      UNION SELECT 'true', 'en'
      UNION SELECT 'under', 'en'
      UNION SELECT 'until', 'en'
      UNION SELECT 'up', 'en'
      UNION SELECT 'upon', 'en'
      UNION SELECT 'use', 'en'
      UNION SELECT 'user', 'en'
      UNION SELECT 'users', 'en'
      UNION SELECT 'veri', 'en'
      UNION SELECT 'version', 'en'
      UNION SELECT 'very', 'en'
      UNION SELECT 'via', 'en'
      UNION SELECT 'want', 'en'
      UNION SELECT 'was', 'en'
      UNION SELECT 'way', 'en'
      UNION SELECT 'were', 'en'
      UNION SELECT 'what', 'en'
      UNION SELECT 'when', 'en'
      UNION SELECT 'where', 'en'
      UNION SELECT 'whi', 'en'
      UNION SELECT 'which', 'en'
      UNION SELECT 'who', 'en'
      UNION SELECT 'whom', 'en'
      UNION SELECT 'whose', 'en'
      UNION SELECT 'why', 'en'
      UNION SELECT 'wide', 'en'
      UNION SELECT 'will', 'en'
      UNION SELECT 'with', 'en'
      UNION SELECT 'within', 'en'
      UNION SELECT 'without', 'en'
      UNION SELECT 'would', 'en'
      UNION SELECT 'yes', 'en'
      UNION SELECT 'yet', 'en'
      UNION SELECT 'you', 'en'
      UNION SELECT 'your', 'en'
      UNION SELECT 'yours', 'en'
;

-- Table data for table #__languages

INSERT INTO #__languages
  SELECT '1' AS lang_id, 'en-GB' AS lang_code, 'English (UK)' AS title, 'English (UK)' AS title_native, 'en' AS sef, 'en' AS image, '' AS description, '' AS metakey, '' AS metadesc, '' AS sitename, '1' AS published, '1' AS access, '1' AS ordering
;

-- Table data for table #__menu

INSERT INTO #__menu
  SELECT '1' AS id, '' AS menutype, 'Menu_Item_Root' AS title, 'root' AS alias, '' AS note, '' AS path, '' AS link, '' AS type, '1' AS published, '0' AS parent_id, '0' AS level, '0' AS component_id, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '0' AS browserNav, '0' AS access, '' AS img, '0' AS template_style_id, '' AS params, '0' AS lft, '49' AS rgt, '0' AS home, '*' AS language, '0' AS client_id
UNION SELECT '2', 'menu', 'com_banners', 'Banners', '', 'Banners', 'index.php?option=com_banners', 'component', '0', '1', '1', '4', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners', '0', '', '1', '10', '0', '*', '1'
      UNION SELECT '3', 'menu', 'com_banners', 'Banners', '', 'Banners/Banners', 'index.php?option=com_banners', 'component', '0', '2', '2', '4', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners', '0', '', '2', '3', '0', '*', '1'
      UNION SELECT '4', 'menu', 'com_banners_categories', 'Categories', '', 'Banners/Categories', 'index.php?option=com_categories&extension=com_banners', 'component', '0', '2', '2', '6', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners-cat', '0', '', '4', '5', '0', '*', '1'
      UNION SELECT '5', 'menu', 'com_banners_clients', 'Clients', '', 'Banners/Clients', 'index.php?option=com_banners&view=clients', 'component', '0', '2', '2', '4', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners-clients', '0', '', '6', '7', '0', '*', '1'
      UNION SELECT '6', 'menu', 'com_banners_tracks', 'Tracks', '', 'Banners/Tracks', 'index.php?option=com_banners&view=tracks', 'component', '0', '2', '2', '4', '0', '0000-00-00 00:00:00', '0', '0', 'class:banners-tracks', '0', '', '8', '9', '0', '*', '1'
      UNION SELECT '7', 'menu', 'com_contact', 'Contacts', '', 'Contacts', 'index.php?option=com_contact', 'component', '0', '1', '1', '8', '0', '0000-00-00 00:00:00', '0', '0', 'class:contact', '0', '', '11', '16', '0', '*', '1'
      UNION SELECT '8', 'menu', 'com_contact', 'Contacts', '', 'Contacts/Contacts', 'index.php?option=com_contact', 'component', '0', '7', '2', '8', '0', '0000-00-00 00:00:00', '0', '0', 'class:contact', '0', '', '12', '13', '0', '*', '1'
      UNION SELECT '9', 'menu', 'com_contact_categories', 'Categories', '', 'Contacts/Categories', 'index.php?option=com_categories&extension=com_contact', 'component', '0', '7', '2', '6', '0', '0000-00-00 00:00:00', '0', '0', 'class:contact-cat', '0', '', '14', '15', '0', '*', '1'
      UNION SELECT '10', 'menu', 'com_messages', 'Messaging', '', 'Messaging', 'index.php?option=com_messages', 'component', '0', '1', '1', '15', '0', '0000-00-00 00:00:00', '0', '0', 'class:messages', '0', '', '17', '22', '0', '*', '1'
      UNION SELECT '11', 'menu', 'com_messages_add', 'New Private Message', '', 'Messaging/New Private Message', 'index.php?option=com_messages&task=message.add', 'component', '0', '10', '2', '15', '0', '0000-00-00 00:00:00', '0', '0', 'class:messages-add', '0', '', '18', '19', '0', '*', '1'
      UNION SELECT '12', 'menu', 'com_messages_read', 'Read Private Message', '', 'Messaging/Read Private Message', 'index.php?option=com_messages', 'component', '0', '10', '2', '15', '0', '0000-00-00 00:00:00', '0', '0', 'class:messages-read', '0', '', '20', '21', '0', '*', '1'
      UNION SELECT '13', 'menu', 'com_newsfeeds', 'News Feeds', '', 'News Feeds', 'index.php?option=com_newsfeeds', 'component', '0', '1', '1', '17', '0', '0000-00-00 00:00:00', '0', '0', 'class:newsfeeds', '0', '', '23', '28', '0', '*', '1'
      UNION SELECT '14', 'menu', 'com_newsfeeds_feeds', 'Feeds', '', 'News Feeds/Feeds', 'index.php?option=com_newsfeeds', 'component', '0', '13', '2', '17', '0', '0000-00-00 00:00:00', '0', '0', 'class:newsfeeds', '0', '', '24', '25', '0', '*', '1'
      UNION SELECT '15', 'menu', 'com_newsfeeds_categories', 'Categories', '', 'News Feeds/Categories', 'index.php?option=com_categories&extension=com_newsfeeds', 'component', '0', '13', '2', '6', '0', '0000-00-00 00:00:00', '0', '0', 'class:newsfeeds-cat', '0', '', '26', '27', '0', '*', '1'
      UNION SELECT '16', 'menu', 'com_redirect', 'Redirect', '', 'Redirect', 'index.php?option=com_redirect', 'component', '0', '1', '1', '24', '0', '0000-00-00 00:00:00', '0', '0', 'class:redirect', '0', '', '29', '30', '0', '*', '1'
      UNION SELECT '17', 'menu', 'com_search', 'Basic Search', '', 'Basic Search', 'index.php?option=com_search', 'component', '0', '1', '1', '19', '0', '0000-00-00 00:00:00', '0', '0', 'class:search', '0', '', '31', '32', '0', '*', '1'
      UNION SELECT '18', 'menu', 'com_weblinks', 'Weblinks', '', 'Weblinks', 'index.php?option=com_weblinks', 'component', '0', '1', '1', '21', '0', '0000-00-00 00:00:00', '0', '0', 'class:weblinks', '0', '', '33', '38', '0', '*', '1'
      UNION SELECT '19', 'menu', 'com_weblinks_links', 'Links', '', 'Weblinks/Links', 'index.php?option=com_weblinks', 'component', '0', '18', '2', '21', '0', '0000-00-00 00:00:00', '0', '0', 'class:weblinks', '0', '', '34', '35', '0', '*', '1'
      UNION SELECT '20', 'menu', 'com_weblinks_categories', 'Categories', '', 'Weblinks/Categories', 'index.php?option=com_categories&extension=com_weblinks', 'component', '0', '18', '2', '6', '0', '0000-00-00 00:00:00', '0', '0', 'class:weblinks-cat', '0', '', '36', '37', '0', '*', '1'
      UNION SELECT '21', 'menu', 'com_finder', 'Smart Search', '', 'Smart Search', 'index.php?option=com_finder', 'component', '0', '1', '1', '27', '0', '0000-00-00 00:00:00', '0', '0', 'class:finder', '0', '', '39', '40', '0', '*', '1'
      UNION SELECT '22', 'menu', 'com_joomlaupdate', 'Joomla! Update', '', 'Joomla! Update', 'index.php?option=com_joomlaupdate', 'component', '1', '1', '1', '28', '0', '0000-00-00 00:00:00', '0', '0', 'class:joomlaupdate', '0', '', '41', '42', '0', '*', '1'
      UNION SELECT '23', 'main', 'com_tags', 'Tags', '', 'Tags', 'index.php?option=com_tags', 'component', '0', '1', '1', '29', '0', '0000-00-00 00:00:00', '0', '1', 'class:tags', '0', '', '43', '44', '0', '', '1'
      UNION SELECT '24', 'main', 'com_postinstall', 'Post-installation messages', '', 'Post-installation messages', 'index.php?option=com_postinstall', 'component', '0', '1', '1', '32', '0', '0000-00-00 00:00:00', '0', '1', 'class:postinstall', '0', '', '45', '46', '0', '*', '1'
      UNION SELECT '101', 'mainmenu', 'Home', 'home', '', 'home', 'index.php?option=com_content&view=featured', 'component', '1', '1', '1', '22', '0', '0000-00-00 00:00:00', '0', '1', '', '0', '{"featured_categories":[""],"layout_type":"blog","num_leading_articles":"1","num_intro_articles":"3","num_columns":"3","num_links":"0","multi_column_order":"1","orderby_pri":"","orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1","show_title":"","link_titles":"","show_intro":"","info_block_position":"","show_category":"","link_category":"","show_parent_category":"","link_parent_category":"","show_author":"","link_author":"","show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"","show_readmore":"","show_readmore_title":"","show_icons":"","show_print_icon":"","show_email_icon":"","show_hits":"","show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_text":1,"page_title":"","show_page_heading":1,"page_heading":"","pageclass_sfx":"","menu-meta_description":"","menu-meta_keywords":"","robots":"","secure":0}', '47', '48', '1', '*', '0'
;

-- Table data for table #__menu_types

INSERT INTO #__menu_types
  SELECT '1' AS id, 'mainmenu' AS menutype, 'Main Menu' AS title, 'The main menu for the site' AS description
;

-- Table data for table #__modules

INSERT INTO #__modules
  SELECT '1' AS id, '55' AS asset_id, 'Main Menu' AS title, '' AS note, '' AS content, '1' AS ordering, 'position-7' AS position, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '0000-00-00 00:00:00' AS publish_up, '0000-00-00 00:00:00' AS publish_down, '1' AS published, 'mod_menu' AS module, '1' AS access, '1' AS showtitle, '{"menutype":"mainmenu","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"","layout":"","moduleclass_sfx":"_menu","cache":"1","cache_time":"900","cachemode":"itemid"}' AS params, '0' AS client_id, '*' AS language
UNION SELECT '2', '56', 'Login', '', '', '1', 'login', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_login', '1', '1', '', '1', '*'
      UNION SELECT '3', '57', 'Popular Articles', '', '', '3', 'cpanel', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_popular', '3', '1', '{"count":"5","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', '1', '*'
      UNION SELECT '4', '58', 'Recently Added Articles', '', '', '4', 'cpanel', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_latest', '3', '1', '{"count":"5","ordering":"c_dsc","catid":"","user_id":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', '1', '*'
      UNION SELECT '8', '59', 'Toolbar', '', '', '1', 'toolbar', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_toolbar', '3', '1', '', '1', '*'
      UNION SELECT '9', '60', 'Quick Icons', '', '', '1', 'icon', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_quickicon', '3', '1', '', '1', '*'
      UNION SELECT '10', '61', 'Logged-in Users', '', '', '2', 'cpanel', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_logged', '3', '1', '{"count":"5","name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0","automatic_title":"1"}', '1', '*'
      UNION SELECT '12', '62', 'Admin Menu', '', '', '1', 'menu', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_menu', '3', '1', '{"layout":"","moduleclass_sfx":"","shownew":"1","showhelp":"1","cache":"0"}', '1', '*'
      UNION SELECT '13', '63', 'Admin Submenu', '', '', '1', 'submenu', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_submenu', '3', '1', '', '1', '*'
      UNION SELECT '14', '64', 'User Status', '', '', '2', 'status', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_status', '3', '1', '', '1', '*'
      UNION SELECT '15', '65', 'Title', '', '', '1', 'title', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_title', '3', '1', '', '1', '*'
      UNION SELECT '16', '66', 'Login Form', '', '', '7', 'position-7', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_login', '1', '1', '{"greeting":"1","name":"0"}', '0', '*'
      UNION SELECT '17', '67', 'Breadcrumbs', '', '', '1', 'position-2', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_breadcrumbs', '1', '1', '{"moduleclass_sfx":"","showHome":"1","homeText":"","showComponent":"1","separator":"","cache":"1","cache_time":"900","cachemode":"itemid"}', '0', '*'
      UNION SELECT '79', '68', 'Multilanguage status', '', '', '1', 'status', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0', 'mod_multilangstatus', '3', '1', '{"layout":"_:default","moduleclass_sfx":"","cache":"0"}', '1', '*'
      UNION SELECT '86', '69', 'Joomla Version', '', '', '1', 'footer', '0', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '1', 'mod_version', '3', '1', '{"format":"short","product":"1","layout":"_:default","moduleclass_sfx":"","cache":"0"}', '1', '*'
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
      UNION SELECT '86', '0'
;

-- Table data for table #__postinstall_messages

INSERT INTO #__postinstall_messages
  SELECT '1' AS postinstall_message_id, '700' AS extension_id, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_TITLE' AS title_key, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_BODY' AS description_key, 'PLG_TWOFACTORAUTH_TOTP_POSTINSTALL_ACTION' AS action_key, 'plg_twofactorauth_totp' AS language_extension, '1' AS language_client_id, 'action' AS type, 'site://plugins/twofactorauth/totp/postinstall/actions.php' AS action_file, 'twofactorauth_postinstall_action' AS action, 'site://plugins/twofactorauth/totp/postinstall/actions.php' AS condition_file, 'twofactorauth_postinstall_condition' AS condition_method, '3.2.0' AS version_introduced, '1' AS enabled
UNION SELECT '2', '700', 'COM_CPANEL_MSG_EACCELERATOR_TITLE', 'COM_CPANEL_MSG_EACCELERATOR_BODY', 'COM_CPANEL_MSG_EACCELERATOR_BUTTON', 'com_cpanel', '1', 'action', 'admin://components/com_admin/postinstall/eaccelerator.php', 'admin_postinstall_eaccelerator_action', 'admin://components/com_admin/postinstall/eaccelerator.php', 'admin_postinstall_eaccelerator_condition', '3.2.0', '1'
      UNION SELECT '3', '700', 'COM_CPANEL_WELCOME_BEGINNERS_TITLE', 'COM_CPANEL_WELCOME_BEGINNERS_MESSAGE', '', 'com_cpanel', '1', 'message', '', '', '', '', '3.2.0', '1'
      UNION SELECT '4', '700', 'COM_CPANEL_MSG_PHPVERSION_TITLE', 'COM_CPANEL_MSG_PHPVERSION_BODY', '', 'com_cpanel', '1', 'message', '', '', 'admin://components/com_admin/postinstall/phpversion.php', 'admin_postinstall_phpversion_condition', '3.2.2', '1'
;

-- Table data for table #__tags

INSERT INTO #__tags
  SELECT '1' AS id, '0' AS parent_id, '0' AS lft, '1' AS rgt, '0' AS level, '' AS path, 'ROOT' AS title, 'root' AS alias, '' AS note, '' AS description, '1' AS published, '0' AS checked_out, '0000-00-00 00:00:00' AS checked_out_time, '1' AS access, '' AS params, '' AS metadesc, '' AS metakey, '' AS metadata, '0' AS created_user_id, '2011-01-01 00:00:01' AS created_time, '' AS created_by_alias, '0' AS modified_user_id, '0000-00-00 00:00:00' AS modified_time, '' AS images, '' AS urls, '0' AS hits, '*' AS language, '1' AS version, '0000-00-00 00:00:00' AS publish_up, '0000-00-00 00:00:00' AS publish_down
;

-- Table data for table #__template_styles

INSERT INTO #__template_styles
  SELECT '4' AS id, 'beez3' AS template, '0' AS client_id, '0' AS home, 'Beez3 - Default' AS title, '{"wrapperSmall":"53","wrapperLarge":"72","logo":"images\/joomla_black.gif","sitetitle":"Joomla!","sitedescription":"Open Source Content Management","navposition":"left","templatecolor":"personal","html5":"0"}' AS params
UNION SELECT '5', 'hathor', '1', '0', 'Hathor - Default', '{"showSiteName":"0","colourChoice":"","boldText":"0"}'
      UNION SELECT '7', 'protostar', '0', '1', 'protostar - Default', '{"templateColor":"","logoFile":"","googleFont":"1","googleFontName":"Open+Sans","fluidContainer":"0"}'
      UNION SELECT '8', 'isis', '1', '1', 'isis - Default', '{"templateColor":"","logoFile":""}'
;

-- Table data for table #__update_sites

INSERT INTO #__update_sites
  SELECT '1' AS update_site_id, 'Joomla! Core' AS name, 'collection' AS type, 'http://update.joomla.org/core/list.xml' AS location, '1' AS enabled, '0' AS last_check_timestamp, '' AS extra_query
UNION SELECT '2', 'Joomla! Extension Directory', 'collection', 'http://update.joomla.org/jed/list.xml', '1', '0', ''
      UNION SELECT '3', 'Accredited Joomla! Translations', 'collection', 'http://update.joomla.org/language/translationlist_3.xml', '1', '0', ''
;

-- Table data for table #__update_sites_extensions

INSERT INTO #__update_sites_extensions
  SELECT '1' AS update_site_id, '700' AS extension_id
UNION SELECT '2', '700'
      UNION SELECT '3', '600'
;

-- Table data for table #__usergroups

INSERT INTO #__usergroups
  SELECT '1' AS id, '0' AS parent_id, '1' AS lft, '18' AS rgt, 'Public' AS title
UNION SELECT '2', '1', '8', '15', 'Registered'
      UNION SELECT '3', '2', '9', '14', 'Author'
      UNION SELECT '4', '3', '10', '13', 'Editor'
      UNION SELECT '5', '4', '11', '12', 'Publisher'
      UNION SELECT '6', '1', '4', '7', 'Manager'
      UNION SELECT '7', '6', '5', '6', 'Administrator'
      UNION SELECT '8', '1', '16', '17', 'Super Users'
      UNION SELECT '9', '1', '2', '3', 'Guest'
;

-- Table data for table #__viewlevels

INSERT INTO #__viewlevels
  SELECT '1' AS id, 'Public' AS title, '0' AS ordering, '[1]' AS rules
UNION SELECT '2', 'Registered', '1', '[6,2,8]'
      UNION SELECT '3', 'Special', '2', '[6,3,8]'
      UNION SELECT '5', 'Guest', '0', '[9]'
      UNION SELECT '6', 'Super Users', '0', '[8]'
;
