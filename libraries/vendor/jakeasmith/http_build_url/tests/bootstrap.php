<?php
require dirname( __DIR__ ) . '/src/http_build_url.php';

// PHPUnit 6 compatibility for previous versions
if ( class_exists( 'PHPUnit\Runner\Version' ) && version_compare( PHPUnit\Runner\Version::id(), '6.0', '>=' ) ) {
    class_alias( 'PHPUnit\Framework\Assert',        'PHPUnit_Framework_Assert' );
    class_alias( 'PHPUnit\Framework\TestCase',      'PHPUnit_Framework_TestCase' );
    class_alias( 'PHPUnit\Framework\Error\Error',   'PHPUnit_Framework_Error' );
    class_alias( 'PHPUnit\Framework\Error\Notice',  'PHPUnit_Framework_Error_Notice' );
    class_alias( 'PHPUnit\Framework\Error\Warning', 'PHPUnit_Framework_Error_Warning' );
}

// Past this point, tests will start
