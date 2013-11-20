[php-amqplib](http://php.net/amqp) integration in Symfony2
===========

### Installation

#### Bundle and Dependencies

Add GieAmqpBundle to your application's `composer.json` file:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/gkozinski/amqp-bundle"
        }
    ],
    "require": {
        "gie/amqp-bundle": "dev-master"
    }
}
```

Install the bundle and its dependencies with the following command:

```bash
$ php composer.phar update gie/amqp-bundle
```

Enable the bundle in your application kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Gie\AmqpBundle\GieAmqpBundle(),
    );
}
```

#### Basic configuration

    #app/config/config.yml
    gie_amqp:
        connection:
            default:
                host: 127.0.0.1
                login: guest
                password: guest
                vhost: /
                timeout: 0
                channel:
                    default:
                        exchange:
                            default:
                                durable: true
                                type: direct
                        queue:
                            test_example:
                                routing_key: test.example
                                exchange: [default]
                                publisher: test.publisher.example
                                consumer:
                                    default:
                                        service: test.consumer.example
                                        class: Gie\AmqpBundle\Consumer\ConsumerExample
