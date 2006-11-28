<?php
include_once('../simplepie.inc');
include_once('../idn/idna_convert.class.php');

// Parse it
$feed = new SimplePie();
if (!empty($_GET['feed'])) {
	$_GET['feed'] = stripslashes($_GET['feed']);
	$feed->feed_url($_GET['feed']);
	$feed->enable_caching(false);
	if (isset($_GET['xmldump'])) $feed->enable_xmldump($_GET['xmldump']);
	$starttime = explode(' ', microtime());
	$starttime = $starttime[1] + $starttime[0];
	$feed->init();
	$endtime = explode(' ', microtime());
	$endtime = $endtime[1] + $endtime[0];
	$time = $endtime - $starttime;
} else {
	$time = 'null';
}

$feed->handle_content_type();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<title>SimplePie Test</title>
<p>Parsed in: <?php echo $time; ?></p>
<pre>
<?php

// Output buffer
function callable_htmlspecialchars($string)
{
	return htmlspecialchars($string);
}
ob_start('callable_htmlspecialchars');

// Output
print_r($feed);
ob_end_flush();

?>
</pre>