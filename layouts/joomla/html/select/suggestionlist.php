<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $baseIndent  The base indentation
 * @var  string   $id          The extension id
 * @var  string   $name        The suggestion list name
 * @var  string   $options     The suggestion list options
 * @var  string   $data        The suggestion list data
 */

extract($displayData);


echo $baseIndent . '<datalist' . $id . '>' . $options['format.eol']
. JHtml::_('select.options', $data, $options) . $baseIndent . '</datalist>' . $options['format.eol'];
