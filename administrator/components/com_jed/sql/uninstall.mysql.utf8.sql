SET FOREIGN_KEY_CHECKS = 0;

/* Templates for Emails being sent from JED */
DROP TABLE IF EXISTS `#__jed_message_templates`;
DROP TABLE IF EXISTS `#__jed_ticket_categories`;
DROP TABLE IF EXISTS `#__jed_ticket_groups`;
DROP TABLE IF EXISTS `#__jed_ticket_linked_item_types`;
DROP TABLE IF EXISTS `#__jed_vel_report`;
DROP TABLE IF EXISTS `#__jed_vel_developer_update`;
DROP TABLE IF EXISTS `#__jed_vel_abandoned_report`;
DROP TABLE IF EXISTS `#__jed_vel_vulnerable_item`;
DROP TABLE IF EXISTS `#__jed_ticket_messages`;
DROP TABLE IF EXISTS `#__jed_ticket_internal_notes`;
DROP TABLE IF EXISTS `#__jed_jedtickets`;
DROP TABLE IF EXISTS `#__jed_extensions_approved_reasons`;
DROP TABLE IF EXISTS `#__jed_extensions_categories`;
DROP TABLE IF EXISTS `#__jed_extensions_logos`;
DROP TABLE IF EXISTS `#__jed_extensions_favoured`;
DROP TABLE IF EXISTS `#__jed_extensions_files`;
DROP TABLE IF EXISTS `#__jed_extensions_joomla_versions`;
DROP TABLE IF EXISTS `#__jed_extensions_notes`;
DROP TABLE IF EXISTS `#__jed_extensions_php_versions`;
DROP TABLE IF EXISTS `#__jed_extensions_published_reasons`;
DROP TABLE IF EXISTS `#__jed_extensions_related`;
DROP TABLE IF EXISTS `#__jed_extensions_status`;
DROP TABLE IF EXISTS `#__jed_extensions_types`;
DROP TABLE IF EXISTS `#__jed_extension_supply_options`;
DROP TABLE IF EXISTS `#__jed_reviews`;
DROP TABLE IF EXISTS `#__jed_reviews_comments`;
DROP TABLE IF EXISTS `#__jed_extensions`;
DROP TABLE IF EXISTS `#__jed_extension_varied_data`;
DROP TABLE IF EXISTS `#__jed_extension_images`;
DROP TABLE IF EXISTS `#__jed_extension_scores`;
DROP TABLE IF EXISTS `#__jed_developers`;

DROP TABLE IF EXISTS old_rsform5;
DROP TABLE IF EXISTS old_rsform7;
DROP TABLE IF EXISTS old_rsform9;
DROP TABLE IF EXISTS old_rsform10;
DROP TABLE IF EXISTS old_rsform11;
DROP TABLE IF EXISTS old_rsform12;
DROP TABLE IF EXISTS old_rsform13;
DROP TABLE IF EXISTS old_rsform14;
DROP TABLE IF EXISTS combine_jed_extensions;
DROP TABLE IF EXISTS master_rows;
DROP TABLE IF EXISTS combine_jed_reviews;
DROP TABLE IF EXISTS combine_jed_review_texts;


SET FOREIGN_KEY_CHECKS = 1;