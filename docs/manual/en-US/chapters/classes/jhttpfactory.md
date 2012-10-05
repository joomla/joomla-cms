JHttpFactory
============

JHttp objects are created by using the JHttpFactory::getHttp method.

    // The default transport will be 'curl' because this is the first transport.
    $http = JHttpFactory::getHttp();

    // Create a 'stream' transport.
    $http = JHttpFactory::getHttp(null, 'stream');
