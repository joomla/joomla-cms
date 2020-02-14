<?php
if (version_compare(PHP_VERSION, '5.4') < 0) {
    throw new \Exception('scssphp requires PHP 5.4 or above');
}

if (! class_exists('Leafo\ScssPhp\Version', false)) {
    include_once __DIR__ . '/scss/Base/Range.php';
    include_once __DIR__ . '/scss/Block.php';
    include_once __DIR__ . '/scss/Colors.php';
    include_once __DIR__ . '/scss/Compiler.php';
    include_once __DIR__ . '/scss/Compiler/Environment.php';
    include_once __DIR__ . '/scss/Exception/CompilerException.php';
    include_once __DIR__ . '/scss/Exception/ParserException.php';
    include_once __DIR__ . '/scss/Exception/ServerException.php';
    include_once __DIR__ . '/scss/Formatter.php';
    include_once __DIR__ . '/scss/Formatter/Compact.php';
    include_once __DIR__ . '/scss/Formatter/Compressed.php';
    include_once __DIR__ . '/scss/Formatter/Crunched.php';
    include_once __DIR__ . '/scss/Formatter/Debug.php';
    include_once __DIR__ . '/scss/Formatter/Expanded.php';
    include_once __DIR__ . '/scss/Formatter/Nested.php';
    include_once __DIR__ . '/scss/Formatter/OutputBlock.php';
    include_once __DIR__ . '/scss/Node.php';
    include_once __DIR__ . '/scss/Node/Number.php';
    include_once __DIR__ . '/scss/Parser.php';
    include_once __DIR__ . '/scss/Type.php';
    include_once __DIR__ . '/scss/Util.php';
    include_once __DIR__ . '/scss/Version.php';
}
