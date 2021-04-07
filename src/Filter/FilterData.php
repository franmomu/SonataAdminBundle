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

namespace Sonata\AdminBundle\Filter;

final class FilterData
{
    /**
     * @var ?int
     */
    private $type;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $hasValue;

    private function __construct()
    {
        $this->hasValue = false;
    }

    public static function fromArray(array $data): self
    {
        $filterData = new self();

        if (isset($data['type'])) {
            $type = $data['type'];

            if (!is_numeric($type)) {
                throw new \InvalidArgumentException(sprintf(
                    'The "type" value MUST be "null" or "int", %s given.',
                    \is_object($type) ? 'instance of '.\get_class($type) : \gettype($type)
                ));
            }

            $filterData->type = (int) $type;
        }

        if (\array_key_exists('value', $data)) {
            $filterData->value = $data['value'];
            $filterData->hasValue = true;
        }

        return $filterData;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function hasType(): bool
    {
        return null !== $this->type;
    }

    public function isType(int $type): bool
    {
        return $this->type === $type;
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }
}
