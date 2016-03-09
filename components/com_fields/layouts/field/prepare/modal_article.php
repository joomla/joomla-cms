<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (! key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$value = $field->value;
if (! $value)
{
	return;
}

JLoader::import('joomla.application.component.model');
JModelLegacy::addIncludePath(JPATH_BASE . '/components/com_content/models', 'ContentModel');
$model = JModelLegacy::getInstance('Article', 'ContentModel');

// If the article is not found an error is thrown we need to hold the
// old error handler
$errorHandler = JError::getErrorHandling(E_ERROR);

// Ignoring all errors
JError::setErrorHandling(E_ERROR, 'ignore');

// Fetching the article
$article = $model->getItem($value);

// Restoreing the old error handler
JError::setErrorHandling(E_ERROR, $errorHandler['mode'], $errorHandler['options']);

if ($article instanceof JException)
{
	return;
}

echo htmlentities($article->title);
