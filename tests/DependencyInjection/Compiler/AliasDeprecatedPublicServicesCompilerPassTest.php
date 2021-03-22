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

namespace Sonata\AdminBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\Compiler\AliasDeprecatedPublicServicesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

final class AliasDeprecatedPublicServicesCompilerPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container
            ->register('foo')
            ->setPublic(true)
            ->addTag('sonata.container.private', ['version' => '1.2']);

        (new AliasDeprecatedPublicServicesCompilerPass())->process($container);

        $this->assertTrue($container->hasAlias('foo'));

        $alias = $container->getAlias('foo');

        $this->assertSame('.sonata.container.private.foo', (string) $alias);
        $this->assertTrue($alias->isPublic());
        $this->assertFalse($alias->isPrivate());
        $this->assertSame([
            'package' => 'sonata-project/admin-bundle',
            'version' => '1.2',
            'message' => 'Accessing the "foo" service directly from the container is deprecated, use dependency injection instead.',
        ], $alias->getDeprecation('foo'));
    }

    public function testProcessWithMissingAttribute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "version" attribute is mandatory for the "sonata.container.private" tag on the "foo" service.');

        $container = new ContainerBuilder();
        $container
            ->register('foo')
            ->addTag('sonata.container.private', []);

        (new AliasDeprecatedPublicServicesCompilerPass())->process($container);
    }

    public function testProcessWithNonPublicService(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The "foo" service is private: it cannot have the "sonata.container.private" tag.');

        $container = new ContainerBuilder();
        $container
            ->register('foo')
            ->addTag('sonata.container.private', ['package' => 'foo/bar', 'version' => '1.2']);

        (new AliasDeprecatedPublicServicesCompilerPass())->process($container);
    }
}
