<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// No direct access.
defined('_JEXEC') or die;

// Class map of the core extensions
JLoader::registerAlias('ActionLogPlugin', '\\Joomla\\Component\\Actionlogs\\Administrator\\Plugin\\ActionLogPlugin', '5.0');

JLoader::registerAlias('FieldsPlugin', '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsPlugin', '5.0');
JLoader::registerAlias('FieldsListPlugin', '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsListPlugin', '5.0');

JLoader::registerAlias('PrivacyExportDomain', '\\Joomla\\Component\\Privacy\\Administrator\\Export\\Domain', '5.0');
JLoader::registerAlias('PrivacyExportField', '\\Joomla\\Component\\Privacy\\Administrator\\Export\\Field', '5.0');
JLoader::registerAlias('PrivacyExportItem', '\\Joomla\\Component\\Privacy\\Administrator\\Export\\Item', '5.0');
JLoader::registerAlias('PrivacyPlugin', '\\Joomla\\Component\\Privacy\\Administrator\\Plugin\\PrivacyPlugin', '5.0');
JLoader::registerAlias('PrivacyRemovalStatus', '\\Joomla\\Component\\Privacy\\Administrator\\Removal\\Status', '5.0');
JLoader::registerAlias('PrivacyTableRequest', '\\Joomla\\Component\\Privacy\\Administrator\\Table\\RequestTable', '5.0');

JLoader::registerAlias('TagsTableTag', '\\Joomla\\Component\\Tags\\Administrator\\Table\\TagTable', '5.0');

JLoader::registerAlias('ContentHelperRoute', '\\Joomla\\Component\\Content\\Site\\Helper\\RouteHelper', '5.0');

JLoader::registerAlias('FinderIndexerAdapter', '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Adapter', '5.0');
JLoader::registerAlias('FinderIndexerHelper', '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Helper', '5.0');
JLoader::registerAlias('FinderIndexer', '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Indexer', '5.0');
JLoader::registerAlias('FinderIndexerParser', '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Parser', '5.0');
JLoader::registerAlias('FinderIndexerQuery', '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Query', '5.0');
JLoader::registerAlias('FinderIndexerResult', '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Result', '5.0');
JLoader::registerAlias('FinderIndexerTaxonomy', '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Taxonomy', '5.0');
JLoader::registerAlias('FinderIndexerToken', '\\Joomla\\Component\\Finder\\Administrator\\Indexer\\Token', '5.0');
