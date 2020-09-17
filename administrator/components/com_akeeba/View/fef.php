<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

?>

<div style="margin: 1em">
	<h1>Akeeba Frontend Framework (FEF) could not be found on this site</h1>
	<hr/>
	<div class="alert alert-warning">
		<h2>
			This component requires the Akeeba Frontend Framework (FEF) to be installed on your site. Please go to <a
					href="https://www.akeeba.com/download/official/fef.html">our download page</a> to download it, then install it on your site.
		</h2>
	</div>
	<hr/>
	<h4>Further information</h4>
	<p>
        FEF is the name of our custom CSS framework. It's responsible for rendering the interface of our Joomla!
		extensions. It is automatically installed when you install our extensions on your site.
	</p>
	<p>
		FEF can be missing from your site either because Joomla failed to install it or because you, another Super User,
		or another extension mistakenly uninstalled it.
	</p>
	<p>
		If it's missing we cannot display the interface to this component. That's why you see this message.
	</p>
	<p>
		You do not have to worry about adding bloat to your site. FEF is very small. It will also be automatically
		uninstalled when you uninstall all components which depend on it.
	</p>
	<p>
		FEF is installed in the <code>media/fef</code> folder under your site's root. It appears in Joomla's Extensions,
		Manage page as <code>file_fef</code>. Please do not remove it from your site.
	</p>
</div>
