<?php defined('_JEXEC') or die('Restricted access'); ?>

<pre>
	<?php
	// Strip php tag
	$changelog = preg_replace('/\<\?php[^\?]*\?\>/','',$this->changelog);

	// Convert all other HTML entities
	echo htmlentities($changelog);
	?>
</pre>