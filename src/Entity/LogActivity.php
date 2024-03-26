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
}
