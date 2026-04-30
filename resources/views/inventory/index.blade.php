@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Gestion du stock de DVD</h5>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (empty($filmGroups))
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Aucun DVD disponible dans le stock.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 50px;"></th>
                                        <th>Titre</th>
                                        <th style="width: 200px;">Exemplaires disponibles</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($filmGroups as $filmId => $group)
                                        @php
                                            $film = $group['film'];
                                            $inventories = $group['inventories'];
                                            $availableCount = $group['availableCount'];
                                            $totalCount = $group['totalCount'];
                                        @endphp

                                        {{-- Ligne du film (niveau principal) --}}
                                        <tr class="film-row" data-film-id="{{ $filmId }}" style="cursor: pointer;">
                                            <td>
                                                <i class="bi bi-chevron-right toggle-icon"></i>
                                            </td>
                                            <td><strong>{{ $film['title'] ?? 'Sans titre' }}</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $availableCount > 0 ? 'success' : 'danger' }}">
                                                    {{ $availableCount }}/{{ $totalCount }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('inventory.show', $filmId) }}"
                                                       class="btn btn-sm btn-info"
                                                       title="Consulter le stock par store"
                                                       onclick="event.stopPropagation()">
                                                        <i class="bi bi-eye"></i> Consulter stock
                                                    </a>
                                                    <a href="{{ route('inventory.create', ['film_id' => $filmId]) }}"
                                                       class="btn btn-sm btn-primary"
                                                       title="Ajouter un DVD"
                                                       onclick="event.stopPropagation()">
                                                        <i class="bi bi-plus-circle"></i> Ajouter DVD
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Ligne des DVD (niveau secondaire, masqué par défaut) --}}
                                        <tr class="dvd-details-row" data-film-id="{{ $filmId }}" style="display: none;">
                                            <td colspan="4" class="p-0">
                                                <div class="bg-light p-3 rounded">
                                                    <table class="table table-bordered mb-0 shadow-sm">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th style="width: 10%;" class="text-center">ID</th>
                                                                <th style="width: 15%;" class="text-center">Statut</th>
                                                                <th style="width: 40%;">Store associé</th>
                                                                <th style="width: 35%;" class="text-center">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white">
                                                            @foreach ($inventories as $inventory)
                                                                @php
                                                                    $isRented = isset($inventory['rentalId']) && $inventory['rentalId'] !== null;
                                                                    $store = $inventory['store'] ?? null;
                                                                    $storeId = $inventory['storeId'] ?? ($store['storeId'] ?? ($store['id'] ?? null));
                                                                    $storeName = $store['storeName'] ?? ($store['name'] ?? null);

                                                                    // Si pas de nom, afficher l'ID
                                                                    if (!$storeName && $storeId) {
                                                                        $storeName = 'Store #' . $storeId;
                                                                    } elseif (!$storeName) {
                                                                        $storeName = 'N/A';
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td class="text-center align-middle">{{ $inventory['inventoryId'] ?? 'N/A' }}</td>
                                                                    <td class="text-center align-middle">
                                                                        @if($isRented)
                                                                            <span class="badge bg-warning text-dark">Loué</span>
                                                                        @else
                                                                            <span class="badge bg-success">En stock</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="align-middle">{{ $storeName }}</td>
                                                                    <td class="text-center align-middle">
                                                                        <div class="d-flex gap-2 justify-content-center">
                                                                            <a href="{{ route('inventory.edit', $inventory['inventoryId']) }}"
                                                                               class="btn btn-sm btn-warning {{ $isRented ? 'disabled' : '' }}"
                                                                               style="width: 90px;"
                                                                               title="{{ $isRented ? 'DVD loué, modification impossible' : 'Modifier' }}"
                                                                               @if($isRented) onclick="return false;" @endif>
                                                                                <i class="bi bi-pencil-square"></i> Modifier
                                                                            </a>
                                                                            <form action="{{ route('inventory.destroy', $inventory['inventoryId']) }}"
                                                                                  method="POST"
                                                                                  class="d-inline">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit"
                                                                                        class="btn btn-sm btn-danger {{ $isRented ? 'disabled' : '' }}"
                                                                                        style="width: 90px;"
                                                                                        title="{{ $isRented ? 'DVD loué, suppression impossible' : 'Supprimer' }}"
                                                                                        @if($isRented)
                                                                                            disabled
                                                                                        @else
                                                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce DVD ?')"
                                                                                        @endif>
                                                                                    <i class="bi bi-trash3"></i> Supprimer
                                                                                </button>
                                                                            </form>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <p class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Total : <strong>{{ count($filmGroups) }}</strong> film(s) avec des DVD en stock
                            </p>
                            <p class="text-muted">
                                <i class="bi bi-hand-index"></i>
                                Cliquez sur une ligne de film pour afficher/masquer les DVD associés
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .film-row:hover {
        background-color: #f8f9fa;
    }

    .toggle-icon {
        transition: transform 0.3s ease;
    }

    .film-row.expanded .toggle-icon {
        transform: rotate(90deg);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gérer le clic sur les lignes de film pour déplier/replier les DVD
    const filmRows = document.querySelectorAll('.film-row');

    filmRows.forEach(row => {
        row.addEventListener('click', function() {
            const filmId = this.dataset.filmId;
            const detailsRow = document.querySelector(`.dvd-details-row[data-film-id="${filmId}"]`);

            if (detailsRow) {
                // Toggle l'affichage
                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = 'table-row';
                    this.classList.add('expanded');
                } else {
                    detailsRow.style.display = 'none';
                    this.classList.remove('expanded');
                }
            }
        });
    });
});
</script>
@endsection
