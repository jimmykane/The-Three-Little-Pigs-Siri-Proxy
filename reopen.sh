#!/bin/bash
while true; 
do 
echo "Starting Server"
    if ruby start.rb; then

       echo "Server is Running"
   exit 1
   else
      echo "Crashed! Restarting!"
fi
sleep 2
done
