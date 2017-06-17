pipeline {
  agent any
  stages {
    stage('codestyles') {
      agent {
        docker 'rdeutz/docker-phpcs'
      }
      steps {
        sh 'echo $(date)'
        sh '#/usr/local/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla .'
      }
    }
    stage('Testing-PHP53') {
        environment {
            PHPVERSION = 'php53'
        }
        steps {
            sh 'docker-compose -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
        }
    }
    stage('Testing-PHP54') {
        environment {
            PHPVERSION = 'php54'
        }
        steps {
            sh 'docker-compose -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
        }
    }
    stage('Testing-PHP55') {
        environment {
            PHPVERSION = 'php55'
        }
        steps {
            sh 'docker-compose -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
        }
    }
    stage('Testing-PHP56') {
        environment {
            PHPVERSION = 'php56'
        }
        steps {
            sh 'docker-compose -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
        }
    }
    stage('Testing-PHP70') {
        environment {
            PHPVERSION = 'php70'
        }
        steps {
            sh 'docker-compose -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
        }
    }
    stage('Testing-PHP71') {
        environment {
            PHPVERSION = 'php71'
        }
        steps {
            sh 'docker-compose -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
        }
    }
    stage('Testing-Javascript') {
      agent {
        docker {
            image 'joomlaprojects/docker-systemtests'
            args  '--user 0'
        }
      }
      steps {
        sh 'echo $(date)'
        sh '''
            ln -s /usr/bin/nodejs /usr/bin/node && \
            export DISPLAY=:0 && \
            (Xvfb -screen 0 1024x768x24 -ac +extension GLX +render -noreset &) && \
            sleep 3 && \
            (fluxbox &) && \
            cd tests/javascript && npm install --no-optional && cd ../.. && \
            (firefox &) && \
            tests/javascript/node_modules/karma/bin/karma start karma.conf.js --single-run
        '''
        sh 'echo $(date)'
      }
    }
  }
}

