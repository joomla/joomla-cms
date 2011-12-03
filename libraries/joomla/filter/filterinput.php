<?php

/**
 * @deprecated       12.3
 */
defined('JPATH_PLATFORM') or die;

JLog::add('JFilterInput has moved to jimport(\'joomla.filter.input\'), please update your code.', JLog::WARNING, 'deprecated');

require_once __DIR__ . '/input.php';

