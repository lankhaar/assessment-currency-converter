<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\AllowedIp;
use App\Entity\CurrencyExchangeRate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(CurrencyExchangeRateCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('EZPZ Currency Converter')
            ->setDefaultColorScheme('dark')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Currency management', 'fas fa-coins', CurrencyExchangeRate::class);
        yield MenuItem::subMenu('Access Control', 'fas fa-lock')->setSubItems([
            MenuItem::linkToCrud('Allowed IPs', 'fas fa-location-crosshairs', AllowedIp::class),
            MenuItem::linkToCrud('Users', 'fas fa-user', User::class),
        ]);
    }
}
