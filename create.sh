echo "----------Certificate creator by jk---------"
echo "Step 1:" 
openssl req  -new -x509 -extensions v3_ca -keyout ca.key -out ca.crt -days 1825
echo "Step 2:------Warning!------" 
echo "Info: now enter common name the ip/dns/of the server "
openssl req  -new -nodes -keyout server.key -out server.csr -days 365
echo "Step 3:" 
openssl x509 -req -days 365 -in server.csr -CA ca.crt -CAkey ca.key -set_serial 01 -out server.passless.crt
echo "Step 4:" 
openssl rsa -in server.key -out server.key.insecure
echo "Step 5:" 
mv server.key server.key.secure
mv server.key.insecure server.passless.key
echo "All Done!!!" 
