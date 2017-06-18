#!groovy

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


    stage("Testing PHP") {
      steps {
        // You can only use the parallel step if it's the *only* step in the stage.
        parallel(
          COMPOSE: {
            sh 'docker-compose --help'
          },
          PHP53: {
            sh 'export PHPVERSION=php53;/usr/local/bin/docker-compose --project-name php53 -f build/jenkins/docker-compose.yml run --rm  test bash build/jenkins/unit-tests.sh'
          },
          PHP54: {
            sh 'export PHPVERSION=php54;/usr/local/bin/docker-compose -f build/jenkins/docker-compose.yml run --rm --project-name php54 test bash build/jenkins/unit-tests.sh'
          },
          PHP55: {
            sh 'export PHPVERSION=php55;/usr/local/bin/docker-compose -f build/jenkins/docker-compose.yml run --rm --project-name php55 test bash build/jenkins/unit-tests.sh'
          },
          PHP56: {
            sh 'export PHPVERSION=php56;/usr/local/bin/docker-compose -f build/jenkins/docker-compose.yml run --rm --project-name php56 test bash build/jenkins/unit-tests.sh'
          },
          PHP70: {
            sh 'export PHPVERSION=php70;/usr/local/bin/docker-compose -f build/jenkins/docker-compose.yml run --rm --project-name php70 test bash build/jenkins/unit-tests.sh'
          },
          PHP71: {
            sh 'export PHPVERSION=php71;/usr/local/bin/docker-compose -f build/jenkins/docker-compose.yml run --rm --project-name php71 test bash build/jenkins/unit-tests.sh'
          }
        )
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
            tests/javascript/node_modules/karma/bin/karma start karma.conf.js --single-run
        '''
        sh 'echo $(date)'
      }
    }
  }
}

