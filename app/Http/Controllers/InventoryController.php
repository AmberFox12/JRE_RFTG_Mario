<?php

namespace App\Http\Controllers;

use App\Services\ToadInventoryService;
use App\Services\ToadFilmService;
use App\Services\ToadStoreService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private ToadInventoryService $inventoryService;
    private ToadFilmService $filmService;
    private ToadStoreService $storeService;

    public function __construct(
        ToadInventoryService $inventoryService,
        ToadFilmService $filmService,
        ToadStoreService $storeService
    ) {
        $this->middleware('auth');
        $this->inventoryService = $inventoryService;
        $this->filmService = $filmService;
        $this->storeService = $storeService;
    }

    /**
     * Affiche la liste des films avec leurs inventaires (DVD)
     */
    public function index()
    {
        $startTime = microtime(true);
        \Log::info("=== INDEX START ===");

        $inventoriesResult = $this->inventoryService->getAllInventory();
        \Log::info("getAllInventory took: " . round((microtime(true) - $startTime) * 1000, 2) . "ms");

        $inventories = [];

        if (is_array($inventoriesResult) && ($inventoriesResult['success'] ?? false)) {
            $inventories = $inventoriesResult['data'] ?? [];
            \Log::info("Received " . count($inventories) . " inventories");
        }

        $beforeGrouping = microtime(true);
        // Grouper les inventaires par film
        $filmGroups = [];
        foreach ($inventories as $inventory) {
            $filmId = $inventory['filmId'] ?? null;
            if ($filmId) {
                if (!isset($filmGroups[$filmId])) {
                    $filmGroups[$filmId] = [
                        'film' => $inventory['film'] ?? null,
                        'inventories' => [],
                        'availableCount' => 0,
                        'totalCount' => 0
                    ];
                }
                $filmGroups[$filmId]['inventories'][] = $inventory;
                $filmGroups[$filmId]['totalCount']++;

                // Compter les DVD disponibles (non loués)
                if (!isset($inventory['rentalId']) || $inventory['rentalId'] === null) {
                    $filmGroups[$filmId]['availableCount']++;
                }
            }
        }
        \Log::info("Grouping took: " . round((microtime(true) - $beforeGrouping) * 1000, 2) . "ms");

        // Limiter à 20 films pour l'affichage
        $filmGroups = array_slice($filmGroups, 0, 20, true);
        \Log::info("Total index operation took: " . round((microtime(true) - $startTime) * 1000, 2) . "ms");
        \Log::info("=== INDEX END ===");

        return view('inventory.index', [
            'filmGroups' => $filmGroups
        ]);
    }

    /**
     * Affiche le formulaire de création d'un DVD
     */
    public function create(Request $request)
    {
        $filmId = $request->query('film_id');
        $film = null;

        if ($filmId) {
            $filmResult = $this->filmService->getFilmById($filmId);
            if (is_array($filmResult)) {
                $film = $filmResult;
            }
        }

        // Récupérer tous les films pour la sélection
        $filmsResult = $this->filmService->getAllFilms();
        $films = [];
        if (is_array($filmsResult)) {
            $films = $filmsResult;
        }

        // Récupérer tous les stores
        $storesResult = $this->storeService->getAllStores();
        $stores = [];
        if (is_array($storesResult) && ($storesResult['success'] ?? false)) {
            $stores = $storesResult['data'] ?? [];
        }

        return view('inventory.create', [
            'film' => $film,
            'films' => $films,
            'stores' => $stores
        ]);
    }

    /**
     * Enregistre un nouveau DVD dans l'inventaire
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'filmId' => 'required|integer',
            'storeId' => 'required|integer',
        ]);

        // Vérifier que le film existe
        $filmResult = $this->filmService->getFilmById($data['filmId']);
        if (!is_array($filmResult) || empty($filmResult)) {
            return back()->withInput()->with('error', 'Le film spécifié n\'existe pas.');
        }

        // Vérifier que le store est renseigné
        if (empty($data['storeId'])) {
            return back()->withInput()->with('error', 'Le store doit être renseigné.');
        }

        $payload = [
            'filmId' => (int) $data['filmId'],
            'storeId' => (int) $data['storeId'],
        ];

        $result = $this->inventoryService->createInventory($payload);

        if (is_array($result) && ($result['success'] ?? false)) {
            return redirect()->route('inventory.index')->with('success', 'DVD ajouté avec succès au stock');
        }

        $message = 'Impossible d\'ajouter le DVD.';
        if (is_array($result) && !empty($result['error'])) {
            $message .= ' ' . $result['error'];
        }

        return back()->withInput()->with('error', $message);
    }

    /**
     * Affiche le formulaire d'édition pour un DVD
     */
    public function edit($id)
    {
        $inventoryResult = $this->inventoryService->getInventoryById($id);

        if (!is_array($inventoryResult) || !($inventoryResult['success'] ?? false)) {
            return redirect()->route('inventory.index')->with('error', 'DVD introuvable');
        }

        $inventory = $inventoryResult['data'] ?? null;

        if (!$inventory) {
            return redirect()->route('inventory.index')->with('error', 'DVD introuvable');
        }

        // Récupérer tous les stores
        $storesResult = $this->storeService->getAllStores();
        $stores = [];
        if (is_array($storesResult) && ($storesResult['success'] ?? false)) {
            $stores = $storesResult['data'] ?? [];
        }

        return view('inventory.edit', [
            'inventory' => $inventory,
            'stores' => $stores
        ]);
    }

    /**
     * Met à jour un DVD existant dans l'inventaire
     */
    public function update(Request $request, $id)
    {
        $startTime = microtime(true);
        \Log::info("=== UPDATE START ===");

        // Vérifier que le DVD est disponible (non loué)
        $isAvailable = $this->inventoryService->checkIfDVDIsAvailable($id);
        \Log::info("checkIfDVDIsAvailable took: " . round((microtime(true) - $startTime) * 1000, 2) . "ms");

        if (!$isAvailable) {
            return back()->with('error', 'Impossible de modifier un DVD actuellement loué');
        }

        $data = $request->validate([
            'storeId' => 'required|integer',
            'filmId'  => 'required|integer',
        ]);

        $payload = [
            'filmId'  => (int) $data['filmId'],
            'storeId' => (int) $data['storeId'],
        ];

        $beforeUpdate = microtime(true);
        $result = $this->inventoryService->updateInventory($id, $payload);
        \Log::info("updateInventory took: " . round((microtime(true) - $beforeUpdate) * 1000, 2) . "ms");
        \Log::info("Total update operation took: " . round((microtime(true) - $startTime) * 1000, 2) . "ms");
        \Log::info("=== UPDATE END (before redirect) ===");

        if (is_array($result) && ($result['success'] ?? false)) {
            return redirect()->route('inventory.index')->with('success', 'DVD mis à jour avec succès');
        }

        $message = 'Impossible de mettre à jour le DVD.';
        if (is_array($result) && !empty($result['error'])) {
            $message .= ' ' . $result['error'];
        }

        return back()->withInput()->with('error', $message);
    }

    /**
     * Supprime un DVD de l'inventaire
     */
    public function destroy($id)
    {
        // Vérifier que le DVD est disponible (non loué)
        $isAvailable = $this->inventoryService->checkIfDVDIsAvailable($id);

        if (!$isAvailable) {
            return redirect()->route('inventory.index')->with('error', 'Impossible de supprimer un DVD actuellement loué');
        }

        $result = $this->inventoryService->deleteInventory($id);

        if (is_array($result) && ($result['success'] ?? false)) {
            return redirect()->route('inventory.index')->with('success', 'DVD supprimé avec succès');
        }

        $message = 'Erreur lors de la suppression du DVD.';
        if (is_array($result) && !empty($result['error'])) {
            $message .= ' ' . $result['error'];
        }

        return redirect()->route('inventory.index')->with('error', $message);
    }

    public function show($filmId)
    {
        //
        $allInventoriesResult = $this->inventoryService->getAllInventory();
        $filmByFilmIdResult = $this->filmService->getFilmById($filmId);

        $inventories = [];
        if (is_array($allInventoriesResult) && ($allInventoriesResult['success'] ?? false)) {
            $all = $allInventoriesResult['data'] ?? [];
            foreach ($all as $item) {
                if (($item['filmId'] ?? null) == $filmId) {
                    $inventories[] = $item;
                }
            }
        }
        // Grouper les film par store
        $storeGroups = [];
        foreach ($inventories as $inventory) {
            $storeId = $inventory['storeId'] ?? null;
            if ($storeId) {
                if (!isset($storeGroups[$storeId])) {
                    $storeGroups[$storeId] = [
                        'film' => $inventory['film'] ?? null,
                        'inventories' => [],
                        'availableCount' => 0,
                        'totalCount' => 0
                    ];
                }
                $storeGroups[$storeId]['inventories'][] = $inventory;
                $storeGroups[$storeId]['totalCount']++;

                // Compter les DVD disponibles (non loués)
                if (!isset($inventory['rentalId']) || $inventory['rentalId'] === null) {
                    $storeGroups[$storeId]['availableCount']++;
                }
            }
        }
        return view('inventory.show', [
            'storeGroups' => $storeGroups,
            'film' => $filmByFilmIdResult
        ]);
    }
}
