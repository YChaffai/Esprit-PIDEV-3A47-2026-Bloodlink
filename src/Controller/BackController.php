<?php
namespace App\Controller;


use App\Entity\Compagne;
use App\Entity\Entitecollecte;
use App\Form\CompagneType;
use App\Form\EntitecollecteType;
use App\Repository\CompagneRepository;
use App\Repository\EntitecollecteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin')]
class BackController extends AbstractController
{
#[Route('/', name: 'back_index', methods: ['GET'])]
public function index(
CompagneRepository $compagneRepo,
EntitecollecteRepository $entiteRepo
): Response {
return $this->render('back/index.html.twig', [
'compagnes' => $compagneRepo->findAll(),
'entites' => $entiteRepo->findAll(),
]);
}


#[Route('/compagne/edit/{id}', name: 'back_edit_compagne', methods: ['GET','POST'])]
public function editCompagne(Compagne $compagne, Request $request, EntityManagerInterface $em): Response
{
$form = $this->createForm(CompagneType::class, $compagne);
$form->handleRequest($request);


if ($form->isSubmitted() && $form->isValid()) {
$em->flush();
return $this->redirectToRoute('back_index');
}


return $this->render('back/edit_compagne.html.twig', [
'form' => $form->createView()
]);
}


#[Route('/compagne/delete/{id}', name: 'back_delete_compagne', methods: ['POST'])]
public function deleteCompagne(Request $request, Compagne $compagne, EntityManagerInterface $em): Response
{
if ($this->isCsrfTokenValid('delete_compagne'.$compagne->getId(), $request->request->get('_token'))) {
$em->remove($compagne);
$em->flush();
}
return $this->redirectToRoute('back_index');
}


#[Route('/entite/edit/{id}', name: 'back_edit_entite', methods: ['GET','POST'])]
public function editEntite(Entitecollecte $entite, Request $request, EntityManagerInterface $em): Response
{
$form = $this->createForm(EntitecollecteType::class, $entite);
$form->handleRequest($request);


if ($form->isSubmitted() && $form->isValid()) {
$em->flush();
return $this->redirectToRoute('back_index');
}


return $this->render('back/edit_entite.html.twig', [
'form' => $form->createView()
]);
}


#[Route('/entite/delete/{id}', name: 'back_delete_entite', methods: ['POST'])]
public function deleteEntite(Request $request, Entitecollecte $entite, EntityManagerInterface $em): Response
{
if ($this->isCsrfTokenValid('delete_entite'.$entite->getId(), $request->request->get('_token'))) {
$em->remove($entite);
$em->flush();
}
return $this->redirectToRoute('back_index');
}
}