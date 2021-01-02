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
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @phpstan-template T of object
 * @phpstan-extends AbstractAdmin<T>
 */
abstract class ServiceAbstractAdmin extends AbstractAdmin implements ServiceSubscriberInterface, ServiceAdminInterfaceInterface
{
    public function __construct(string $modelClass)
    {
        parent::__construct('sonata_deprecation_mute', $modelClass, 'sonata_deprecation_mute');
    }

    public function withCode(string $code): void
    {
        $this->code = $code;
    }

    protected $container;

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
            TranslatorInterface::class => TranslatorInterface::class,
            Pool::class => Pool::class,
            RouteGeneratorInterface::class => DefaultRouteGenerator::class,
            FactoryInterface::class => MenuFactory::class,
            RouteBuilderInterface::class => PathInfoBuilder::class,
            LabelTranslatorStrategyInterface::class => NativeLabelTranslatorStrategy::class,
        ];
    }

    public function getTranslator()
    {
        return $this->container->get(TranslatorInterface::class);
    }

    public function getConfigurationPool()
    {
        return $this->container->get(Pool::class);
    }

    public function getRouteGenerator()
    {
        return $this->container->get(RouteGeneratorInterface::class);
    }

    public function getMenuFactory()
    {
        return $this->container->get(FactoryInterface::class);
    }

    public function getRouteBuilder()
    {
        return $this->container->get(RouteBuilderInterface::class);
    }

    public function getLabelTranslatorStrategy()
    {
        return $this->container->get(LabelTranslatorStrategyInterface::class);
    }
}

