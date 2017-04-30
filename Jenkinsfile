pipeline {
      agent {
        docker 'rdeutz/docker-phpcs'
      }
  stages {
    stage('codestyles') {
      steps {
          "codestyle" : {
            	sh "/usr/local/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla ."
          }
      }
    }
  }
}