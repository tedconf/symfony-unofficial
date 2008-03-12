<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfGeneratorConfigHandler.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfGeneratorConfigHandler.class.php 3203 2007-01-09 18:32:54Z fabien $
 */
class sfGeneratorConfigHandler extends sfYamlConfigHandler
{
  /**
   * Executes this configuration handler.
   *
   * @param array An array of absolute filesystem path to a configuration file
   *
   * @return string Data to be written to a cache file
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable
   * @throws sfParseException If a requested configuration file is improperly formatted
   * @throws sfInitializationException If a generator.yml key check fails
   */
  public function execute($configFiles)
  {
    // parse the yaml
    $config = $this->parseYamls($configFiles);
    if (!$config)
    {
      return '';
    }

    if (!isset($config['generator']))
    {
      throw new sfParseException(sprintf('Configuration file "%s" must specify a generator section', $configFiles[1] ? $configFiles[1] : $configFiles[0]));
    }

    $config = $config['generator'];

    if (!isset($config['class']))
    {
      throw new sfParseException(sprintf('Configuration file "%s" must specify a generator class section under the generator section', $configFiles[1] ? $configFiles[1] : $configFiles[0]));
    }

    foreach (array('fields', 'list', 'edit') as $section)
    {
      if (isset($config[$section]))
      {
        throw new sfParseException(sprintf('Configuration file "%s" can specify a "%s" section but only under the param section', $configFiles[1] ? $configFiles[1] : $configFiles[0], $section));
      }
    }

    // generate class and add a reference to it
    $generatorManager = new sfGeneratorManager();
    $generatorManager->initialize();

    // generator parameters
    $generatorParam = (isset($config['param']) ? $config['param'] : array());

    // hack to find the module name
    preg_match('#'.sfConfig::get('sf_app_module_dir_name').'/([^/]+)/#', $configFiles[0], $match);
    $generatorParam['moduleName'] = $match[1];

    $data = $generatorManager->generate($config['class'], $generatorParam);

    // compile data
    $retval = "<?php\n".
              "// auto-generated by sfGeneratorConfigHandler\n".
              "// date: %s\n%s\n";
    $retval = sprintf($retval, date('Y/m/d H:i:s'), $data);

    return $retval;
  }
}
