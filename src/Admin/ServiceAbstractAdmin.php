<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Admin;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuFactory;
use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Builder\RouteBuilderInterface;
use Sonata\AdminBundle\Route\DefaultRouteGenerator;
use Sonata\AdminBundle\Route\PathInfoBuilder;
use Sonata\AdminBundle\Route\RouteGeneratorInterface;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\AdminBundle\Translator\NativeLabelTranslatorStrategy;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @phpstan-template T of object
 * @phpstan-extends AbstractAdmin<T>
 */
abstract class ServiceAbstractAdmin extends AbstractAdmin implements ServiceSubscriberInterface, ServiceAdminInterfaceInterface
{
    protected $container;

    public function __construct(string $modelClass)
    {
        parent::__construct('', $modelClass, '');
    }

    public function withCode(string $code): void
    {
        $this->code = $code;
    }

    public function withBaseControllerName(string $baseControllerName): void
    {
        $this->baseControllerName = $baseControllerName;
    }

    /**
     * @internal
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    public static function getSubscribedServices(): array
    {
        return [
        ];
    }

    public function getTranslator()
    {
        return $this->container->get('translator');
    }

    public function getConfigurationPool()
    {
        return $this->container->get('configuration_pool');
    }

    public function getRouteGenerator()
    {
        return $this->container->get('route_generator');
    }

    public function getMenuFactory()
    {
        return $this->container->get('menu_factory');
    }

    public function getRouteBuilder()
    {
        return $this->container->get('route_builder');
    }

    public function getLabelTranslatorStrategy()
    {
        return $this->container->get('label_translator_strategy');
    }
}

