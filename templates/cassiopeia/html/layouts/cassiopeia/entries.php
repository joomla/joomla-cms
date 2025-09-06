<?php

/**
 * @package     Joomla.Site
 * @subpackage  Templates.cassiopeia
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') || die;

use Joomla\Utilities\ArrayHelper;

extract($displayData);
?>
<!DOCTYPE html>
<html <?php echo ArrayHelper::toString($doc->params->htmlTagAttributes); ?>>

<head>
    <jdoc:include type="metas" />
    <jdoc:include type="styles" />
    <jdoc:include type="scripts" />
</head>

<body <?php echo ArrayHelper::toString($doc->params->bodyTagAttributes); ?>>
    <?php echo $entry !== 'component' ? $this->sublayout('_header', ['doc' => & $doc, 'entry' => $entry]) : ''; ?>
    <?php echo $this->sublayout($entry, ['doc' => & $doc, 'entry' => $entry]); ?>
</body>

</html>
