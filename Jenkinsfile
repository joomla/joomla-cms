#!/usr/bin/env groovy

node('master') {
	agent none
    try {
	    stage('codestyles') {
	    	agent { docker 'joomlaprojects/docker-phpcs' }

	    	sh "/root/.composer/vendor/bin/phpcs --version"
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