<?php
namespace Oro\Bundle\MeasureBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * Load measure bundle configuration from any bundles
 *
 *
 */
class OroMeasureExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // retrieve each measure config from bundles
        $measuresConfig = array();
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()).'/Resources/config/measure.yml')) {
                // merge measures configs
                if (empty($measuresConfig)) {
                    $measuresConfig = Yaml::parse(realpath($file));
                } else {
                    $entities = Yaml::parse(realpath($file));
                    foreach ($entities['measures_config'] as $family => $familyConfig) {
                        // merge result with already existing family config to add custom units
                        if (isset($measuresConfig['measures_config'][$family])) {
                            $measuresConfig['measures_config'][$family]['units'] =
                                array_merge(
                                    $measuresConfig['measures_config'][$family]['units'],
                                    $familyConfig['units']
                                );
                        } else {
                            $measuresConfig['measures_config'][$family]= $familyConfig;
                        }
                    }
                }
            }
        }
        $configs[]= $measuresConfig;
        // process configurations to validate and merge
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // load service
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        // set measures config
        $container->setParameter('oro_measure.measures_config', $config);

        $manager = $container
            ->getDefinition('oro_measure.manager')
            ->addMethodCall('setMeasureConfig', $config);
    }
}
