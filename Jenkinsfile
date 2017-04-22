#!/usr/bin/env groovy

pipeline {
	agent none
	stages {
	    stage('codestyles') {
	    	agent { docker 'joomlaprojects/docker-phpcs' }
	    	steps {
	    		sh "export PATH=~/.composer/vendor/bin:$PATH && phpcs --version"
	    	}
	    	
	    }
	    stage('test') {
	        steps {
	            sh "echo stage test"    
	        }
	        
	    }
	
	}
}