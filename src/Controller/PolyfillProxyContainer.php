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

namespace Sonata\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PolyfillProxyContainer extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function proxyCall($method, $arguments)
    {
        return $this->{$method}(...$arguments);
    }
}
