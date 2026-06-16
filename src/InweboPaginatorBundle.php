<?php

declare(strict_types=1);

namespace Inwebo\PaginatorBundle;

use Inwebo\PaginatorBundle\Twig\PaginationExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Twig\Environment;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class InweboPaginatorBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->extension('twig', [
            'paths' => [
                '%kernel.project_dir%/vendor/inwebo/paginator-bundle/templates' => 'InweboPaginator',
            ],
        ]);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->set('inwebo_paginator.twig.pagination_extension', PaginationExtension::class)
                ->arg('$environment', service(Environment::class))
                ->tag('twig.extension')
                ->autowire(false)
                ->public()
        ;
    }
}
