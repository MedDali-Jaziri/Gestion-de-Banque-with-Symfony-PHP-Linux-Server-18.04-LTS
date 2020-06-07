echo "The installation of apache2,mysql,php and update system"
sudo apt update
sudo apt upgrade
sudo apt install apache2
sudo apt install mysql-server
sudo apt install php7.2 libapache2-mod-php7.2 php7.2-mysql php7.2-xml

echo "The installation of composer"
sudo apt install curl php-cli php-mbstring git unzip
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.phpHASH=544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
composer

echo "The installation of Symfony"
wget https://get.symfony.com/cli/installer -O - | bash
sudo mv /home/useradm/.symfony/bin/symfony /usr/local/bin/symfony
symfony check:requirements

composer require knplabs/knp-paginator-bundle

echo "Creation of database"
php bin/console doctrine:database:create
php bin/console doctrine:schema:update --force
