#!/bin/bash


cat redisdump8.txt  |  grep _userid | awk -F"=" '{print $2}' | sort -u | xargs -l  php telegram-notify.php "Please rate me via this link: https://telegram.me/storebot?start=trafficRobot 
Thank you!"
