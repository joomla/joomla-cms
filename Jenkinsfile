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
      steps {
        parallel(
          "Testing-PHP53": {
            agent {
              docker 'rdeutz/docker-php56'
            }
           sh 'echo "php53"'           
          },
          "Testing-PHP54": {
            agent {
              docker 'rdeutz/docker-php56'
            }
            sh 'echo "php54"'
            
          }
        )
      }
    }
  }
}