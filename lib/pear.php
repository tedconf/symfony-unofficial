<?php

$sf_symfony_lib_dir  = '/home/httpd/vhosts/symfony.fedora.synace/symfony/lib';
$sf_symfony_data_dir = '/home/httpd/vhosts/symfony.fedora.synace/symfony/data';
$sf_version = '@SYMFONY-VERSION@' == '@'.'SYMFONY-VERSION'.'@' ? trim(file_get_contents(dirname(__FILE__).'/BRANCH')) : '@SYMFONY-VERSION@';

return 'OK';
