<?php

namespace Symfony\Bundle\FrameworkBundle\DependencyInjection;

use Symfony\Components\DependencyInjection\Extension\Extension;
use Symfony\Components\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Components\DependencyInjection\Resource\FileResource;
use Symfony\Components\DependencyInjection\ContainerBuilder;
use Symfony\Components\DependencyInjection\Reference;
use Symfony\Components\DependencyInjection\Definition;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * WebExtension.
 *
 * @package    Symfony
 * @subpackage Bundle_FrameworkBundle
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class WebExtension extends Extension
{
    protected $resources = array(
        'templating' => 'templating.xml',
        'web'        => 'web.xml',
        // validation.xml conflicts with the naming convention for XML
        // validation mapping files, so call it validator.xml
        'validation' => 'validator.xml',
    );

    protected $bundleDirs = array();
    protected $bundles = array();

    public function __construct(array $bundleDirs, array $bundles)
    {
        $this->bundleDirs = $bundleDirs;
        $this->bundles = $bundles;
    }

    /**
     * Loads the web configuration.
     *
     * @param array                                                        $config        An array of configuration settings
     * @param \Symfony\Components\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('controller_manager')) {
            $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
            $loader->load($this->resources['web']);
        }

        if (isset($config['ide']) && 'textmate' === $config['ide']) {
            $container->setParameter('debug.file_link_format', 'txmt://open?url=file://%%f&line=%%l');
        }

        if (isset($config['toolbar']) && $config['toolbar']) {
            $config['profiler'] = true;
        }

        if (isset($config['profiler'])) {
            if ($config['profiler']) {
                if (!$container->hasDefinition('profiler')) {
                    $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
                    $loader->load('profiling.xml');
                    $loader->load('collectors.xml');
                }
            } elseif ($container->hasDefinition('profiler')) {
                $container->getDefinition('profiling')->clearAnnotations();
            }
        }

        // toolbar need to be registered after the profiler
        if (isset($config['toolbar'])) {
            if ($config['toolbar']) {
                if (!$container->hasDefinition('debug.toolbar')) {
                    $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
                    $loader->load('toolbar.xml');
                }
            } elseif ($container->hasDefinition('debug.toolbar')) {
                $container->getDefinition('debug.toolbar')->clearAnnotations();
            }
        }

        if (isset($config['validation']['enabled'])) {
            if ($config['validation']['enabled']) {
                if (!$container->hasDefinition('validator')) {
                    $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
                    $loader->load($this->resources['validation']);
                }

                $xmlMappingFiles = array();
                $yamlMappingFiles = array();
                $messageFiles = array();

                // default entries by the framework
                $xmlMappingFiles[] = __DIR__.'/../../../Components/Form/Resources/config/validation.xml';
                $messageFiles[] = __DIR__ . '/../../../Components/Validator/Resources/i18n/messages.en.xml';
                $messageFiles[] = __DIR__ . '/../../../Components/Form/Resources/i18n/messages.en.xml';

                foreach ($this->bundles as $className) {
                    $tmp = dirname(str_replace('\\', '/', $className));
                    $namespace = str_replace('/', '\\', dirname($tmp));
                    $bundle = basename($tmp);

                    foreach ($this->bundleDirs as $dir) {
                        if (file_exists($file = $dir.'/'.$bundle.'/Resources/config/validation.xml')) {
                            $xmlMappingFiles[] = realpath($file);
                        }
                        if (file_exists($file = $dir.'/'.$bundle.'/Resources/config/validation.yml')) {
                            $yamlMappingFiles[] = realpath($file);
                        }

                        // TODO do we really want the message files of all cultures?
                        foreach (glob($dir.'/'.$bundle.'/Resources/i18n/messages.*.xml') as $file) {
                            $messageFiles[] = realpath($file);
                        }
                    }
                }

                $xmlFilesLoader = new Definition(
                    $container->getParameter('validator.mapping.loader.xml_files_loader.class'),
                    array($xmlMappingFiles)
                );

                $yamlFilesLoader = new Definition(
                    $container->getParameter('validator.mapping.loader.yaml_files_loader.class'),
                    array($yamlMappingFiles)
                );

                $container->setDefinition('validator.mapping.loader.xml_files_loader', $xmlFilesLoader);
                $container->setDefinition('validator.mapping.loader.yaml_files_loader', $yamlFilesLoader);
                $container->setParameter('validator.message_interpolator.files', $messageFiles);

                foreach ($xmlMappingFiles as $file) {
                    $container->addResource(new FileResource($file));
                }

                foreach ($yamlMappingFiles as $file) {
                    $container->addResource(new FileResource($file));
                }

                foreach ($messageFiles as $file) {
                    $container->addResource(new FileResource($file));
                }

                if (isset($config['validation']['annotations']) && $config['validation']['annotations'] === true) {
                    $annotationLoader = new Definition($container->getParameter('validator.mapping.loader.annotation_loader.class'));
                    $container->setDefinition('validator.mapping.loader.annotation_loader', $annotationLoader);

                    $loader = $container->getDefinition('validator.mapping.loader.loader_chain');
                    $arguments = $loader->getArguments();
                    array_unshift($arguments[0], new Reference('validator.mapping.loader.annotation_loader'));
                    $loader->setArguments($arguments);
                }
            } elseif ($container->hasDefinition('validator')) {
                $container->getDefinition('validator')->clearAnnotations();
            }
        }
    }

    /**
     * Loads the templating configuration.
     *
     * @param array                                                        $config        An array of configuration settings
     * @param \Symfony\Components\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
     */
    public function templatingLoad($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('templating')) {
            $loader = new XmlFileLoader($container, __DIR__.'/../Resources/config');
            $loader->load($this->resources['templating']);
        }

        if (array_key_exists('escaping', $config)) {
            $container->setParameter('templating.output_escaper', $config['escaping']);
        }

        if (array_key_exists('assets_version', $config)) {
            $container->setParameter('templating.assets.version', $config['assets_version']);
        }

        // path for the filesystem loader
        if (isset($config['path'])) {
            $container->setParameter('templating.loader.filesystem.path', $config['path']);
        }

        // loaders
        if (isset($config['loader'])) {
            $loaders = array();
            $ids = is_array($config['loader']) ? $config['loader'] : array($config['loader']);
            foreach ($ids as $id) {
                $loaders[] = new Reference($id);
            }

            if (1 === count($loaders)) {
                $container->setAlias('templating.loader', (string) $loaders[0]);
            } else {
                $container->getDefinition('templating.loader.chain')->addArgument($loaders);
                $container->setAlias('templating.loader', 'templating.loader.chain');
            }
        }

        // cache?
        $container->setParameter('templating.loader.cache.path', null);
        if (isset($config['cache'])) {
            // wrap the loader with some cache
            $container->setDefinition('templating.loader.wrapped', $container->findDefinition('templating.loader'));
            $container->setDefinition('templating.loader', $container->getDefinition('templating.loader.cache'));
            $container->setParameter('templating.loader.cache.path', $config['cache']);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://www.symfony-project.org/schema/dic/symfony';
    }

    public function getAlias()
    {
        return 'web';
    }
}
