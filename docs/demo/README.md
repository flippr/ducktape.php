Demo Documentation for Ducktape.php
===================================

These are the steps involved for setting up your first demo class for Ducktape.php as well as your first consumer/provider.  

First, we need to make sure you have all the basics.

System Configuration and Installation
-------------------------------------

1. Installing the latest PHP

ducktape.php is currently developed and tested on PHP53.  Some extra warnings may exist when deployed on a PHP54 installation.  So the first step is getting PHP up and running on your system.

#### Debian Based Systems ####

sudo apt-get install php5

#### FreeBSD Systems ####

portinstall -r lang/php5
portinstall -r lang/php5-extensions

2. Installing the required PHP Extensions

#### Debian Based Systems ####

#### FreeBSD Systems ####

##### PHPUnit #####
portinstall -r devel/pear
portinstall -r devel/php5-pcntl
pear channel-discover pear.phpunit.de/PHPUnit
pear channel-discover pear.symfony.com
pear install symfony/YAML
pear install phpunit/PHP\_Invoker
pear install phpunit/PHPUnit

##### PHP YAML #####
portinstall -r textproc/pecl-yaml

Post-Installation
-----------------

After installing the extensions mentioned above, make sure they are loaded into your php.ini, then give your web server a restart and you should be good to go!
  Remember, if you are running PHP-FPM or PHP-CGI, you will have to restart that instead of your web server.

### Creating Directories ###

You can deploy your ducktape.php in many different styles, but as pointed out on the [Expressive Analytics Ducktape.php Wiki](https://github.com/expressiveanalytics/ducktape.php/wiki/Getting-started-guide) following the suggested directory structure is great for security and version control of your application.

1. Create a local, logs, and site folder next to your ducktape.php deployment
