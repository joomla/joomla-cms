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
        sh '/usr/local/vendor/bin/phpcs --report=full --extensions=php -p --standard=build/phpcs/Joomla .'
      }
    }

    stage("Testing PHP") {
      steps {
        // You can only use the parallel step if it's the *only* step in the stage.
        parallel(
          PHP53: {
            sh 'export PHPVERSION=php53;/usr/local/bin/docker-compose --project-name php53-$BUILD_ID -f build/jenkins/docker-compose.yml run --rm  test bash build/jenkins/unit-tests.sh'
          },
          PHP54: {
            sh 'export PHPVERSION=php54;/usr/local/bin/docker-compose --project-name php54-$BUILD_ID -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
          },
          PHP55: {
            sh 'export PHPVERSION=php55;/usr/local/bin/docker-compose --project-name php55-$BUILD_ID -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
          },
          PHP56: {
            sh 'export PHPVERSION=php56;/usr/local/bin/docker-compose --project-name php56-$BUILD_ID -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
          },
          PHP70: {
            sh 'export PHPVERSION=php70;/usr/local/bin/docker-compose --project-name php70-$BUILD_ID -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
          },
          PHP71: {
            sh 'export PHPVERSION=php71;/usr/local/bin/docker-compose --project-name php71-$BUILD_ID -f build/jenkins/docker-compose.yml run --rm test bash build/jenkins/unit-tests.sh'
          }
        )
      }
      post {
        always {
         // Spin down containers no matter what happens
         sh 'export PHPVERSION=php53;/usr/local/bin/docker-compose --project-name php53-$BUILD_ID -f build/jenkins/docker-compose.yml down'
         sh 'export PHPVERSION=php54;/usr/local/bin/docker-compose --project-name php54-$BUILD_ID -f build/jenkins/docker-compose.yml down'
         sh 'export PHPVERSION=php55;/usr/local/bin/docker-compose --project-name php55-$BUILD_ID -f build/jenkins/docker-compose.yml down'
         sh 'export PHPVERSION=php56;/usr/local/bin/docker-compose --project-name php56-$BUILD_ID -f build/jenkins/docker-compose.yml down'
         sh 'export PHPVERSION=php70;/usr/local/bin/docker-compose --project-name php70-$BUILD_ID -f build/jenkins/docker-compose.yml down'
         sh 'export PHPVERSION=php71;/usr/local/bin/docker-compose --project-name php71-$BUILD_ID -f build/jenkins/docker-compose.yml down'
        }
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

    stage('System test') {
      agent {
        docker {
           image 'joomlaprojects/docker-systemtests'
           args  '--user 0 --privileged'
        }
      }
      steps {
        sh 'bash build/jenkins/system-tests.sh'
      }
    }
  }
}

