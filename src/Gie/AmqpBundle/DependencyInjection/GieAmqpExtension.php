<?php

namespace Gie\AmqpBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class GieAmqpExtension extends Extension
{
    /**
     *
     * @var ContainerBuilder
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->defineServices($config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Define connection services based on configuration and
     * depends on them channel services.
     *
     * @param array $config
     */
    private function defineServices($config)
    {
        foreach ($config['connection'] as $name => $settings) {
            $connectionService = 'gie_amqp.connection.' . $name;
            $credentials = [
                'host' => $settings['host'],
                'port' => $settings['port'],
                'vhost' => $settings['vhost'],
                'login' => $settings['login'],
                'password' => $settings['password'],
            ];

            $this->defineServiceByStandardizedWay(
                    'connection',
                    $connectionService,
                    function ($definition) use ($credentials, $settings) {
                        $definition->addArgument($credentials);
                        $definition->addArgument($settings['timeout']);
                    }
            );

            $this->defineChannelServices($settings['channel'], $connectionService);
        }
    }

    /**
     * Define channel services based on configuration and
     * depends on them exchange and queue services.
     *
     * @param array $channelConfig
     * @param string $connectionService connection service id
     */
    private function defineChannelServices($channelConfig, $connectionService)
    {
        foreach ($channelConfig as $name => $settings) {
            $channelService = $connectionService . '.channel.' . $name;

            $this->defineServiceByStandardizedWay(
                    'channel',
                    $channelService,
                    function ($definition) use ($connectionService) {
                        $definition->addArgument(new Reference($connectionService));
                    }
            );

            $this->defineExchangeServices($settings['exchange'], $channelService);
            $this->defineQueueServices($settings['queue'], $channelService);
        }
    }

    /**
     * Define exchange servieces based on configuration
     *
     * @param array $exhangeConfig
     * @param string $channelService channel service id
     */
    private function defineExchangeServices($exhangeConfig, $channelService)
    {
        foreach ($exhangeConfig as $name => $settings) {
            $exchangeService = $channelService . '.exchange.' . $name;
            $settings['name'] = $name;

            $this->defineServiceByStandardizedWay(
                    'exchange',
                    $exchangeService,
                    function ($definition) use ($channelService, $settings) {
                        $definition->addArgument(new Reference($channelService));
                        $definition->addArgument($settings);
                    }
            );
        }
    }

    /**
     * Define queue services based on configuration
     *
     * @param array $queueConfig
     * @param string $channelService channel service id
     */
    private function defineQueueServices($queueConfig, $channelService)
    {
        foreach ($queueConfig as $name => $settings) {
            $queueService = $channelService . '.queue.' . $name;
            $settings['name'] = $name;
            $exchanges = array();

            // load all bind to queue exchange services before load queue service
            foreach ($settings['exchange'] as $exchangeName) {
                $exchangeService = $channelService . '.exchange.' . $exchangeName;
                $exchanges[$exchangeName] = new Reference($exchangeService);
            }

            $this->defineServiceByStandardizedWay(
                    'queue',
                    $queueService,
                    function ($definition) use ($channelService, $settings, $exchanges) {
                        $definition->addArgument(new Reference($channelService));
                        $definition->addArgument($settings);
                        $definition->addArgument($channelService);
                        $definition->addArgument($exchanges); // load exchanges
                    }
            );

            foreach ($settings['exchange'] as $exchangeName) {
                $this->definePublisherService(
                        $channelService . '.exchange.' . $exchangeName,
                        $exchangeName,
                        $settings,
                        $queueService
                );
            }

            $this->defineConsumerServices($settings['consumer'], $queueService);
        }
    }

    /**
     * Define publisher service
     *
     * @param string $exchangeService exchange service id
     * @param string $exchangeName exchange name
     * @param array $queueSettings
     * @param array $queueService queue service id
     */
    private function definePublisherService($exchangeService, $exchangeName, $queueSettings, $queueService)
    {
        $publisherService = $queueSettings['publisher'] . '.' . $exchangeName;
        $serviceClass = 'Gie\AmqpBundle\Publisher';
        $factoryClass = 'Gie\AmqpBundle\DependencyInjection\Factory\PublisherFactory';

        $definition = new Definition();
        $definition->setClass($serviceClass);
        $definition->setFactoryClass($factoryClass);
        $definition->setFactoryMethod('get');

        $definition->addArgument(new Reference($exchangeService));
        $definition->addArgument($queueSettings['routing_key']);
        // load queue before publish
        $definition->addArgument(new Reference($queueService));

        $this->container->setDefinition($publisherService, $definition);
    }

    /**
     * Define consumer servieces
     *
     * @param array $consumerConfig
     * @param string $queueService queue service
     */
    private function defineConsumerServices($consumerConfig, $queueService)
    {
        foreach ($consumerConfig as $settings) {
            $definition = new Definition();
            $definition->setClass($settings['class']);
            $definition->setFactoryClass('Gie\AmqpBundle\DependencyInjection\Factory\ConsumerFactory');
            $definition->setFactoryMethod('get');

            $definition->addArgument($settings['class']);
            $definition->addArgument(new Reference($queueService));
            $definition->addArgument($settings['count']);

            if (!empty($settings['services']) && is_array($settings['services'])) {
                foreach ($settings['services'] as $serviceInfo) {
                    $services[$serviceInfo['setter']] = new Reference($serviceInfo['service']);
                }
                $definition->addArgument($services);
            }

            $this->container->setDefinition($settings['service'], $definition);
        }
    }

    /**
     * Stadardized way to create services definition
     * like connection, channel, exchange, queue
     *
     * @param string $serviceType connection, channel, exchange, queue
     * @param string $service service id
     * @param callable $callback additional definition operation
     */
    private function defineServiceByStandardizedWay($serviceType, $service, callable $callback)
    {
        $serviceClass = 'AMQP' . ucfirst($serviceType);
        $factoryClass = 'Gie\AmqpBundle\DependencyInjection\Factory\\' .
                ucfirst($serviceType) . 'Factory';

        $definition = new Definition();
        $definition->setClass($serviceClass);
        $definition->setFactoryClass($factoryClass);
        $definition->setFactoryMethod('get');

        // for example: to set arguments
        $callback($definition);

        $this->container->setDefinition($service, $definition);
    }
}
