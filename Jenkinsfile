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
    stage('Testing') {
      agent {
        docker 'rdeutz/docker-phpcs'
      }
      steps {
        parallel(
          "Testing-PHP53": {
            sh 'echo "php53"'
            
          },
          "Testing-PHP54": {
            sh 'echo "php54"'
            
          }
        )
      }
    }
  }
}