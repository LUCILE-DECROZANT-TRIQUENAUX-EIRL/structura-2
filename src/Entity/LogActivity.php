<?php

namespace App\Entity;

use App\Repository\LogActivityRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;

#[ORM\Entity(repositoryClass: LogActivityRepository::class)]
#[ORM\Index(columns: ['username'], name: 'log_activity_user_index')]
#[ORM\Index(columns: ['object_class'], name: 'log_activity_class_index')]
#[ORM\Index(columns: ['logged_at'], name: 'log_activity_date_index')]
#[ORM\Index(columns: ['object_id', 'object_class', 'version'], name: 'log_activity_version_index')]
class LogActivity extends AbstractLogEntry
{
    /**
     * @return string|null
     */
    public function getModelName(): ?string
    {
        $modelName = '';
        foreach (explode('\\', $this->getObjectClass()) as $key => $part) {
            if ($key > 1) {
                if ($key > 2) {
                    $modelName .= '\\';
                }
                $modelName .= $part;
            }
        }
        return $modelName;
    }

    /**
     * Format data in an associative array of form [column => data]
     * @return array
     */
    public function getDataAsArray(): array
    {
        $record = [];
        foreach ($this->getData() ?? [] as $column => $data) {
            $record[$column] = $data;
        }
        return $record;
    }
}
