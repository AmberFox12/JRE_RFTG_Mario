<?php

namespace App\Http\Controllers;

use App\Services\ToadFilmService;
use App\Services\ToadLanguageService;
use Illuminate\Http\Request;

class FilmController extends Controller
{
    private ToadFilmService $filmService;
    private ToadLanguageService $languageService;

    public function __construct(ToadFilmService $filmService, ToadLanguageService $languageService)
    {
        $this->middleware('auth');
        $this->filmService = $filmService;
        $this->languageService = $languageService;
    }

    public function index()
    {
        $films = $this->filmService->getAllFilms();

        return view('films.index', [
            'films' => $films ?? []
        ]);
    }

    public function show($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            abort(404, 'Film non trouvé');
        }

        // Récupérer le nom de la langue
        $language = $this->languageService->getLanguageName($film['originalLanguageId'] ?? null);

        // Pour le débogage
        \Illuminate\Support\Facades\Log::info('Langue du film', [
            'film_id' => $film['filmId'],
            'originalLanguageId' => $film['originalLanguageId'],
            'language_name' => $language
        ]);

        return view('films.show', [
            'film' => $film,
            'language' => $language
        ]);
    }

    /**
     * Affiche le formulaire de création d'un film
     */
    public function create()
    {
        return view('films.create');
    }

    /**
     * Supprime un film existant
     */
    public function destroy($id)
    {
        $result = $this->filmService->deleteFilm($id);

        if (is_array($result) && ($result['success'] ?? false)) {
            return redirect()->route('films.index')->with('success', 'Film supprimé avec succès');
        }

        $message = 'Erreur lors de la suppression du film.';
        if (is_array($result) && !empty($result['error'])) {
            $message .= ' ' . $result['error'];
        }

        return redirect()->route('films.index')->with('error', $message);
    }

    /**
     * Affiche le formulaire d'édition pour un film existant
     */
    public function edit($id)
    {
        $film = $this->filmService->getFilmById($id);

        if (!$film) {
            return redirect()->route('films.index')->with('error', 'Film introuvable');
        }

        // S'assurer que filmId est défini
        if (!isset($film['filmId']) && isset($film['id'])) {
            $film['filmId'] = $film['id'];
        }

        // Récupérer le nom de la langue
        $language = $this->languageService->getLanguageName($film['originalLanguageId']);

        return view('films.edit', [
            'film' => $film,
            'language' => $language
        ]);
    }

    /**
     * Enregistre un nouveau film via le service externe
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'releaseYear' => 'nullable|integer',
            'originalLanguageId' => 'nullable|integer',
            'rentalDuration' => 'nullable|integer',
            'rentalRate' => 'nullable|numeric',
            'length' => 'nullable|integer',
            'replacementCost' => 'nullable|numeric',
            'rating' => 'nullable|string|max:10',
            'specialFeatures' => 'nullable|string',
        ]);

        // Ensure types are correct for the API (cast numeric values)
        $payload = $data;
        if (isset($payload['releaseYear'])) {
            $payload['releaseYear'] = (int) $payload['releaseYear'];
        }
        if (isset($payload['originalLanguageId'])) {
            $payload['originalLanguageId'] = (int) $payload['originalLanguageId'];
        }
        if (isset($payload['rentalDuration'])) {
            $payload['rentalDuration'] = (int) $payload['rentalDuration'];
        }
        if (isset($payload['length'])) {
            $payload['length'] = (int) $payload['length'];
        }
        if (isset($payload['rentalRate'])) {
            $payload['rentalRate'] = (float) $payload['rentalRate'];
        }
        if (isset($payload['replacementCost'])) {
            $payload['replacementCost'] = (float) $payload['replacementCost'];
        }

        $result = $this->filmService->createFilm($payload);

        if (is_array($result) && ($result['success'] ?? false)) {
            return redirect()->route('films.index')->with('success', 'Film ajouté avec succès');
        }

        $message = 'Impossible d\'ajouter le film.';
        if (is_array($result)) {
            if (!empty($result['error'])) {
                $message .= ' ' . $result['error'];
            }
        }

        return back()->withInput()->with('error', $message);
    }

    /**
     * Met à jour un film existant via le service externe
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'releaseYear' => 'nullable|integer',
            'originalLanguageId' => 'nullable|integer',
            'rentalDuration' => 'nullable|integer',
            'rentalRate' => 'nullable|numeric',
            'length' => 'nullable|integer',
            'replacementCost' => 'nullable|numeric',
            'rating' => 'nullable|string|max:10',
            'specialFeatures' => 'nullable|string',
        ]);

        $payload = $data;
        if (isset($payload['releaseYear'])) {
            $payload['releaseYear'] = (int) $payload['releaseYear'];
        }
        if (isset($payload['originalLanguageId'])) {
            $payload['originalLanguageId'] = (int) $payload['originalLanguageId'];
        }
        if (isset($payload['rentalDuration'])) {
            $payload['rentalDuration'] = (int) $payload['rentalDuration'];
        }
        if (isset($payload['length'])) {
            $payload['length'] = (int) $payload['length'];
        }
        if (isset($payload['rentalRate'])) {
            $payload['rentalRate'] = (float) $payload['rentalRate'];
        }
        if (isset($payload['replacementCost'])) {
            $payload['replacementCost'] = (float) $payload['replacementCost'];
        }

        $result = $this->filmService->updateFilm($id, $payload);

        if (is_array($result) && ($result['success'] ?? false)) {
            return redirect()->route('films.show', $id)->with('success', 'Film mis à jour avec succès');
        }

        $message = 'Impossible de mettre à jour le film.';
        if (is_array($result)) {
            if (!empty($result['error'])) {
                $message .= ' ' . $result['error'];
            }
        }

        return back()->withInput()->with('error', $message);
    }
}