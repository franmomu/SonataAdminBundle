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

namespace Sonata\AdminBundle\Tests\Builder;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\AbstractFormContractor;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Form\Type\ModelTypeList;
use Sonata\AdminBundle\Builder\Exception\MissingAssociationAdminException;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Builder\Exception\UnhandledAssociationTypeException;
use Sonata\AdminBundle\Form\Type\ModelReferenceType;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
class AbstractFormContractorTest extends TestCase
{
    /**
     * @dataProvider testFieldDescriptionValidationProvider
     */
    public function testFieldDescriptionValidation(
        string $formType,
        string $targetEntity,
        string $associationType,
        string $associationAdmin,
        string $expectedException
    ): void
    {
        $admin = $this->getMockBuilder(AdminInterface::class)->getMock();
        $admin->method('getClass')->willReturn('Foo');

        $fieldDescription = $this->getMockBuilder(FieldDescriptionInterface::class)->getMock();
        $fieldDescription->method('getAdmin')->willReturn($admin);
        $fieldDescription->method('getAssociationAdmin')->willReturn($associationAdmin);
        $fieldDescription->method('getTargetEntity')->willReturn($targetEntity);
        $isSingleAssociation = 'single' === $associationType;
        $fieldDescription->method('describesSingleValuedAssociation')->willReturn($isSingleAssociation);
        $fieldDescription->method('describesCollectionValuedAssociation')->willReturn(!$isSingleAssociation);

        $formContractor = $this->createMock(AbstractFormContractor::class);

        $this->expectException($expectedException);
        $formContractor->getDefaultOptions($formType, $fieldDescription);
    }

    public function testFieldDescriptionValidationProvider(): array
    {
        return [
            // MissingTargetModelClassException
            'AdminType, no target entity' => [
                AdminType::class,
                null,
                'single',
                'FooAdmin',
                MissingTargetModelClassException::class,
            ],
            'CollectionType, no target entity' => [
                CollectionType::class,
                null,
                'collection',
                'FooAdmin',
                MissingTargetModelClassException::class,
            ],
            'ModelType, no target entity' => [
                ModelType::class,
                null,
                'single',
                'FooAdmin',
                MissingTargetModelClassException::class,
            ],
            'ModelTypeList, no target entity' => [
                ModelTypeList::class,
                null,
                'single',
                'FooAdmin',
                MissingTargetModelClassException::class,
            ],
            'ModelHiddenType, no target entity' => [
                ModelHiddenType::class,
                null,
                'single',
                'FooAdmin',
                MissingTargetModelClassException::class,
            ],
            'ModelReferenceType, no target entity' => [
                ModelReferenceType::class,
                null,
                'single',
                'FooAdmin',
                MissingTargetModelClassException::class,
            ],
            'ModelAutocompleteType, no target entity' => [
                ModelAutocompleteType::class,
                null,
                'single',
                'FooAdmin',
                MissingTargetModelClassException::class,
            ],
            // UnhandledAssociationTypeException
            'AdminType, collection-valued association' => [
                AdminType::class,
                'Foo',
                'collection',
                'FooAdmin',
                UnhandledAssociationTypeException::class,
            ],
            'ModelTypeList, collection-valued association' => [
                ModelTypeList::class,
                'Foo',
                'collection',
                'FooAdmin',
                UnhandledAssociationTypeException::class,
            ],
            'ModelHiddenType, collection-valued association' => [
                ModelHiddenType::class,
                'Foo',
                'collection',
                'FooAdmin',
                UnhandledAssociationTypeException::class,
            ],
            'ModelReferenceType, collection-valued association' => [
                ModelReferenceType::class,
                'Foo',
                'collection',
                'FooAdmin',
                UnhandledAssociationTypeException::class,
            ],
            'CollectionType, singled-valued association' => [
                CollectionType::class,
                'Foo',
                'single',
                'FooAdmin',
                UnhandledAssociationTypeException::class,
            ],
            // MissingAssociationAdminException
            'AdminType, no associationAdmin' => [
                AdminType::class,
                'Foo',
                'single',
                null,
                MissingAssociationAdminException::class,
            ],
            'CollectionType, no associationAdmin' => [
                CollectionType::class,
                'Foo',
                'collection',
                null,
                MissingAssociationAdminException::class,
            ],
            'ModelTypeList, no associationAdmin' => [
                ModelTypeList::class,
                'Foo',
                'single',
                null,
                MissingAssociationAdminException::class,
            ],
            'ModelAutocompleteType, no associationAdmin' => [
                ModelAutocompleteType::class,
                'Foo',
                'single',
                null,
                MissingAssociationAdminException::class,
            ],
        ];
    }
}
