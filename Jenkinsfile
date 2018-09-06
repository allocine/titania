pipeline {
    agent any

    options {
        disableConcurrentBuilds()
        ansiColor('xterm')
    }

    environment {
        DOCKER_COMPOSE_OVERRIDE = 'jenkins'

        COMPOSER_HOME = '~/.composer/'
        COMPOSER_PROCESS_TIMEOUT = 900

        BUILD_UUID = "${WORKSPACE.tokenize('/').last()}"
        GIT_SHA1 = "${GIT_COMMIT.substring(0,7)}"

        VOLUME_USER_ID = "${JENKINS_USER_ID}"
    }

    stages {
        stage('Install deps') {
            steps {
                echo 'Installing deps...'
                sh '''
                    make login-gcp install
                '''
            }
        }
        stage('Test') {
            steps {
                echo 'Testing...'
                sh '''
                    make test
                '''
            }
        }
    }

    post {
        always {
            echo 'Post actions : Always'
        }
        success {
            echo 'Post actions : Success'
        }
        unstable {
            echo 'Post actions : Unstable'
        }
        failure {
            echo 'Post actions : Failure'
        }
        changed {
            echo 'Post actions : Changed'
        }
        cleanup {
            echo 'Post actions : Clean up'

            sh """
                make cleanup
            """
        }
    }
}
