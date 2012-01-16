echo "----------Certificate creator by jk---------"
echo "----------Do not enter any info ! !---------"
echo "Step 1:" 
openssl req  -new -x509 -extensions v3_ca -keyout ~/.siriproxy/ca.key -out ~/.siriproxy/ca.crt -days 1825
echo "Step 2:------Warning!------" 
echo "Info: now enter common name the ip/dns/of the server "
openssl req  -new -nodes -keyout ~/.siriproxy/server.key -out ~/.siriproxy/server.csr -days 365
echo "Step 3:" 
openssl x509 -req -days 365 -in ~/.siriproxy/server.csr -CA ~/.siriproxy/ca.crt -CAkey ~/.siriproxy/ca.key -set_serial 01 -out ~/.siriproxy/server.passless.crt
echo "Step 4:" 
openssl rsa -in ~/.siriproxy/server.key -out ~/.siriproxy/server.key.insecure
echo "Step 5:" 
mv ~/.siriproxy/server.key ~/.siriproxy/server.key.secure
mv ~/.siriproxy/server.key.insecure ~/.siriproxy/server.passless.key
echo "All Done!!!" 
