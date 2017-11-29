#!/bin/bash -xe

composer self-update
composer clear-cache
wget -O /tmp/geckodriver.tar.gz https://github.com/mozilla/geckodriver/releases/download/v0.14.0/geckodriver-v0.14.0-linux64.tar.gz
mkdir /tmp/geckodriver
tar -C /tmp/geckodriver -xzf /tmp/geckodriver.tar.gz
export PATH=$PATH:/tmp/geckodriver
export DISPLAY=:99.0
sh -e /etc/init.d/xvfb start
curl -L -o /tmp/selenium-server.jar http://selenium-release.storage.googleapis.com/3.0/selenium-server-standalone-3.0.1.jar
java -jar /tmp/selenium-server.jar > /tmp/selenium-server.log 2>&1 &
echo $! > /tmp/selenium-server.pid
virtualenv travis/venv
. travis/venv/bin/activate
pip install -r travis/requirements.txt
psql -c 'create database pacifica_metadata;' -U postgres
mysql -e 'CREATE DATABASE pacifica_uniqueid;'
mysql -e 'CREATE DATABASE pacifica_ingest;'
export POSTGRES_ENV_POSTGRES_USER=postgres
export POSTGRES_ENV_POSTGRES_PASSWORD=
export MYSQL_ENV_MYSQL_USER=travis
export MYSQL_ENV_MYSQL_PASSWORD=
archiveinterfaceserver.py --config travis/config.cfg &
echo $! > archiveinterface.pid
pushd travis/metadata
MetadataServer.py &
popd
MAX_TRIES=60
HTTP_CODE=$(curl -sL -w "%{http_code}\\n" localhost:8121/keys -o /dev/null || true)
while [[ $HTTP_CODE != 200 && $MAX_TRIES > 0 ]] ; do
    sleep 1
    HTTP_CODE=$(curl -sL -w "%{http_code}\\n" localhost:8121/keys -o /dev/null || true)
    MAX_TRIES=$(( MAX_TRIES - 1 ))
done
TOP_DIR=$PWD
MD_TEMP=$(mktemp -d)
git clone https://github.com/pacifica/pacifica-metadata.git ${MD_TEMP}
pushd ${MD_TEMP}
python test_files/loadit.py
popd
pushd travis/policy
PolicyServer.py &
echo $! > PolicyServer.pid
popd
