pipeline {
  agent none
  stages {
    stage('codestyles') {
      agent {
        docker 'joomlaprojects/docker-phpcs'
      }
      steps {
        parallel(
          "codestyles": {
            sh 'phpcs --version'
            
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