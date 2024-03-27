<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('strikeWhenDeleted', [$this, 'getStrikeWhenDeleted']),
            new TwigFunction('isDeletedEntity', [$this, 'isDeletedEntity']),
        ];
    }

    // return css classes to visually indicate a deletion of the entity
    public function isDeletedEntity(EntityDto $entityDto): ?string
    {
        $isDeleted = false;
        if ($entityDto->hasProperty('deletedAt')) {
            $isDeleted = !is_null($entityDto->getFields()->getByProperty('deleted_at')->getValue());
        }

        return $isDeleted ? 'text-decoration-line-through text-danger' : null;
    }
}
