<?php

namespace App\Repository;

use App\Entity\LogActivity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

/**
 * @implements ServiceEntityRepositoryInterface<LogActivity>
 *
 * @method LogActivity|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogActivity|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogActivity[]    findAll()
 * @method LogActivity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogActivityRepository extends LogEntryRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        $logActivityManager = $registry->getManagerForClass(LogActivity::class);
        parent::__construct($logActivityManager, $logActivityManager->getClassMetadata(LogActivity::class));
    }
}
