pipeline {
      agent {
        docker 'rdeutz/docker-phpcs'
      }
  stages {
    stage('codestyles') {
      steps {
        parallel(
          "codestyles": {
            	sh "/usr/local/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla ."
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