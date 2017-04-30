pipeline {
      agent {
        docker 'joomlaprojects/docker-phpcs'
      }
  stages {
    stage('codestyles') {
      steps {
        parallel(
          "codestyles": {
            	sh "ls -l"
          },
          "cs2": {
            sh 'php --version'
            
          }
        )
      }
    }
    stage('test') {	
      steps {
        sh 'echo stage test'
      }
    }
  }
}