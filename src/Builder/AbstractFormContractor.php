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

namespace Sonata\AdminBundle\Builder;

use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Builder\Exception\MissingAssociationAdminException;
use Sonata\AdminBundle\Builder\Exception\MissingTargetModelClassException;
use Sonata\AdminBundle\Builder\Exception\UnhandledAssociationTypeException;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Form\Type\ModelHiddenType;
use Sonata\AdminBundle\Form\Type\ModelReferenceType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Form\Type\ModelTypeList;
use Sonata\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
abstract class AbstractFormContractor implements FormContractorInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @return FormFactoryInterface
     */
    final public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * {@inheritdoc}
     */
    final public function getFormBuilder($name, array $options = [])
    {
        return $this->getFormFactory()->createNamedBuilder($name, FormType::class, null, $options);
    }

    /**
     * Returns the default options for a form field according to its form type and associated field description.
     *
     * @param string                    $type             the field's form type
     * @param FieldDescriptionInterface $fieldDescription the field's field description
     *
     * @return array The field's default options
     */
    public function getDefaultOptions($type, FieldDescriptionInterface $fieldDescription)
    {
        // NEXT_MAJOR: Check directly against $type when dropping Symfony <2.8 support
        $fqcn = $this->convertSonataFormTypeToFQCN($type);

        $this->ensureFormTypeCanHandleFieldDescription($fqcn, $fieldDescription);

        $options = [
            'sonata_field_description' => $fieldDescription,
        ];

        switch ($fqcn) {
            case ModelType::class:
            case ModelAutocompleteType::class:
                if ($fieldDescription->describesCollectionValuedAssociation()) {
                    $options['multiple'] = true;
                }
            // intentional fallthrough
            // no break
            case ModelTypeList::class:
            case ModelHiddenType::class:
            case ModelReferenceType::class:
                $options['class'] = $fieldDescription->getTargetEntity();
                $options['model_manager'] = $fieldDescription->getAdmin()->getModelManager();
                break;
            case AdminType::class:
                $options['delete'] = false;
                $options['data_class'] = $fieldDescription->getTargetEntity();
                break;
            case CollectionType::class:
                $options['type'] = AdminType::class;
                $options['modifiable'] = true;
                $options['type_options'] = [
                    'sonata_field_description' => $fieldDescription,
                    'data_class' => $fieldDescription->getTargetEntity(),
                ];
                break;
        }

        return $options;
    }

    /**
     * Ensures that an association target model class is defined for the given field description.
     *
     * @throws MissingTargetModelClassException if the field description has no target entity
     */
    final protected function ensureTargetModelClass(FieldDescriptionInterface $fieldDescription): void
    {
        if (!$fieldDescription->getTargetEntity()) {
            throw $this->createMissingTargetModelClassException($fieldDescription);
        }
    }

    /**
     * Ensures that an association admin is defined for the given field description.
     *
     * @throws MissingAssociationAdminException if the field description has no association admin
     */
    final protected function ensureAssociationAdmin(FieldDescriptionInterface $fieldDescription): void
    {
        if (!$fieldDescription->getAssociationAdmin()) {
            throw $this->createMissingAssociationAdminException($fieldDescription);
        }
    }

    /**
     * Ensures a field description describes an association type that can be handled by the given form type.
     *
     * @throws UnhandledAssociationTypeException if the field description has no association admin
     */
    final protected function ensureAssociationType(FieldDescriptionInterface $fieldDescription, string $type, string $formType): void
    {
        if (!\in_array($type, ['single-valued', 'collection-valued'], true)) {
            throw new \InvalidArgumentException(sprintf(
                'Second argument to %s must be either "single-valued" or "collection-valued"',
                __METHOD__
            ));
        }

        if ('single-valued' === $type && !$fieldDescription->describesSingleValuedAssociation()) {
            throw $this->createUnhandledAssociationTypeException($formType, $type, $fieldDescription, [
                CollectionType::class,
                ModelType::class,
                ModelAutocompleteType::class,
            ]);
        }

        if ('collection-valued' === $type && !$fieldDescription->describesCollectionValuedAssociation()) {
            throw $this->createUnhandledAssociationTypeException($formType, $type, $fieldDescription, [
                AdminType::class,
                ModelType::class,
                ModelTypeList::class,
                ModelAutocompleteType::class,
                ModelHiddenType::class,
                ModelReferenceType::class,
            ]);
        }
    }

    /**
     * Returns a `MissingAssociationAdminException` instance with a developer-friendly message,
     * to be thrown when a field description is missing an association admin.
     *
     * @return MissingAssociationAdminException
     */
    protected function createMissingAssociationAdminException(FieldDescriptionInterface $fieldDescription): MissingAssociationAdminException
    {
        $msg = "The current field `{$fieldDescription->getName()}` is not linked to an admin. Please create one";
        if ($fieldDescription->describesAssociation()) {
            if ($fieldDescription->getTargetEntity()) {
                $msg .= " for the target model: `{$fieldDescription->getTargetEntity()}`";
            }
            $msg .= ', make sure your association mapping is properly configured, or';
        } else {
            $msg .= ', and';
        }
        $msg .= ' use the `admin_code` option to link it.';

        return new MissingAssociationAdminException($msg);
    }

    /**
     * Returns a `MissingTargetModelClassException` instance with a developer-friendly message,
     * to be thrown when a field description is missing an association target model class.
     *
     * @return MissingTargetModelClassException
     */
    protected function createMissingTargetModelClassException(FieldDescriptionInterface $fieldDescription): MissingTargetModelClassException
    {
        return new MissingTargetModelClassException(sprintf(
            'The field `%s` in class `%s` does not have a target model class defined.'
            .' Please make sure your association mapping is properly configured.',
            $fieldDescription->getName(),
            $fieldDescription->getAdmin()->getClass()
        ));
    }

    /**
     * Returns a `UnhandledAssociationTypeException` instance with a developer-friendly message,
     * to be thrown when a form type can't handle the association type described by a field description.
     *
     * @param string[] $alternativeTypes
     *
     * @return UnhandledAssociationTypeException
     */
    protected function createUnhandledAssociationTypeException(
        string $formType,
        string $associationType,
        FieldDescriptionInterface $fieldDescription,
        array $alternativeTypes = []
    ) {
        $msg = sprintf('The `%s` type only handles %s associations.', $formType, $associationType);
        if ($alternativeTypes) {
            $msg .= sprintf(
                ' Try using %s%s',
                \count($alternativeTypes) > 1 ? 'one of ' : '',
                implode(', ', $alternativeTypes)
            );
        }
        $msg .= sprintf(' for field `%s`', $fieldDescription->getName());

        return new UnhandledAssociationTypeException($msg);
    }

    /**
     * Ensures a field's form type can handle the provided field description.
     */
    private function ensureFormTypeCanHandleFieldDescription(string $formType, FieldDescriptionInterface $fieldDescription)
    {
        switch ($formType) {
            case ModelType::class:
                $this->ensureTargetModelClass($fieldDescription);
                break;
            case ModelAutocompleteType::class:
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationAdmin($fieldDescription);
                break;
            case AdminType::class:
            case ModelTypeList::class:
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationType($fieldDescription, 'single-valued', $formType);
                $this->ensureAssociationAdmin($fieldDescription);
                break;
            case ModelReferenceType::class:
            case ModelHiddenType::class:
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationType($fieldDescription, 'single-valued', $formType);
                break;
            case CollectionType::class:
                $this->ensureTargetModelClass($fieldDescription);
                $this->ensureAssociationType($fieldDescription, 'collection-valued', $formType);
                $this->ensureAssociationAdmin($fieldDescription);
                break;
        }
    }

    private function convertSonataFormTypeToFQCN(string $type): string
    {
        switch ($type) {
            case 'sonata_type_model':
                return ModelType::class;
            case 'sonata_type_model_list':
                return ModelTypeList::class;
            case 'sonata_type_model_hidden':
                return ModelHiddenType::class;
            case 'sonata_type_model_autocomplete':
                return ModelAutocompleteType::class;
            case 'sonata_type_admin':
                return AdminType::class;
            case 'sonata_type_collection':
                return CollectionType::class;
            default:
                return $type;
        }
    }
}
