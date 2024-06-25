#!/bin/bash

function create_database_in_container() {
    DB_ENV_FULLFILENAME=$1
    DB_CONTAINER_NAME=$2
    source $DB_ENV_FULLFILENAME

    DB_NAME=$MYSQL_DATABASE
    DB_USER=$MYSQL_USER
    DB_PASS=$MYSQL_PASSWORD

    _wait_for_mariadb $DB_CONTAINER_NAME $DB_USER $DB_PASS

    # docker command to check if database exist in the container
    DB_CHECK=$(docker exec -i $DB_CONTAINER_NAME mysql -u$DB_USER -p$DB_PASS -e "SHOW DATABASES LIKE '$DB_NAME';")

    if [[ "$DB_CHECK" == *"$DB_NAME"* ]]; then
        echo "Database '$DB_NAME' already exist."
    else
        echo "Database '$DB_NAME' does not exist. Create..."
        # Commande Docker pour créer la base de données avec Doctrine
        bin/console doctrine:database:create
        if [ $? -eq 0 ]; then
            echo "Success! Database '$DB_NAME' created."
            # Commande Docker pour créer le schéma
            bin/console doctrine:schema:create
            if [ $? -eq 0 ]; then
                echo "Success! Database schema created."
            else
                echo "Error: Database schema creation failed."
            fi
        else
            echo "Error: Database creation failed"
        fi
    fi

    _stop_database_container $CONTAINER_NAME chatbot-php
}

function _wait_for_mariadb()
{
    CONTAINER_NAME=$1
    DB_USER=$2
    DB_PASS=$3
    (
        cd docker || exit
        docker compose up -d $CONTAINER_NAME chatbot-php
    )

    local RETRIES=30
    local WAIT=2
    local COUNT=0

    while [ $COUNT -lt $RETRIES ]; do
        if docker exec $CONTAINER_NAME mysqladmin ping -u"$DB_USER" -p"$DB_PASS" --silent; then
            echo "MariaDB is ready."
            return 0
        fi
        echo "Waiting for MariaDB..."
        COUNT=$((COUNT+1))
        sleep $WAIT
    done
    echo "MariaDB n'a pas pu démarrer après $((RETRIES*WAIT)) secondes."
    return 1
}

function _stop_database_container
{
    (
        cd docker || exit
        docker compose down $CONTAINER_NAME chatbot-php
    )
}