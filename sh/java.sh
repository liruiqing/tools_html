#!/bin/sh
export LANG=en_US.UTF-8
pid=`pgrep java`
ehco '---------------'
echo $pid
echo '---------- kill java pid --------'
kill -9 ${pid}
sleep 3s
echo '-------------------------------------'
echo `pgrep java`
echo '---------------- svn update -------------------'
svn update /home/bijia/quotation_comparison/src/main/java/ins/quotation_comparison
sleep 1s
cd /home/bijia/quotation_comparison
echo '---------------- rm file nohup.out ------'
rm -rf nohup.out 
rm -rf logs/*
echo `pwd`
echo '---------------  clean mvn  -----------'
mvn clean
sleep 4s
echo '----------------- start mvn  ------------'
nohup mvn jetty:run &
sleep 3s
echo '------------------ open logs --------------'
tail -f nohup.out
