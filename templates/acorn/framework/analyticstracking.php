<?php
	/**
	 * @package     acorn.Framework
	 * @subpackage  gtag.js Global site tag (gtag.js) - Google Analytics
	 *
	 * @copyright   Copyright (C) 2015 Troy T. Hall All rights reserved.
	 * @license     GNU General Public License version 2 or later; see LICENSE.txt
	 */
	defined('_JEXEC') or die;
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $gaId; ?>"></script>
<script>
    "use strict";
	window.dataLayer = window.dataLayer || [];
	function gtag() {
		dataLayer.push(arguments);
	}
	gtag('js', new Date());

    gtag('config', '<?php echo $gaId; ?>');
</script>

