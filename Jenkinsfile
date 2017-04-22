#!/usr/bin/env groovy

pipeline {
	agent none
	stages {
	    stage('codestyles') {
	    	agent { docker 'joomlaprojects/docker-phpcs' }
	    	steps {
	    		sh "ifconfig"
	    		sh "phpcs --version"
	    	}
	    	
	    }
	    stage('test') {
	        steps {
	            sh "echo stage test"    
	        }
	        
	    }
	
	}
}