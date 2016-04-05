<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die

if (!key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$value = $field->value;

if (!$value)
{
	return;
}

JLoader::import('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_BASE . '/components/com_content/models', 'ContentModel');
$model = JModelLegacy::getInstance('Article', 'ContentModel');

// If the article is not found an error is thrown so we need to hold the
// old error handler
$errorHandler = JError::getErrorHandling(E_ERROR);

// Ignoring all errors
JError::setErrorHandling(E_ERROR, 'ignore');

// Fetching the article
$article = $model->getItem($value);

// Restoring the old error handler
JError::setErrorHandling(E_ERROR, $errorHandler['mode'], $errorHandler['options']);

if ($article instanceof JException)
{
	return;
}

echo htmlentities($article->title);
