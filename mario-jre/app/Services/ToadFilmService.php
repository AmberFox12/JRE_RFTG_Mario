<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadFilmService
{
    private string $baseUrl;
    private ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
        $this->token = config('services.toad.token');
    }

    /**
     * Récupère le token JWT pour l'authentification
     */
    private function getUserToken(): ?string
    {
        return $this->token;
    }

    public function getAllFilms(): ?array
    {
        $url = $this->baseUrl . '/films';

        try {
            // Préparer la requête avec le bon token
            $request = Http::acceptJson()->timeout(10);
            
            if ($this->token) {
                $request = $request->withToken($this->token);
            }

            Log::info('Appel API Films', [
                'url' => $url,
                'has_token' => !empty($this->token)
            ]);

            $response = $request->get($url);

            Log::info('Réponse API Films', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Données films reçues', ['count' => is_array($data) ? count($data) : 'non-array']);
                return $data;
            }

            Log::warning('Films API KO', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Films', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    public function getFilmById($id): ?array
    {
        // Accept string or int and cast to int to avoid TypeError when routes supply strings like 'create'
        $id = (int) $id;
        $url = $this->baseUrl . '/films/' . $id;

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Erreur API Film', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Crée un nouveau film via l'API Toad
     * Retourne les données du film créé en cas de succès, null sinon.
     *
     * @param array $payload
     * @return array|null
     */
    public function createFilm(array $payload): ?array
    {
        $url = $this->baseUrl . '/films';

        try {
            // Préparation des headers
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            // Requête HTTP
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($url, $payload);

            // CAS SUCCÈS
            if ($response->successful()) {
                return [
                    'success' => true,
                    'status'  => $response->status(),
                    'data'    => $response->json(),
                    'error'   => null,
                ];
            }

            // CAS ERREUR API : on essaie de récupérer un message lisible
            $errorMessage = null;
            try {
                $json = $response->json();
                $errorMessage = $json['message'] ?? json_encode($json);
            } catch (\Throwable $e) {
                $errorMessage = $response->body();
            }

            Log::warning('Create Film API KO', [
                'status' => $response->status(),
                'error'  => $errorMessage
            ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'data'    => null,
                'error'   => $errorMessage,
            ];

        } catch (\Throwable $e) {

            // CAS EXCEPTION PHP
            Log::error('Erreur création Film', ['msg' => $e->getMessage()]);

            return [
                'success' => false,
                'status'  => 0, // 0 = erreur interne
                'data'    => null,
                'error'   => $e->getMessage(),
            ];
        }
    }
    /**
     * Supprime un film via l'API Toad
     * Retourne les données du film supprimé en cas de succès, null sinon.
     *
     * @param int|string $id
     * @return array|null
     */
    public function deleteFilm($id): ?array
    {
        $url = $this->baseUrl . '/films';

        try {
            $headers = ['Accept' => 'application/json'];
            $token = $this->getUserToken();
            if ($token) {
                $headers['Authorization'] = "Bearer {$token}";
            }

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->delete($url . '/' . $id);

            if ($response->body() === '' || $response->status() === 204) {
                return [
                    'success' => true,
                    'status'  => $response->status(),
                    'data'    => null,
                    'error'   => null,
                ];
            }

           if ($response->successful()) {
                return [
                    'success' => true,
                    'status'  => $response->status(),
                    'data'    => $response->json(),
                    'error'   => null,
                ];
            } else {
                Log::warning('Delete Film API KO', ['status' => $response->status(), 'data' => $response->body()]);
                $message = null;
                try {
                    $json = $response->json();
                    $message = $json['message'] ?? json_encode($json);
                } catch (\Throwable $e) {
                    $message = "La suppression a échoué.";
                }
                return [
                    'success' => false,
                    'status'  => $response->status(),
                    'data'    => null,
                    'error'   => $message,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Erreur suppression Film', ['msg' => $e->getMessage()]);
            return [
                'success' => false,
                'status'  => 0,
                'data'    => null,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Met à jour un film via l'API Toad
     *
     * @param int|string $id
     * @param array $payload
     * @return array|null
     */
    public function updateFilm($id, array $payload): ?array
    {
        $id = (int) $id;
        
        // S'assurer que l'ID est valide
        if ($id <= 0) {
            return [
                'success' => false,
                'status' => 400,
                'data' => null,
                'error' => 'ID de film invalide'
            ];
        }

        $url = $this->baseUrl . '/films/' . $id;

        try {
            // Préparation de la requête avec le bon token
            $request = Http::acceptJson()->timeout(10);
            
            if ($this->token) {
                $request = $request->withToken($this->token);
            }

            Log::info('Appel API Update Film', [
                'url' => $url,
                'payload' => $payload,
                'has_token' => !empty($this->token)
            ]);

            // Requête API
            $response = $request->put($url, $payload);

            // Cas succès
            if ($response->successful()) {
                return [
                    'success' => true,
                    'status'  => $response->status(),
                    'data'    => $response->json(),
                    'error'   => null,
                ];
            }

            // Cas erreur API (JSON si possible, body sinon)
            $message = null;
            try {
                $json = $response->json();
                $message = $json['message'] ?? json_encode($json);
            } catch (\Throwable $e) {
                $message = $response->body();
            }

            Log::warning('Update Film API KO', [
                'status' => $response->status(),
                'error'  => $message
            ]);

            return [
                'success' => false,
                'status'  => $response->status(),
                'data'    => null,
                'error'   => $message,
            ];

        } catch (\Throwable $e) {

            // Cas exception PHP
            Log::error('Erreur update Film', ['msg' => $e->getMessage()]);

            return [
                'success' => false,
                'status'  => 0,
                'data'    => null,
                'error'   => $e->getMessage(),
            ];
        }
    }
}