<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @var \Composer\Autoload\ClassLoader $autoloader
 */

$autoLoader->addPsr4('Joomla\\Component\\Content\\Administrator\\', JPATH_ADMINISTRATOR . '/components/com_content');

JLoader::registerAlias('ContentHelper' , '\\Joomla\\Component\\Content\\Administrator\\Helper\\ContentHelper');
