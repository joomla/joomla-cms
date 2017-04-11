#!/usr/bin/env groovy

node('master') {
    try {
	    stage('codestyles') {
	    	sh "echo stage codestyles"
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