#!/bin/sh
EPOS="$1"
REV="$2"
export LANG=en_US.UTF-8
echo "--------------------  svn update   --------------------------"
svn update /home/bijia/www/appback/trunk
cd /home/bijia/www/appback/trunk
echo "--------------------  stop forever --------------------------"
forever stopall
sleep 3s
echo "--------------------- start forever--------------------------"
forever start -l forever.log -a app.js
