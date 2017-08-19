#!/bin/bash -xe

composer self-update
composer clear-cache
wget -O /tmp/geckodriver.tar.gz https://github.com/mozilla/geckodriver/releases/download/v0.14.0/geckodriver-v0.14.0-linux64.tar.gz
mkdir /tmp/geckodriver
tar -C /tmp/geckodriver -xzf /tmp/geckodriver.tar.gz
export PATH=$PATH:/tmp/geckodriver
sudo rm -f /usr/local/bin/docker-compose
sudo curl -L -o /usr/local/bin/docker-compose https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-$(uname -s)-$(uname -m)
sudo chmod +x /usr/local/bin/docker-compose
export DISPLAY=:99.0
sh -e /etc/init.d/xvfb start
sudo service postgresql stop
sudo service mysql stop
sudo service nginx stop
curl -L -o /tmp/selenium-server.jar http://selenium-release.storage.googleapis.com/3.0/selenium-server-standalone-3.0.1.jar
java -jar /tmp/selenium-server.jar > /tmp/selenium-server.log 2>&1 &
echo $! > /tmp/selenium-server.pid
