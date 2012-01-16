#!/bin/bash
while true; 
do 
echo "Starting Server"
    if siriproxy server; then

       echo "Server is Running"
   exit 1
   else
      echo "Crashed! Restarting!"
fi
sleep 2
done
