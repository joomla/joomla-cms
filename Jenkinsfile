pipeline {
  agent none
  stages {
    stage('codestyles') {
      agent {
        docker 'rdeutz/docker-phpcs'
      }
      steps {
        sh 'echo $(date)'
        //sh '/usr/local/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla .'
      }
    }
    stage('Testing-PHP5') {
      agent {
        docker 'rdeutz/docker-php56'
      }
      steps {
        sh 'phpunit'
      }
    }
    stage('Testing-Javascript') {
      agent {
        docker 'joomlaprojects/docker-systemtests'
      }
      steps {
        sh 'echo $(date)'
      }
    }
  }
}
