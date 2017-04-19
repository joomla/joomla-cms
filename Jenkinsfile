#!/usr/bin/env groovy

pipeline {
	agent none
	stages {
	    stage('codestyles') {
	    	agent { docker 'joomlaprojects/docker-phpcs' }
	    	steps {
	    	    sh "echo $PWD"    
	    	}
	    	
	    }
	    stage('test') {
	        steps {
	            sh "echo stage test"    
	        }
	        
	    }
	
	}
}