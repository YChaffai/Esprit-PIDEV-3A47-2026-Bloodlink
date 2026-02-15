<?php

namespace App\Controller\Admin;

use App\Entity\Client;
use App\Entity\Don;
use App\Entity\DossierMed;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Pidev Admin')
            ->renderContentMaximized()
            ->setFaviconPath('favicon.ico');
    }

    /*public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('admin.css');
    }*/
    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('css/admin.css')   // ✅ if file is in public/css/admin.css
            ->addHtmlContentToHead('
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
        ')
            ->addJsFile('https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js')
            ->addJsFile('js/ajax-search.js'); // ✅ public/js/ajax-search.js
    }


    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Clients', 'fa fa-address-card', Client::class);
        yield MenuItem::linkToCrud('Dons', 'fa fa-hand-holding-heart', Don::class);
        yield MenuItem::linkToCrud('Dossier Médicaux', 'fa fa-notes-medical', DossierMed::class);
        yield MenuItem::linkToCrud('Clients', 'fa fa-address-card', Client::class);
        yield MenuItem::linkToCrud('Dons', 'fa fa-hand-holding-heart', Don::class);
        yield MenuItem::linkToCrud('Dossier Médicaux', 'fa fa-notes-medical', DossierMed::class);


        yield MenuItem::section('Utilisateurs & Banques');
        yield MenuItem::linkToRoute('Utilisateurs', 'fa fa-users', 'app_user_index');
        yield MenuItem::linkToRoute('Banques de sang', 'fa fa-hospital', 'app_banque_index');

        yield MenuItem::section('Stocks & Commandes');
        yield MenuItem::linkToRoute('Stocks', 'fa fa-layer-group', 'back_stock_index');
        yield MenuItem::linkToRoute('Commandes', 'fa fa-shopping-cart', 'back_commandes_index');

        yield MenuItem::section('Collecte & Rendez-vous');
        yield MenuItem::linkToRoute('Entités Collecte', 'fa fa-map-marker-alt', 'back_entite_collecte_index');
        yield MenuItem::linkToRoute('Questionnaires', 'fa fa-clipboard-list', 'questionnaireback_list');
        yield MenuItem::linkToRoute('Rendez-vous', 'fa fa-calendar-alt', 'rendezvousback_list');

        yield MenuItem::section('Navigation');
        yield MenuItem::linkToUrl('Retour au site', 'fa fa-arrow-left', '/front');
    }
}
