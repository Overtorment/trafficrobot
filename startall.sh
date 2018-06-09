#!/usr/bin/env bash
forever --spinSleepTime=5000 --minUptime=1000 -a -l /dev/null -o /dev/null -e /dev/null -c php start app.php --bot &
forever --spinSleepTime=5000 --minUptime=1000 -a -l /dev/null -o /dev/null -e /dev/null -c php start app.php --email &
forever --spinSleepTime=5000 --minUptime=1000 -a -l /dev/null -o /dev/null -e /dev/null -c php start app.php --website &
forever --spinSleepTime=5000 --minUptime=1000 -a -l /dev/null -o /dev/null -e /dev/null        start app.js &
