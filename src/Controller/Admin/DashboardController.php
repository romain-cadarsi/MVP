<?php

namespace App\Controller\Admin;

use App\Entity\Categorie;
use App\Entity\MasterCategorie;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Www');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Categorie', 'fas fa-file', Categorie::class)->setDefaultSort(['id' => 'DESC']);
        yield MenuItem::linkToCrud('Master Categorie', 'fas fa-crown', MasterCategorie::class)->setDefaultSort(['id' => 'DESC']);
    }
}
