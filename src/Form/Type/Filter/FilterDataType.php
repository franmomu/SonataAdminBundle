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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', $options['operator_type'], $options['operator_options'] + ['required' => false])
            ->add('value', $options['field_type'], $options['field_options'] + ['required' => false]);

        $builder
            ->addModelTransformer(new CallbackTransformer(
                function (?FilterData $filterData) {
                    if (null === $filterData) {
                        return null;
                    }

                    $data = [
                        'type' => $filterData->hasType() ? $filterData->getType() : null,
                    ];

                    if ($filterData->hasValue()) {
                        $data['value'] = $filterData->getValue();
                    }

                    return $data;
                },
                function (array $data) {
                    return FilterData::fromArray($data);
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('operator_type')
            ->setRequired('field_type');

        $resolver->setDefaults([
            'operator_options' => [],
            'field_options' => [],
        ]);
    }
}
