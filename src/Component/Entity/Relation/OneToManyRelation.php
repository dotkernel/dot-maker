<?php

declare(strict_types=1);

namespace Dot\Maker\Component\Entity\Relation;

use Dot\Maker\Component\Entity\Relations;

class OneToManyRelation extends AbstractRelation
{
    protected string $doctrineType = Relations::ONE_TO_MANY;

    public function getDefinition(): string
    {
        $attributes = [
            sprintf('targetEntity="%s"', $this->getPhpType()),
        ];

        if ($this->hasCascade()) {
            $attributes[] = sprintf('cascade=%s', $this->getCascade());
        }

        $attributes = implode(', ', $attributes);

        return <<<DEF
/**
     * @ORM\\$this->doctrineType($attributes)
     */
DEF;
    }
}
