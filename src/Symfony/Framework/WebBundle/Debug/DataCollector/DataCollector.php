<?php

namespace Symfony\Framework\WebBundle\Debug\DataCollector;

use Symfony\Components\DependencyInjection\ContainerInterface;

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * 
 *
 * @package    symfony
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
abstract class DataCollector implements DataCollectorInterface
{
  protected $manager;
  protected $container;
  protected $data;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function getData()
  {
    if (null === $this->data)
    {
      $this->data = $this->collect();
    }

    return $this->data;
  }

  abstract protected function collect();

  public function setCollectorManager(DataCollectorManager $manager)
  {
    $this->manager = $manager;
  }
}
