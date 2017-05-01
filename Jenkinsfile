pipeline {
  agent none
  stages {
    stage('codestyles') {
      agent {
        docker 'rdeutz/docker-phpcs'
      }
      steps {
        sh '/usr/local/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla .'
      }
    }
    stage('Testing-PHP') {
      agent {
        docker 'rdeutz/docker-php56'
      }
      steps {
        sh 'phpunit --version'
      }
    }
    stage('Testing-Javascrip') {
      agent {
        docker 'joomlaprojects/docker-systemtests'
      }
      steps {
        sh 'echo $(date)'
      }
    }
  }
}