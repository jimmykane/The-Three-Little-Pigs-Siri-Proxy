#!/bin/bash
while true; 
do 
echo "Starting Server"
    if siriproxy server; then
       echo "[Info - SiriProxy] Max connections reached! Pausing for 2 mins" 
       sleep 120
    else
      echo "[Warning - Crash - SiriProxy] Crashed! Restarting in 2 sec!"
      sleep 2
    fi

done
