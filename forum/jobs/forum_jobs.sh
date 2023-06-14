#!/bin/bash
#

cd $(dirname $0)

/usr/bin/php -c /etc/php.ini -f "/var/www/html/public/nosql.itwrks.org/forum/jobs/forum_jobs.php" > "/var/www/html/public/nosql.itwrks.org/forum/jobs/protocoll.log"
