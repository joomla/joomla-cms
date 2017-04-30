pipeline {
      agent {
        none
      }
  stages {
    stage('codestyles') {
      agent {
        docker 'rdeutz/docker-phpcs'
      }
      steps {
      	sh "/usr/local/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla ."
      }
    }
    stage('Test'){
      steps {
        sh 'echo php53'
      }  
    }
  }
}  