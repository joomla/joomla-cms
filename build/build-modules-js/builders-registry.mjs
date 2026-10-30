/**
 * Builders registry for Media resources
 */

// List of media resources. A folder (extension) name under media_source/
// By default, is used DefaultModuleBuilder class.
// However, each resource may have own builder.mjs script, for this it should be placed in root of its folder.
export const builders = [
  // Libraries
  'vendor', // Many extensions depending on it, should be run first
  'system',
  'vendor/bootstrap', // Customised bootstrap
  'vendor/jquery', // jQuery extras
  'vendor/short-and-sweet', // Customised short-and-sweet
  'layouts',
  'legacy',
  'mailto',

  // Components
  'cache',
  'com_actionlogs',
  'com_admin',
  'com_associations',
  'com_banners',
  'com_cache',
  'com_categories',
  'com_config',
  'com_contact',
  'com_content',
  'com_contenthistory',
  'com_cpanel',
  'com_fields',
  'com_finder',
  'com_guidedtours',
  'com_installer',
  'com_joomlaupdate',
  'com_languages',
  'com_mails',
  'com_media',
  'com_menus',
  'com_modules',
  'com_scheduler',
  'com_tags',
  'com_templates',
  'com_users',
  'com_workflow',
  'com_wrapper',

  // Modules
  'mod_articles',
  'mod_articles_news',
  'mod_languages',
  'mod_login',
  'mod_menu',
  'mod_quickicon',
  'mod_sampledata',

  // Plugins
  'plg_behaviour_compat6',
  'plg_content_vote',
  'plg_editors-xtd_image',
  'plg_editors_codemirror',
  'plg_editors_none',
  'plg_editors_tinymce',
  'plg_installer_folderinstaller',
  'plg_installer_packageinstaller',
  'plg_installer_urlinstaller',
  'plg_installer_webinstaller',
  'plg_media-action_crop',
  'plg_media-action_resize',
  'plg_media-action_rotate',
  'plg_multifactorauth_email',
  'plg_multifactorauth_fixed',
  'plg_multifactorauth_totp',
  'plg_multifactorauth_webauthn',
  'plg_multifactorauth_yubikey',
  'plg_quickicon_autoupdate',
  'plg_quickicon_eos',
  'plg_quickicon_extensionupdate',
  'plg_quickicon_joomlaupdate',
  'plg_quickicon_overridecheck',
  'plg_quickicon_privacycheck',
  'plg_system_debug',
  'plg_system_guidedtours',
  'plg_system_jooa11y',
  'plg_system_schedulerunner',
  'plg_system_shortcut',
  'plg_system_stats',
  'plg_system_webauthn',
  'plg_user_token',

  // Templates
  'templates/administrator/atum',
  'templates/site/cassiopeia',
  'templates/site/cassiopeia_extended',
  'installation/template',

  // Additional builders, which are not distributed under media/
  'error-pages',
];

// Builders which should be completed before any following builder starts.
// Used for mass-execution to prevent collisions.
export const blockingBuilders = [
  'vendor', // Blocking many extensions depending on it
  'system', // Blocking because 'error-pages' writes in to the same folder, so 'system' should be completed before that
];
