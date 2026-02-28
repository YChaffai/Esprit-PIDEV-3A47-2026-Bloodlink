<?php

namespace App\Controller\Front;

use App\Repository\BanqueRepository;
use App\Repository\CommandeRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotController extends AbstractController
{
    private string $apiKey;
    private HttpClientInterface $httpClient;
    private StockRepository $stockRepository;
    private CommandeRepository $commandeRepository;
    private BanqueRepository $banqueRepository;

    public function __construct(
        string $geminiApiKey,
        HttpClientInterface $httpClient,
        StockRepository $stockRepository,
        CommandeRepository $commandeRepository,
        BanqueRepository $banqueRepository
    ) {
        $this->apiKey = $geminiApiKey;
        $this->httpClient = $httpClient;
        $this->stockRepository = $stockRepository;
        $this->commandeRepository = $commandeRepository;
        $this->banqueRepository = $banqueRepository;
    }

    #[Route('/chat', name: 'front_chatbot', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';
        $history = $data['history'] ?? [];

        if (!$userMessage) {
            return new JsonResponse(['error' => 'No message provided'], 400);
        }

        if (!$this->apiKey || $this->apiKey === 'your_google_gemini_api_key_here') {
             return new JsonResponse(['response' => 'API Key not configured. Please add GEMINI_API_KEY in .env']);
        }

        // Prepare the tools definition for Gemini
        $tools = [
            [
                'function_declarations' => [
                    [
                        'name' => 'get_available_banks',
                        'description' => 'Get a list of blood banks that have a specific blood type available.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'blood_type' => [
                                    'type' => 'STRING',
                                    'description' => 'The blood type, e.g., A+, O-, B+'
                                ]
                            ],
                            'required' => ['blood_type']
                        ]
                    ],
                    [
                        'name' => 'get_commande_status',
                        'description' => 'Get the status of a specific order (commande) by its ID or reference.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'commande_id' => [
                                    'type' => 'STRING',
                                    'description' => 'The ID or reference of the order (commande).'
                                ]
                            ],
                            'required' => ['commande_id']
                        ]
                    ],
                    [
                        'name' => 'get_donation_preparation',
                        'description' => 'Get FAQ instructions on how to prepare for a blood donation.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => new \stdClass()
                        ]
                    ]
                ]
            ]
        ];

        // Format history
        $contents = [];
        // Add system instruction as first user message or handle via systemInstruction
        
        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => 'You are a helpful blood donation assistant for BloodLink. Answer questions concisely. Use tools to fetch real data about banks and orders.']
            ]
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [
                ['text' => 'Understood. I will help the user with their questions using the tools provided.']
            ]
        ];

        foreach ($history as $msg) {
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['text']]]
            ];
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]]
        ];

        $payload = [
            'contents' => $contents,
            'tools' => $tools,
            'generationConfig' => [
                'temperature' => 0.2
            ]
        ];

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $this->apiKey;

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $payload
            ]);

            $result = $response->toArray();
            
            // Handle tool calls
            if (isset($result['candidates'][0]['content']['parts'][0]['functionCall'])) {
                $functionCall = $result['candidates'][0]['content']['parts'][0]['functionCall'];
                $name = $functionCall['name'];
                $args = $functionCall['args'] ?? [];

                $functionResponseData = $this->handleToolCall($name, $args);

                // Send the function response back to Gemini
                $contents[] = $result['candidates'][0]['content']; // Append the model's function call request
                $contents[] = [
                    'role' => 'user',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name' => $name,
                                'response' => ['result' => $functionResponseData]
                            ]
                        ]
                    ]
                ];

                $secondPayload = [
                    'contents' => $contents,
                    'tools' => $tools
                ];

                $secondResponse = $this->httpClient->request('POST', $url, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'json' => $secondPayload
                ]);

                $secondResult = $secondResponse->toArray();
                $finalText = $secondResult['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not process the response.';
                return new JsonResponse(['response' => $finalText]);
            }

            $finalText = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I am not able to answer that right now.';
            return new JsonResponse(['response' => $finalText]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'API Error: ' . $e->getMessage()], 500);
        }
    }

    private function handleToolCall(string $name, array $args): array
    {
        switch ($name) {
            case 'get_available_banks':
                $bloodType = $args['blood_type'] ?? '';
                $stocks = $this->stockRepository->searchBy(['type_sang' => $bloodType, 'type_org' => 'banque']);
                $banks = [];
                foreach ($stocks as $stock) {
                    if ($stock->getQuantite() > 0) {
                        $bankId = $stock->getTypeOrgid();
                        $banque = $this->banqueRepository->find($bankId);
                        if ($banque) {
                            $banks[] = "Banque: " . $banque->getNom() . " (Phone: " . $banque->getTelephone() . ") has {$stock->getQuantite()} units of $bloodType";
                        }
                    }
                }
                if (empty($banks)) {
                    return ['message' => 'No banks currently have ' . $bloodType . ' available.'];
                }
                return ['banks' => $banks];

            case 'get_commande_status':
                $id = $args['commande_id'] ?? '';
                $commandes = $this->commandeRepository->searchBy(['search' => $id]);
                if (empty($commandes)) {
                    return ['status' => 'Commande not found.'];
                }
                $cmd = $commandes[0];
                return [
                    'reference' => $cmd->getReference(), 
                    'status' => $cmd->getStatus(),
                    'priorite' => $cmd->getPriorite(),
                    'quantite' => $cmd->getQuantite(),
                    'type_sang' => $cmd->getTypeSang()
                ];

            case 'get_donation_preparation':
                return [
                    'instructions' => [
                        'Drink plenty of water before the donation (at least 500ml).',
                        'Eat a healthy meal, avoiding fatty foods.',
                        'Bring a valid ID.',
                        'Get a good night\'s sleep.',
                        'Avoid strenuous physical activity before and after donating.'
                    ]
                ];
        }

        return ['error' => 'Unknown tool call'];
    }
}
