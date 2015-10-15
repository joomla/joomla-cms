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
 * @var  string  $baseIndent  The base indentation
 * @var  string  $id          The tag id
 * @var  string  $name        The tag name
 * @var  string  $options     The tag options
 * @var  string  $data        The tag data
 */

extract($displayData);

?>
<?php echo $baseIndent; ?><select<?php echo ($id !== '' ? ' id="' . $id . '"' : ''); ?> name="<?php echo $name; ?>"<?php echo $attribs; ?>><?php echo $options['format.eol']; ?>
<?php echo JHtml::_('select.options', $data, $options) . $baseIndent; ?></select><?php echo $options['format.eol'];
