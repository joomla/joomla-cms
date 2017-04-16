#!/usr/bin/env groovy

node('master') {
	agent none
    stage('codestyles') {
    	agent { docker 'joomlaprojects/docker-phpcs' }

    	sh "/root/.composer/vendor/bin/phpcs --version"
    }
    stage('test') {
        sh "echo stage test"
    }
}