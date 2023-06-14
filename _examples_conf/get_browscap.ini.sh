#!/bin/bash
#

curl -s -L -o /tmp/browscap.ini http://browscap.org/stream?q=Full_PHP_BrowsCapINI
[ $? == 0 ] && mv /tmp/browscap.ini /opt/etc/php/extra/
