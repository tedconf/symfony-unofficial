<?php

$config->add(array(
  // root directory structure
  'sf_cache_dir_name' => 'cache',
  'sf_log_dir_name'   => 'log',
  'sf_lib_dir_name'   => 'lib',
  'sf_model_dir_name' => 'model',
  'sf_web_dir_name'   => 'web',
  'sf_data_dir_name'  => 'data',

  // global directory structure
  'sf_app_dir'        => SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP,
  'sf_model_dir'      => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'model',
  'sf_lib_dir'        => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'lib',
  'sf_web_dir'        => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'web',
  'sf_upload_dir'     => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'uploads',
  'sf_base_cache_dir' => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.SF_APP,
  'sf_cache_dir'      => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.SF_ENVIRONMENT,
  'sf_log_dir'        => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'log',
  'sf_data_dir'       => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'data',

  // SF_CACHE_DIR directory structure
  'sf_template_cache_dir' => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.SF_ENVIRONMENT.DIRECTORY_SEPARATOR.'template',
  'sf_i18n_cache_dir'     => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.SF_ENVIRONMENT.DIRECTORY_SEPARATOR.'i18n',
  'sf_config_cache_dir'   => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.SF_ENVIRONMENT.DIRECTORY_SEPARATOR.'config',
  'sf_test_cache_dir'     => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.SF_ENVIRONMENT.DIRECTORY_SEPARATOR.'test',
  'sf_module_cache_dir'   => SF_ROOT_DIR.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.SF_ENVIRONMENT.DIRECTORY_SEPARATOR.'module',

  // SF_APP_DIR sub-directories names
  'sf_app_i18n_dir_name'     => 'i18n',
  'sf_app_config_dir_name'   => 'config',
  'sf_app_lib_dir_name'      => 'lib',
  'sf_app_module_dir_name'   => 'modules',
  'sf_app_template_dir_name' => 'templates',

  // SF_APP_DIR directory structure
  'sf_app_config_dir'   => SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config',
  'sf_app_lib_dir'      => SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'lib',
  'sf_app_module_dir'   => SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'modules',
  'sf_app_template_dir' => SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'templates',
  'sf_app_i18n_dir'     => SF_ROOT_DIR.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'i18n',

  // SF_APP_MODULE_DIR sub-directories names
  'sf_app_module_action_dir_name'   => 'actions',
  'sf_app_module_template_dir_name' => 'templates',
  'sf_app_module_lib_dir_name'      => 'lib',
  'sf_app_module_view_dir_name'     => 'views',
  'sf_app_module_validate_dir_name' => 'validate',
  'sf_app_module_config_dir_name'   => 'config',
));

?>