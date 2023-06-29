#!/bin/bash
php ../composer.phar dump-autoload
php artisan cache:clear
php artisan config:cache
#php artisan ide-helper:generate
#php artisan ide-helper:meta
echo -e "\033[0;31m перед \033[0;34m push \033[0;31m  проверить сборку фронта в прод \033[0;34m npm run prod \033[0;31m !!!"
echo -e "\033[0;31m перед изменением существующего метода проверить, \033[0;34m выходные данные чтоб не было отличий \033[0;31m !!!"
echo -e "\033[0;31m желательно написать тесты перед ре факторингом!!!"
echo -e "\033[0;37m"
php -v
echo -e "\033[0m"
