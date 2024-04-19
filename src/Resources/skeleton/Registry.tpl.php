<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsAlias('<?= $alias_name ?>')]
class <?= $class_name ?>
{
    /**
     * @var array<<?= $interface_name ?>>
     */
    private array $services = [];

    public function __construct(
        #[TaggedIterator('<?= $tag_name ?>')]
        iterable $services
    )
    {
        foreach ($services as $service) {
            if ($service instanceof <?= $interface_name ?>) {
                $name = $service->getName();
                if (array_key_exists($name, $this->services)) {
                    $duplicate = $this->services[$name];
                    throw new \LogicException(sprintf(
                        'Service name "%s" duplicate between %s and %s.',
                        $name,
                        get_class($duplicate),
                        get_class($service),
                    ));
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
     * @return array<<?= $interface_name ?>>
     */
    public function get<?= $name ?>s(): array
    {
        return $this->services;
    }

    public function get<?= $name ?>(string $name): <?= $interface_name ?>
    {
        if (array_key_exists($name, $this->services)) {
            return $this->services[$name];
        }

        throw new \UnexpectedValueException(sprintf(
            'Cannot find service "%s". Available services are %s.',
            $name,
            implode(',', $this->getNames())
        ));
    }
}