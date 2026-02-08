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


#[Route('/front')]
class FrontController extends AbstractController
{
#[Route('/', name: 'front_index', methods: ['GET','POST'])]
public function index(
Request $request,
EntityManagerInterface $em,
CompagneRepository $compagneRepository,
EntitecollecteRepository $entiteRepository
): Response {
// ======= CREATE COMPAGNE =======
$compagne = new Compagne();
$compagneForm = $this->createForm(CompagneType::class, $compagne);
$compagneForm->handleRequest($request);


if ($compagneForm->isSubmitted() && $compagneForm->isValid()) {
$em->persist($compagne);
$em->flush();
return $this->redirectToRoute('front_index');
}


// ======= CREATE ENTITE =======
$entite = new Entitecollecte();
$entiteForm = $this->createForm(EntitecollecteType::class, $entite);
$entiteForm->handleRequest($request);


if ($entiteForm->isSubmitted() && $entiteForm->isValid()) {
$em->persist($entite);
$em->flush();
return $this->redirectToRoute('front_index');
}


return $this->render('front/index.html.twig', [
'compagneForm' => $compagneForm->createView(),
'entiteForm' => $entiteForm->createView(),
'campagnes' => $compagneRepository->findAll(),
'entites' => $entiteRepository->findAll(),
]);
}


// ======= UPDATE COMPAGNE =======
#[Route('/compagne/edit/{id}', name: 'front_edit_compagne', methods: ['GET','POST'])]
public function editCompagne(Compagne $compagne, Request $request, EntityManagerInterface $em): Response
{
$form = $this->createForm(CompagneType::class, $compagne);
$form->handleRequest($request);


if ($form->isSubmitted() && $form->isValid()) {
$em->flush();
return $this->redirectToRoute('front_index');
}


return $this->render('front/edit_compagne.html.twig', [
'form' => $form->createView()
]);
}


// ======= DELETE COMPAGNE =======
#[Route('/compagne/delete/{id}', name: 'front_delete_compagne', methods: ['POST'])]
public function deleteCompagne(Request $request, Compagne $compagne, EntityManagerInterface $em): Response
{
if ($this->isCsrfTokenValid('delete_compagne'.$compagne->getId(), $request->request->get('_token'))) {
$em->remove($compagne);
$em->flush();
}
return $this->redirectToRoute('front_index');
}


// ======= UPDATE ENTITE =======
#[Route('/entite/edit/{id}', name: 'front_edit_entite', methods: ['GET','POST'])]
public function editEntite(Entitecollecte $entite, Request $request, EntityManagerInterface $em): Response
{
$form = $this->createForm(EntitecollecteType::class, $entite);
$form->handleRequest($request);


if ($form->isSubmitted() && $form->isValid()) {
$em->flush();
return $this->redirectToRoute('front_index');
}


return $this->render('front/edit_entite.html.twig', [
'form' => $form->createView()
]);
}


// ======= DELETE ENTITE =======
#[Route('/entite/delete/{id}', name: 'front_delete_entite', methods: ['POST'])]
public function deleteEntite(Request $request, Entitecollecte $entite, EntityManagerInterface $em): Response
{
if ($this->isCsrfTokenValid('delete_entite'.$entite->getId(), $request->request->get('_token'))) {
$em->remove($entite);
$em->flush();
}
return $this->redirectToRoute('front_index');
}
}