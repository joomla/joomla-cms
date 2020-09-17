<?php
/**
 * PHP Exception Handler
 *
 * @copyright Copyright (c) 2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var Throwable $e */
/** @var string $title */
/** @var bool $isPro */

$code = $e->getCode();
$code = !empty($code) ? $code : 500;

$app  = class_exists('\Joomla\CMS\Factory') ? \Joomla\CMS\Factory::getApplication() : \JFactory::getApplication();

$user30 = (class_exists('JFactory') && method_exists('JFactory', 'getUser')) ? JFactory::getUser() : null;
$user38 = class_exists('\Joomla\CMS\Factory') && method_exists('\Joomla\CMS\Factory', 'getUser') ? \Joomla\CMS\Factory::getUser() : null;
$user40 = (is_object($app) && method_exists($app, 'getIdentity')) ? $app->getIdentity() : null;
$user = is_null($user40) ? $user38 : $user40;
$user = is_null($user40) ? $user30 : $user;
$isSuper = !is_null($user) && $user->authorise('core.admin');

$isFrontend   = class_exists('JApplicationSite') && ($app instanceof JApplicationSite);
$isFrontend   = $isFrontend || (class_exists('\Joomla\CMS\Application\SiteApplication') && ($app instanceof \Joomla\CMS\Application\SiteApplication));
$user         = $isFrontend ? (method_exists($app, 'getIdentity') ? $app->getIdentity() : JFactory::getUser()) : null;
$hideTheError = $isFrontend && !(defined('JDEBUG') && (JDEBUG == 1)) && !$isSuper;
$isPro        = !isset($isPro) ? false : $isPro;

// 403 and 404 are re-thrown
if (in_array($code, [403, 404]))
{
	throw $e;
}

if (version_compare(JVERSION, '4', 'lt'))
{
	$app->setHeader('HTTP/1.1', $code);
}
else
{
	// In Joomla 4 we have to use the "Status" header, otherwise we get a fatal error saying that
	// HTTP/1.1 is not a valid header
	$app->setHeader('Status', $code);
}

if (!$isFrontend)
{
	if (class_exists('\Joomla\CMS\Toolbar\ToolbarHelper'))
	{
		\Joomla\CMS\Toolbar\ToolbarHelper::title($title . ' <small>Unhandled Exception</small>');
	}
	else
	{
		JToolbarHelper::title($title . ' <small>Unhandled Exception</small>');
	}

}

?>

<?php if ($hideTheError): ?>
	<h1>The application has stopped responding</h1>
	<p>
		Please contact the administrator of the site and let them know of this error and what you were doing when this
		happened.
	</p>
	<?php return true; endif; ?>

<h1><?php echo $title ?> - An unhandled Exception has been detected</h1>
<h3>
	<?php if (version_compare(JVERSION, '3.999.999', 'le')): ?>
		<span class="label label-danger"><?php echo htmlentities($code) ?></span> <?php echo htmlentities($e->getMessage()) ?>
	<?php else: ?>
		<span class="badge badge-danger"><?php echo htmlentities($code) ?></span> <?php echo htmlentities($e->getMessage()) ?>
	<?php endif; ?>
</h3>
<p>
	File <code><?php echo htmlentities(str_ireplace(JPATH_ROOT, '&lt;root&gt;', $e->getFile())) ?></code>
	Line <span class="label label-info"><?php echo (int) $e->getLine() ?></span>
</p>

<?php if ($isPro): ?>
	<div class="<?php if (version_compare(JVERSION, '3.999.999', 'le')):?>hero-unit<?php else: ?>alert alert-primary<?php endif; ?>">
		<p>
			<strong>Would you like us to help you faster?</strong>
		</p>
		<p>
			Save this page as PDF or HTML. When filing a support ticket please attach that PDF or HTML file.
		</p>
	</div>
	<p>
		<strong>Why do we need all that information?</strong> This information is an x-ray of your site at the time the
		error
		occurred. It lets us reproduce the issue or, if it's not a bug in our software, help you pinpoint the external
		reason which
		led to it.
	</p>
	<p>
		<strong>What about privacy?</strong>
		Attachments are private in our ticket system: only you and us can see them, <em>even if you file a public
			ticket</em>, and
		they are automatically deleted after a month.
	</p>
<?php endif; ?>

<hr />
<p>
	<span class="icon icon-warning-2"></span>
	<em>
		The content below this point is for developers and power users.
	</em>
</p>
<hr />

<p class="alert alert-warning">
	Joomla <?= JVERSION ?> – PHP <?= PHP_VERSION ?> on <?= PHP_OS ?>
</p>

<h3>Debug information</h3>
<p>
	Exception type: <code><?php echo htmlentities(get_class($e)) ?></code>
</p>
<pre><?php echo htmlentities($e->getTraceAsString()) ?></pre>

<h3>System information</h3>
<table class="table table-striped">
	<tr>
		<td>Operating System (reported by PHP)</td>
		<td><?php echo PHP_OS ?></td>
	</tr>
	<tr>
		<td>PHP version (as reported <em>by your server</em>)</td>
		<td><?php echo PHP_VERSION ?></td>
	</tr>
	<tr>
		<td>PHP Built On</td>
		<td><?php echo htmlentities(php_uname()); ?></td>
	</tr>
	<tr>
		<td>PHP SAPI</td>
		<td><?php echo PHP_SAPI ?></td>
	</tr>
	<tr>
		<td>Server identity</td>
		<td><?php echo htmlentities(isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : getenv('SERVER_SOFTWARE')) ?></td>
	</tr>
	<tr>
		<td>Browser identity</td>
		<td><?php echo htmlentities(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') ?></td>
	</tr>
	<tr>
		<td>Joomla! version</td>
		<td><?php echo JVERSION ?></td>
	</tr>
	<?php
	$db = JFactory::getDbo();
	if (!is_null($db)):
	?>
	<tr>
		<td>Database driver name</td>
		<td><?php echo $db->getName() ?></td>
	</tr>
	<tr>
		<td>Database driver type</td>
		<td><?php echo $db->getServerType() ?></td>
	</tr>
	<tr>
		<td>Database server version</td>
		<td><?php echo $db->getVersion() ?></td>
	</tr>
	<tr>
		<td>Database collation</td>
		<td><?php echo $db->getCollation() ?></td>
	</tr>
	<tr>
		<td>Database connection collation</td>
		<td><?php echo $db->getConnectionCollation() ?></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td>PHP Memory limit</td>
		<td><?php echo function_exists('ini_get') ? htmlentities(ini_get('memory_limit')) : 'N/A' ?></td>
	</tr>
	<tr>
		<td>Peak Memory usage</td>
		<td><?php echo function_exists('memory_get_peak_usage') ? sprintf('%0.2fM', (memory_get_peak_usage() / 1024 / 1024)) : 'N/A' ?></td>
	</tr>
	<tr>
		<td>PHP Timeout (seconds)</td>
		<td><?php echo function_exists('ini_get') ? htmlentities(ini_get('max_execution_time')) : 'N/A' ?></td>
	</tr>
</table>

<h3>Request information</h3>
<h4>$_GET</h4>
<pre><?php echo htmlentities(print_r($_GET, true)) ?></pre>
<h4>$_POST</h4>
<pre><?php echo htmlentities(print_r($_POST, true)) ?></pre>
<h4>$_COOKIE</h4>
<pre><?php echo htmlentities(print_r($_COOKIE, true)) ?></pre>
<h4>$_REQUEST</h4>
<pre><?php echo htmlentities(print_r($_REQUEST, true)) ?></pre>

<h3>Session state</h3>
<pre><?php
	if (version_compare(JVERSION, '4', 'lt'))
	{
		echo htmlentities(print_r($app->getSession()->getData()->toArray(), true));
	}
	else
	{
		echo htmlentities(print_r($app->getSession()->all(), true));
	}
	?></pre>

<?php
if (version_compare(JVERSION, '3.999.999', 'le'))
{
	if (!include_once(JPATH_ADMINISTRATOR . '/components/com_admin/models/sysinfo.php'))
	{
		return;
	}

	$model       = new AdminModelSysInfo();
}
else
{
	try
	{
		/** @var MVCFactoryInterface $factory */
		$factory = $app->bootComponent('com_admin')->getMVCFactory();
		/** @var \Joomla\Component\Admin\Administrator\Model\SysinfoModel $model */
		$model = $factory->createModel('Sysinfo', 'Administrator');
	}
	catch (Exception $e)
	{
		return;
	}
}

$directories = $model->getDirectory();

try
{
	$extensions = $model->getExtensions();
}
catch (Exception $e)
{
	$extension = [];
}

$phpSettings = $model->getPhpSettings();
$hasPHPInfo  = $model->phpinfoEnabled();
?>

<h3>PHP Settings</h3>
<table class="table table-striped">
	<?php foreach ($phpSettings as $k => $v): ?>
		<tr>
			<td><?php echo $k ?></td>
			<td><?php echo htmlentities(print_r($v, true)) ?></td>
		</tr>
	<?php endforeach; ?>
</table>

<?php if ($hasPHPInfo):
	$phpInfo = $model->getPhpInfoArray(); ?>
	<h3>Loaded PHP Extensions</h3>
	<table class="table table-striped">
		<?php foreach ($phpInfo as $section => $data):
			if ($section == 'Core')
			{
				continue;
			} ?>
			<tr>
				<td><?php echo htmlentities($section) ?></td>
				<td>
					<?php if (in_array($section, ['curl', 'openssl', 'ssh2', 'ftp', 'session', 'tokenizer'])): ?>
						<pre><?php echo htmlentities(print_r($data, true)) ?></pre>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>

<h3>Enabled Extensions</h3>
<table class="table table-striped">
	<?php foreach ($extensions as $extension => $info):
		if (strtoupper($info['state']) != 'ENABLED')
		{
			continue;
		} ?>
		<tr>
			<td><?php echo htmlentities($extension) ?></td>
			<td><?php echo htmlentities($info['version']) ?></td>
			<td><?php echo htmlentities($info['type']) ?></td>
			<td><?php echo htmlentities($info['author']) ?></td>
			<td><?php echo htmlentities($info['authorUrl']) ?></td>
		</tr>
	<?php endforeach; ?>
</table>

<h3>Directory Status</h3>
<table class="table table-striped">
	<?php foreach ($directories as $k => $v): ?>
		<tr>
			<td>
				<?php echo htmlentities($k) ?>
				<?php echo !empty($v['message']) ? "[{$v['message']}]" : '' ?>
			</td>
			<td>
				<?php if ($v['writable']): ?>
					<span class="label label-success">Writeable</span>
				<?php else: ?>
					<span class="label label-danger">Unwriteable</span>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>