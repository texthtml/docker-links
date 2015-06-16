Feature: Parse Docker link environment variables

    Scenario: One port
        Given there is the following environment variables
            | name                         | value                     |
            | DB_REDIS_NAME                | romantic_lumiere/db_redis |
            | DB_REDIS_PORT                | tcp://172.17.0.2:6379     |
            | DB_REDIS_PORT_6379_TCP       | tcp://172.17.0.2:6379     |
            | DB_REDIS_PORT_6379_TCP_ADDR  | 172.17.0.2                |
            | DB_REDIS_PORT_6379_TCP_PORT  | 6379                      |
            | DB_REDIS_PORT_6379_TCP_PROTO | tcp                       |
        When I parse the environment variables
        Then the link "DB_REDIS" should have been found
         And the link "DB_REDIS" name should be "romantic_lumiere/db_redis"
         And the link "DB_REDIS" main port number should be 6379
         And the link "DB_REDIS" main address should be "172.17.0.2"
         And the link "DB_REDIS" main protocol should be "tcp"

    Scenario: multiple ports
        Given there is the following environment variables
            | name                         | value                     |
            | DB_NAME                      | romantic_lumiere/db       |
            | DB_PORT                      | tcp://172.17.0.5:6379     |
            | DB_PORT_6379_TCP             | tcp://172.17.0.5:6379     |
            | DB_PORT_6379_TCP_ADDR        | 172.17.0.5                |
            | DB_PORT_6379_TCP_PORT        | 6379                      |
            | DB_PORT_6379_TCP_PROTO       | tcp                       |
            | DB_PORT_6500_TCP             | tcp://172.17.0.5:6500     |
            | DB_PORT_6500_TCP_ADDR        | 172.17.0.5                |
            | DB_PORT_6500_TCP_PORT        | 6500                      |
            | DB_PORT_6500_TCP_PROTO       | tcp                       |
        When I parse the environment variables
        Then the link "DB" should have been found
         And the link "DB" name should be "romantic_lumiere/db"
         And the link "DB" main port number should be 6379
         And the link "DB" main address should be "172.17.0.5"
         And the link "DB" main protocol should be "tcp"
         And the link "DB" tcp port 6379 address should be "172.17.0.5"
         And the link "DB" tcp port 6500 address should be "172.17.0.5"

    Scenario: Link env
        Given there is the following environment variables
            | name                         | value                     |
            | DB_NAME                      | romantic_lumiere/db       |
            | DB_PORT                      | tcp://172.17.0.2:6379     |
            | DB_ENV_USERNAME              | username                  |
            | DB_ENV_PASSWORD              | password                  |
        When I parse the environment variables
        Then the link "DB" environment variable "USERNAME" should be "username"
         And the link "DB" environment variable "PASSWORD" should be "password"
         And the link "DB" environment variable "EMAIL" should be not exists

    Scenario: alias is case insensitive
        Given there is the following environment variables
            | name                         | value                     |
            | DB_REDIS_NAME                | romantic_lumiere/db_redis |
            | DB_REDIS_PORT                | tcp://172.17.0.2:6379     |
            | DB_REDIS_PORT_6379_TCP       | tcp://172.17.0.2:6379     |
            | DB_REDIS_PORT_6379_TCP_ADDR  | 172.17.0.2                |
            | DB_REDIS_PORT_6379_TCP_PORT  | 6379                      |
            | DB_REDIS_PORT_6379_TCP_PROTO | tcp                       |
        When I parse the environment variables
        Then the link "DB_REDIS" should have been found
         And the link "db_redis" should have been found

    Scenario: only detect links that have at least one port
        Given there is the following environment variables
            | name                         | value                     |
            | SERVER_NAME                  | nostalgic_morse           |
            | DB_REDIS_NAME                | romantic_lumiere/db_redis |
            | DB_REDIS_PORT                | tcp://172.17.0.2:6379     |
            | DB_REDIS_PORT_6379_TCP       | tcp://172.17.0.2:6379     |
            | DB_REDIS_PORT_6379_TCP_ADDR  | 172.17.0.2                |
            | DB_REDIS_PORT_6379_TCP_PORT  | 6379                      |
            | DB_REDIS_PORT_6379_TCP_PROTO | tcp                       |
        When I parse the environment variables
        Then the link "DB_REDIS" should have been found
         And the link "SERVER" should not have been found
