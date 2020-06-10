## Install

```shell script
git clone https://github.com/cetver/best-4-you-test-task -- /project/dir
cd /project/dir
```

## Set up docker

```shell script
find .docker -type f -name .gitignore -exec rm --verbose --recursive --force {} \;

sed \
    --regexp-extended \
    --in-place \
    "s@/var/www/best-4-you-test-task@/project/dir@" \
    .docker/docker-compose.yml

.docker/setup/copy-common-scripts

docker-compose --file .docker/docker-compose.yml up --detach --build

find .docker -type d -name run -exec sudo chmod --verbose --recursive 777 {} \;
 
find .docker -type d -name etc -exec sudo chown --verbose --recursive "$(whoami)" {} \;  
```

## Set up project

```shell script
echo "
##
# Best 4 you test task
##

127.0.0.1    best-4-you-test-task.loc
" | sudo tee --append /etc/hosts

docker-compose --file .docker/docker-compose.yml exec best-4-you-test-task-php /bin/bash --login
best-4-you-test-task@php7_4-debian:/var/www/html$ composer install
best-4-you-test-task@php7_4-debian:/var/www/html$ sed --in-place --regexp-extended \
                                                      "s@;?opcache\.preload=.*@opcache\.preload=/var/www/html/var/cache/dev/App_KernelDevDebugContainer.preload.php@" \
                                                      "${PHP_INI_DIR}/php-cli.ini"
best-4-you-test-task@php7_4-debian:/var/www/html$ sed --in-place --regexp-extended \
                                                      "s@;?opcache\.preload_user=.*@opcache\.preload_user=$(whoami)@" \
                                                      "${PHP_INI_DIR}/php-cli.ini"
best-4-you-test-task@php7_4-debian:/var/www/html$ sed --in-place --regexp-extended \
                                                      "s@;?opcache\.preload=.*@opcache\.preload=/var/www/html/var/cache/dev/App_KernelDevDebugContainer.preload.php@" \
                                                      "${PHP_INI_DIR}/php-fpm-fcgi.ini"
best-4-you-test-task@php7_4-debian:/var/www/html$ sed --in-place --regexp-extended \
                                                      "s@;?opcache\.preload_user=.*@opcache\.preload_user=$(ps -o user= -p $(pidof -s php-fpm))@" \
                                                      "${PHP_INI_DIR}/php-fpm-fcgi.ini"
# password = qwe
best-4-you-test-task@php7_4-debian:/var/www/html$ sudo kill -USR2 1
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/console doctrine:database:create
# Write permissions to all groups, required only for the 'www-data' group. In real project will done via 'setfacl'
best-4-you-test-task@php7_4-debian:/var/www/html$ chmod 666 var/data.db
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/console doctrine:migrations:migrate --no-interaction
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/console doctrine:fixtures:load --no-interaction
```

## Usage

[https://best-4-you-test-task.loc/api/v1](https://best-4-you-test-task.loc/api/v1)

https for http2 and brotli

ignore browser security warning or [install Certificate Authority](https://github.com/cetver/best-4-you-test-task/blob/master/.docker/nginx/setup/template/ssl/README.md#ff)

## Testing

```shell script
docker-compose --file .docker/docker-compose.yml exec best-4-you-test-task-php /bin/bash --login
best-4-you-test-task@php7_4-debian:/var/www/html$ export APP_ENV=test
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/console cache:warmup
best-4-you-test-task@php7_4-debian:/var/www/html$ sed --in-place --regexp-extended \
                                                      "s@;?opcache\.preload=.*@opcache\.preload=/var/www/html/var/cache/test/App_KernelTestDebugContainer.preload.php@" \
                                                      "${PHP_INI_DIR}/php-cli.ini"
best-4-you-test-task@php7_4-debian:/var/www/html$ sed --in-place --regexp-extended \
                                                      "s@;?opcache\.preload_user=.*@opcache\.preload_user=$(whoami)@" \
                                                      "${PHP_INI_DIR}/php-cli.ini"
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/console doctrine:database:create
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/console doctrine:migrations:migrate --no-interaction
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/console doctrine:fixtures:load --no-interaction
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/phpunit --testsuite=unit
best-4-you-test-task@php7_4-debian:/var/www/html$ bin/phpunit --testsuite=functional
```



