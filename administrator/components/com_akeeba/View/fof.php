<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

$tooLongAgo = (int) gmdate('Y') - 2015;
?>

<div style="margin: 1em">
	<h1>Akeeba Framework-on-Framework (FOF) version 3 could not be found on this site</h1>
	<hr/>
	<div class="alert alert-warning">
		<h2>
			This component requires the Akeeba FOF framework package to be installed on your site. Please go to <a
					href="https://www.akeeba.com/download/fof3.html">our download page</a> to download it, then install it on your site.
		</h2>
	</div>
	<hr/>
	<h4>Further information</h4>
	<p>
		FOF is a Joomla component framework. It's the low level code which sits between our Joomla! extensions and
		Joomla! itself. It is automatically installed when you install our extensions on your site.
	</p>
	<p>
		FOF can be missing from your site either because Joomla failed to install it or because you, another Super User,
		or another extension mistakenly uninstalled it.
	</p>
	<p>
		If it's missing, our components cannot talk to Joomla &mdash; or vice versa. Because of that they can not run.
		That's why you see this message.
	</p>
	<p>
		You do not have to worry about adding bloat to your site. FOF is very small. It will also be automatically
		uninstalled when you uninstall all components which depend on it.
	</p>
	<p>
		FOF is installed in the <code><?php echo JPATH_LIBRARIES . DIRECTORY_SEPARATOR?>/fof30</code> folder on your
		server. It appears in Joomla's Extensions, Manage page as <code>FOF30</code>. Please do not remove it from your
		site.
	</p>
<?php if (version_compare(JVERSION, '3.9999.9999', 'le')): ?>
	<h4>Why do I have multiple FOF entries in Joomla?</h4>
	<p>
		Joomla <?php echo JVERSION ?> includes an <em>old, obsolete</em> version of FOF - version 2.x. It is installed
		in the <code><?php echo JPATH_LIBRARIES . DIRECTORY_SEPARATOR ?>fof</code> folder on your server. It appears in
		Joomla's Extensions, Manage page as <code>FOF</code>. Please do not remove it from your site; Joomla needs it
		to function properly.
	</p>
	<p>
		We discontinued FOF 2.x in 2015 &mdash; that's <?php echo $tooLongAgo ?> years ago. Ever since, we replaced it
		with FOF 3.x. The two versions are incompatible with each other but both are required; FOF 2.x for Joomla!
		itself and FOF 3.x for our extensions. That's why you see both. You must not remove either of them or something
		will break!
	</p>
<?php endif; ?>
</div>
