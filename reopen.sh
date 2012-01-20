#!/bin/bash
while true; 
do 
echo "Starting Server"
    if siriproxy server; then
       echo "Max connections reached!" 
   exit 1
   else
      echo "Crashed! Restarting!"
fi
sleep 60
done
