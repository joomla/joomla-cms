<?php

$phar = new Phar('joomla.phar', 0, 'joomla.phar');
$phar->buildFromDirectory(__DIR__ . '/../');

//$phar->buildFromDirectory(__DIR__, '/\.(php|txt|ini|json|xml|js|css|html)$/');
//$phar->setStub($phar->createDefaultStub('cli/index.php', 'www/index.php'));
