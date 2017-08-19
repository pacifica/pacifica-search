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
