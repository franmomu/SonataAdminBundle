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

namespace Sonata\AdminBundle\Tests\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\DateTimeRangeType;
use Sonata\Form\Type\DateTimeRangeType as FormDateTimeRangeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DateTimeRangeTypeTest extends TypeTestCase
{
    public function testDefaultOptions(): void
    {
        $form = $this->factory->create($this->getTestedType());

        $view = $form->createView();

        $this->assertFalse($view->children['type']->vars['required']);
        $this->assertTrue($view->children['value']->vars['required']);
    }

    protected function getTestedType(): string
    {
        return DateTimeRangeType::class;
    }
}
