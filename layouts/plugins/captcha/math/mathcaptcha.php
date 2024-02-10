<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Captcha.match
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string       $name           Name of the input field.
 * @var   array        $attributes     List of input attributes
 * @var   string       $formula        Formula
 * @var   integer      $inputIdx       Active Input index
 * @var   Document     $document       Document instance
 * @var   FileLayout   $this           Context
 */

$id    = $attributes['id'] ?? '';
$class = str_replace(' required', '', ($attributes['class'] ?? ''));

$letters = 'abcdefghijklmnopqrstuvwxyz';
$classes = [
    substr(str_shuffle($letters), 0, 2) . bin2hex(random_bytes(rand(2, 7))),
    substr(str_shuffle($letters), 0, 2) . bin2hex(random_bytes(rand(2, 7))),
    substr(str_shuffle($letters), 0, 2) . bin2hex(random_bytes(rand(2, 7))),
];

$styles = '';
foreach ($classes as $i => $c) {
    $styles .= '.' . $c . '{display:' . ($i !== $inputIdx ? 'none' : 'block') . '}';
}

$document->getWebAssetManager()->addInlineStyle($styles, ['name' => 'inline.plg_captcha_math'])
?>
<div class="input-group mb-3">
    <span class="input-group-text"><?php echo Text::_('PLG_CAPTCHA_MATH_ENTER_SOLUTION') ?> <?php echo $this->escape($formula); ?></span>
    <input type="text" value="" size="4" name="<?php echo $name; ?>[]" id="<?php echo $id; ?>" class="form-control <?php echo $classes[0] . ' ' . $class; ?>"/>
    <input type="text" value="" size="4" name="<?php echo $name; ?>[]" id="<?php echo $id; ?>1" class="form-control <?php echo $classes[1] . ' ' . $class; ?>"/>
    <input type="text" value="" size="4" name="<?php echo $name; ?>[]" id="<?php echo $id; ?>2" class="form-control <?php echo $classes[2] . ' ' . $class; ?>"/>
</div>
