<?php

$sf_symfony_lib_dir  = '/users/joesimms/projects/symfony/joesimms/branch/lib';
$sf_symfony_data_dir = '/users/joesimms/projects/symfony/joesimms/branch/data';
$sf_version = '@SYMFONY-VERSION@' == '@'.'SYMFONY-VERSION'.'@' ? trim(file_get_contents(dirname(__FILE__).'/BRANCH')) : '@SYMFONY-VERSION@';

return 'OK';
