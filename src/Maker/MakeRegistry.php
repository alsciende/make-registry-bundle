<?php

namespace Alsciende\MakeRegistryBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\CliOutputHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class MakeRegistry extends AbstractMaker
{

    public function __construct(
        #[Autowire(service: 'maker.generator')]
        public readonly Generator $generator,
    )
    {
    }

    public static function getCommandName(): string
    {
        return 'make:registry';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new service registry';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $command
            ->addArgument('name', InputArgument::OPTIONAL, sprintf('Base name of the interface and services to create (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->addOption('tag', 't', InputOption::VALUE_REQUIRED, 'Tag to use for the services')
            ->addOption('alias', 'a', InputOption::VALUE_REQUIRED, 'Alias to use for the registry')
            ->setHelp(file_get_contents(__DIR__.'/../Resources/help/MakeRegistry.txt'))
        ;
    }

    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            AutoconfigureTag::class,
            'dependency-injection'
        );
        $dependencies->addClassDependency(
            TaggedIterator::class,
            'dependency-injection'
        );
        $dependencies->addClassDependency(
            AsAlias::class,
            'dependency-injection'
        );
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $name = $input->getArgument('name');
        $tagName = $input->getOption('tag') ?? 'app.' . Str::asSnakeCase($name);
        $aliasName = $input->getOption('alias') ?? $tagName . '_registry';

        $interfaceClassDetails = $generator->createClassNameDetails(
            $name . 'Interface',
            $name
        );
        $interfaceShortName = $interfaceClassDetails->getShortName();

        $registryClassDetails = $generator->createClassNameDetails(
            $name . 'Registry',
            $name
        );

        $sampleClassDetails = $generator->createClassNameDetails(
            'Sample' . $name,
            $name
        );

        if (class_exists($interfaceClassDetails->getFullName())) {
            $io->error('The interface already exists! Aborting.');
        }

        if (class_exists($registryClassDetails->getFullName())) {
            $io->error('The registry already exists! Aborting.');
        }

        if (class_exists($sampleClassDetails->getFullName())) {
            $io->error('The sample already exists! Aborting.');
        }

        $variables = [
            'name' => $name,
            'tag_name' => $tagName,
            'alias_name' => $aliasName,
            'interface_name' => $interfaceShortName,
        ];

        $interfacePath = $this->generator->generateClass(
            $interfaceClassDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/Interface.tpl.php',
            $variables
        );

        $registryPath = $this->generator->generateClass(
            $registryClassDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/Registry.tpl.php',
            $variables
        );

        $samplePath = $this->generator->generateClass(
            $sampleClassDetails->getFullName(),
            __DIR__.'/../Resources/skeleton/Sample.tpl.php',
            $variables
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text([
            sprintf(
                'Next: you can test the result with <info>%s debug:container --tag=%s</info> and <info>%s debug:container %s</info>',
                CliOutputHelper::getCommandPrefix(),
                $tagName,
                CliOutputHelper::getCommandPrefix(),
                $aliasName,
            ),
            '',
        ]);
    }
}