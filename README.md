Presentation
============

This Symfony bundle adds a `make:registry` command to the Console. 
Using this maker will create the building blocks of a specialized Service Registry in your app:
- an Interface describing the type of Service provided by the Registry
- a sample Service implementing the Interface
- a Service Registry configured to register all the services implementing your Interface

Example
=======

Executing `php bin/console make:registry OutputFormatter` will create the following classes:

```
// src/OutputFormatter/OutputFormatterInterface.php
<?php

namespace App\OutputFormatter;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.output_formatter')]
interface OutputFormatterInterface
{
    public function getName(): string;
}
```

```
// src/OutputFormatter
<?php

namespace App\OutputFormatter;

class SampleOutputFormatter implements OutputFormatterInterface
{
    public function getName(): string
    {
        return 'sample';
    }
}
```

```
// src/OutputFormatter/OutputFormatterRegistry.php
<?php

namespace App\OutputFormatter;

use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsAlias('app.output_formatter_registry')]
class OutputFormatterRegistry
{
    /**
     * @var array<OutputFormatterInterface>
     */
    private array $services = [];

    public function __construct(
        #[TaggedIterator('app.output_formatter')]
        iterable $services
    ) {
        foreach ($services as $service) {
            if ($service instanceof OutputFormatterInterface) {
                $name = $service->getName();

                if (array_key_exists($name, $this->services)) {
                    $duplicate = $this->services[$name];

                    throw new \LogicException(sprintf('Service name "%s" duplicate between %s and %s.', $name, $duplicate::class, $service::class));
                }
                $this->services[$name] = $service;
            }
        }
    }

    /**
     * @return array<string>
     */
    public function getNames(): array
    {
        return array_keys($this->services);
    }

    /**
     * @return array<OutputFormatterInterface>
     */
    public function getOutputFormatters(): array
    {
        return $this->services;
    }

    public function getOutputFormatter(string $name): OutputFormatterInterface
    {
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        }

        throw new \UnexpectedValueException(sprintf('Cannot find service "%s". Available services are %s.', $name, implode(',', $this->getNames())));
    }
}
```

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
composer require --dev alsciende/make-registry-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require --dev alsciende/make-registry-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Alsciende\Bundle\MakeRegistryBundle\AlsciendeMakeRegistryBundle::class => ['all' => true],
];
```
