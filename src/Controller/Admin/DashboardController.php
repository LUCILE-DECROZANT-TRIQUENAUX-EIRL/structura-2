<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\LogActivityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private LogActivityRepository $logActivityRepository;

    public function __construct(LogActivityRepository $logActivityRepository)
    {
        $this->logActivityRepository = $logActivityRepository;
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        $logActivities = $this->logActivityRepository->findAll();
        return $this->render('admin/dashboard.html.twig', ['logActivities' => $logActivities]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Structura');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Users'),
//            MenuItem::linkToCrud('Comments', 'fa fa-comment', Comment::class),
            MenuItem::linkToCrud('Users', 'fa fa-user', User::class),
            MenuItem::linkToCrud('Add User', 'fa fa-user-plus', User::class)
                ->setAction('new'),
        ];
//        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
