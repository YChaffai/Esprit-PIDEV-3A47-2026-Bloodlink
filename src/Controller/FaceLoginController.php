<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FaceLoginController extends AbstractController
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private UserRepository $userRepository,
        private TokenStorageInterface $tokenStorage,
        private RequestStack $requestStack,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    #[Route('/face-login', name: 'app_face_login', methods: ['POST'])]
    public function faceLogin(Request $request): JsonResponse
    {
        $imageFile = $request->files->get('image');

        if (!$imageFile) {
            return $this->json(['success' => false, 'error' => 'No image provided'], 400);
        }

        try {
            $formData = new FormDataPart([
                'image' => DataPart::fromPath($imageFile->getPathname(), $imageFile->getClientOriginalName(), $imageFile->getMimeType()),
            ]);

            $response = $this->httpClient->request('POST', 'http://localhost:5000/verify', [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToString(),
                'timeout' => 15,
            ]);

            $result = $response->toArray();
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Face recognition service unavailable: ' . $e->getMessage(),
            ], 503);
        }

        if (empty($result['match'])) {
            return $this->json([
                'success' => false,
                'error' => $result['error'] ?? 'Face not recognized',
            ]);
        }

        $user = $this->userRepository->findOneBy(['role' => 'admin']);
        if (!$user) {
            return $this->json(['success' => false, 'error' => 'Admin user not found in database']);
        }

        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        $session = $this->requestStack->getSession();
        $session->set('_security_main', serialize($token));

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $redirect = $this->urlGenerator->generate('admin');
        } elseif (in_array('ROLE_CLIENT', $user->getRoles()) || in_array('ROLE_DOCTOR', $user->getRoles())) {
            $redirect = $this->urlGenerator->generate('front_home');
        } else {
            $redirect = $this->urlGenerator->generate('app_login');
        }

        return $this->json([
            'success' => true,
            'confidence' => $result['confidence'] ?? null,
            'redirect' => $redirect,
        ]);
    }
}
