<?php
// bin/check_orgs.php

use App\Kernel;
use App\Entity\Banque;
use App\Entity\Entitecollecte;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    $kernel->boot();
    $container = $kernel->getContainer();
    $em = $container->get('doctrine')->getManager();

    $banques = $em->getRepository(Banque::class)->findAll();
    $entites = $em->getRepository(Entitecollecte::class)->findAll();

    echo "Banques found: " . count($banques) . "\n";
    foreach ($banques as $b) {
        echo "- ID: " . $b->getId() . ", Nom: " . $b->getNom() . "\n";
    }

    echo "Entites Found: " . count($entites) . "\n";
    foreach ($entites as $e) {
        echo "- ID: " . $e->getId() . ", Nom: " . $e->getNom() . "\n";
    }
};
