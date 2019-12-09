<?php
defined('_JEXEC') or die;

// TODO this file needs to be completely updated.

use Joomla\CMS\Language\Text;

$app  = JFactory::getApplication();
$lang = JFactory::getLanguage();
$user = JFactory::getUser();
$doc  = JFactory::getDocument();

// Getting params from template
$params = $app->getTemplate(true)->params;

if (!isset($this->error))
{
	$this->error = JFactory::getApplication()->enqueueMessage( Text::_( 'JERROR_ALERTNOAUTHOR' ), 'warning' );
	$this->debug = false;
}

$templateStyle = $params->get('templateStyle');
$theme         = $params->get( 'templateTheme' );
$template      = JPATH::clean(JPATH_BASE . '/templates/' . $this->template);

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>"
      lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
    <title><?php echo $this->title; ?><?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo $template . '/css/styles/' . $templateStyle . '/bootstrap.min.css'; ?>"
          type="text/css"/>
	<?php if ($templateStyle == 'default') { ?>
        <link rel="stylesheet"
              href="<?php echo $template . '/css/styles/' . $templateStyle . '/bootstrap-theme.min.css'; ?>"
              type="text/css"/>
	<?php } ?>
	<!-- <link rel="stylesheet" href="<?php echo $template . '/css/framework/all.css'; ?>" type="text/css"/> -->
    <link rel="stylesheet" href="<?php echo $template . '/css/framework/joomla.fix.css'; ?>" type="text/css"/>
    <link rel="stylesheet" href="<?php echo $template . '/css/framework/bs3.fix.css'; ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo $template . '/css/framework/template.css'; ?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo $template . '/css/colors/' . $theme . '.css'; ?>" type="text/css"/>
    <link rel="stylesheet" href="<?php echo $template; ?>/css/system/error.css" type="text/css"/>
	<?php
	if (JFile::exists($template . '/css/custom.css'))
	{
		echo '<link rel = "stylesheet" href = "' . $template . '/css/custom.css" ' . '  type = "text/css" />';
	};
	?>

    <script src="<?php echo $template . '/js/jui/jquery.min.js' ?>"></script>
    <script src="<?php echo $template . '/js/jui/bootstrap.min.js' ?>"></script>
	<!-- <script src="<?php echo $template . '/js/jquery.mmenu.min.all.js' ?>"></script> -->
    <script src="<?php echo $template . '/js/template.js' ?>"></script>
	<!--  <script src="<?php echo $template . '/js/jquery.resize.js' ?>"></script> -->

</head>
<body>
<div id="error-page" class="jumbotron">
    <div class="container">
        <div class="row well well-lg clearfix">
            <div class="panel panel-danger">
                <div class="panel-heading">
                    <h2>Oooops, we have a <?php echo $this->error->getCode(); ?> error</h2>
                </div>
                <div class="panel-body">
                    <h3 class="error-type"><?php echo $this->error->getMessage(); ?></h3>
                    <h3>The button below will take you back to the home page. <br>If you are still having problems
                        please contact us via our contact page.</h3>
                </div>
                <div class="panel-footer btn-flex">
                    <div class="pill">
                        <a class="button btn btn-success" href="<?php echo $this->baseurl; ?>/index.php"
                           title="<?php echo Text::_('JERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo Text::_('JERROR_LAYOUT_HOME_PAGE'); ?></a>
                    </div>
                </div>
            </div>
			<?php if ($this->debug) { ?>
                <a class="accordion-toggle collapsed btn btn-danger" href="#debug" data-toggle="collapse"
                   aria-expanded="false">Detailed Info...</a>

                <div id="debug" class="panel-collapse collapse btn btn-danger" aria-expanded="false">
                    <div class="error-type">
                        <span class="glyphicon glyphicon-exclamation-sign"></span><span class="label label-warning">
										<?php echo htmlspecialchars($this->error->getMessage(), ENT_QUOTES, 'UTF-8'); ?>
									</span>
                        <span class="glyphicon glyphicon-exclamation-sign"></span>
						<?php echo htmlspecialchars($this->error->getFile(), ENT_QUOTES, 'UTF-8'); ?>
                        :<?php echo $this->error->getLine(); ?>

                    </div>
                    <div>
						<?php echo $this->renderBacktrace(); ?>
						<?php // Check if there are more Exceptions and render their data as well  ?>
						<?php if ($this->error->getPrevious()) : ?>
							<?php $loop = true; ?>
							<?php // Reference $this->_error here and in the loop as setError() assigns errors to this property and we need this for the backtrace to work correctly ?>
							<?php // Make the first assignment to setError() outside the loop so the loop does not skip Exceptions ?>
							<?php $this->setError($this->_error->getPrevious()); ?>
							<?php while ($loop === true) : ?>
                                <p><strong><?php echo Text::_('JERROR_LAYOUT_PREVIOUS_ERROR'); ?></strong></p>
                                <p><?php echo htmlspecialchars($this->_error->getMessage(), ENT_QUOTES, 'UTF-8'); ?></p>
								<?php echo $this->renderBacktrace(); ?>
								<?php $loop = $this->setError($this->_error->getPrevious()); ?>
							<?php endwhile; ?>
							<?php // Reset the main error object to the base error ?>
							<?php $this->setError($this->error); ?>
						<?php endif; ?>
                    </div>
                </div>
			<?php } ?>
        </div>
    </div>
</body>
</html>
