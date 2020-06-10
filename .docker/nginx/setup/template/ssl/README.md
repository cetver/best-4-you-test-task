## CA
```shell script
openssl genrsa -out best-4-you-test-task-CA.key 2048

openssl req \
    -x509 \
    -new \
    -sha256 \
    -subj '/C=AU/ST=Some-State/L=Some-City/O=!!! AAA best-4-you-test-task CA/OU=IT/CN=all.local.sites/emailAddress=ca@best-4-you-test-task.loc' \
    -key best-4-you-test-task-CA.key \
    -out best-4-you-test-task-CA.crt \
    -days 36500
```

## Concrete certificate
```shell script
openssl genrsa -out best-4-you-test-task.key 2048

openssl req \
    -sha256 \
    -new \
    -key best-4-you-test-task.key \
    -out best-4-you-test-task.csr \
    -subj '/C=AU/ST=Some-State/L=Some-City/O=best-4-you-test-task-Cert/OU=IT/CN=best-4-you-test-task.loc/emailAddress=it@best-4-you-test-task.loc'

# View the Certificate Signing Request
openssl req -in best-4-you-test-task.csr -text -noout

openssl x509 \
    -req \
    -sha256 \
    -in best-4-you-test-task.csr \
    -out best-4-you-test-task.crt \
    -CA best-4-you-test-task-CA.crt \
    -CAkey best-4-you-test-task-CA.key \
    -CAcreateserial \
    -days 3650 \
    -extensions v3_req \
    -extfile <(
        echo '[v3_req]'; 
        echo 'keyUsage = nonRepudiation, digitalSignature, keyEncipherment';
        echo 'subjectAltName = @subject_alt_name';
        echo '[subject_alt_name]';
        echo 'DNS.1 = *.best-4-you-test-task.loc';
        echo 'DNS.2 = best-4-you-test-task.loc';
    )

# View the certificate
openssl x509 -in best-4-you-test-task.crt -text -noout
```

## FF
about:preferences 
    search - certificates - view certificates - authorities - import - /project/dir/.docker/nginx/volumes/etc/ssl/best-4-you-test-task-CA.crt

## Chrome
chrome://settings/
    search - certificates - manage certificates - authorities - import - /project/dir/.docker/nginx/volumes/etc/ssl/best-4-you-test-task-CA.crt