<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Cpatcha.match
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string       $name           Name of the input field.
 * @var   array        $attributes     List of input attributes
 * @var   string       $formula        Formula
 * @var   FileLayout   $this           Context
 */

$id    = $attributes['id'] ?? '';
$class = $attributes['class'] ?? '';

?>
<div class="input-group mb-3">
    <span class="input-group-text">Enter solution for: <b>&nbsp;<?php echo $formula; ?></b></span>
    <input type="text" value="" size="5" name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="form-control <?php echo $class; ?>"/>
</div>
