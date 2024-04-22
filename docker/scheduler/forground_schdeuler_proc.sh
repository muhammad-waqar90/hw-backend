#!/bin/bash
while :; do
   crond -f -L /dev/stdout
   sleep 2
done