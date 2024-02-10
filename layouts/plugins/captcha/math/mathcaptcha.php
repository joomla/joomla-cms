<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.match
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
    <span class="input-group-text"><?php echo Text::_('PLG_CAPTCHA_MATH_ENTER_SOLUTION') ?> <?php echo $this->escape($formula); ?></span>
    <input type="text" value="" size="5" name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="form-control <?php echo $class; ?>"/>
</div>
