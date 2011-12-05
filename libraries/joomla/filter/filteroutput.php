<?php

/**
 * @deprecated       12.3
 */
defined('JPATH_PLATFORM') or die;

JLog::add('JFilterOutput has moved to jimport(\'joomla.filter.output\'), please update your code.', JLog::WARNING, 'deprecated');

require_once __DIR__ . '/output.php';

