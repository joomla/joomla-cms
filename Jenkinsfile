#!/usr/bin/env groovy

node('master') {
    try {
	    stage('codestyles') {
	    	sh "docker run -it --rm -v $(pwd):/opt -w /opt joomlaprojects/docker-phpcs /root/.composer/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla ."
	    }
	    stage('test') {
	        sh "echo stage test"
	    }
    } catch(error) {
	    // Maybe some alerting?
        throw error
    } finally {
        // Spin down containers no matter what happens
        
    }
}