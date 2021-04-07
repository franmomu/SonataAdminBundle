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

namespace Sonata\AdminBundle\Form\Type\Filter;

use Sonata\AdminBundle\Filter\FilterData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilterDataType extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', $options['operator_type'], array_merge(['required' => false], $options['operator_options']))
            ->add('value', $options['field_type'], array_merge(['required' => false], $options['field_options']));

        $builder
            ->addModelTransformer(new CallbackTransformer(
                static function (?FilterData $filterData) {
                    if (null === $filterData) {
                        return null;
                    }

                    $data = [
                        'type' => $filterData->getType(),
                    ];

                    if ($filterData->hasValue()) {
                        $data['value'] = $filterData->getValue();
                    }

                    return $data;
                },
                static function (array $data) {
                    return FilterData::fromArray($data);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['operator_type', 'field_type'])
            ->setAllowedTypes('operator_type', 'string')
            ->setAllowedTypes('field_type', 'string')
            ->setAllowedTypes('operator_options', 'array')
            ->setAllowedTypes('field_options', 'array');

        $resolver->setDefaults([
            'operator_options' => [],
            'field_options' => [],
        ]);
    }
}
