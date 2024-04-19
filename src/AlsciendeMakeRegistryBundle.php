<?php

namespace Alsciende\MakeRegistryBundle;

use Alsciende\MakeRegistryBundle\DependencyInjection\AlsciendeMakeRegistryExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AlsciendeMakeRegistryBundle extends AbstractBundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new AlsciendeMakeRegistryExtension();
    }
}