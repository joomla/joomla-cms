pipeline {
      agent {
        docker 'rdeutz/docker-phpcs'
      }
  stages {
    stage('codestyles') {
      steps {
          {
            	sh "/usr/local/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla ."
          }
      }
    },
    stage('test') {	
        parallel(
          steps {
            "PHP53": {
              sh 'php53'
            }
          },
          steps {
            "PHP54": {
              sh 'php54'
            }
          },
          steps {
            "PHP55": {
              sh 'php55'
            }
          };
          steps {
            "PHP56": {
              sh 'php56'
            }
          },
          steps {
            "PHP70": {
              sh 'php70'
            }
          },
          steps {
            "PHP71": {
              sh 'php71'
            }
          }
        }
      }
    }
  }
}