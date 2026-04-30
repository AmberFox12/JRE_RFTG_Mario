@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Stock par point de distribution — {{ $film['title'] ?? 'Film inconnu' }}</h5>
                    <a href="{{ route('inventory.index') }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                </div>

                <div class="card-body">
                    @if (empty($storeGroups))
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            Aucun DVD disponible pour ce film.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Point de distribution</th>
                                        <th class="text-center">Exemplaires disponibles</th>
                                        <th class="text-center">Total exemplaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($storeGroups as $storeId => $group)
                                        @php
                                            $store = $group['store'] ?? null;
                                            $storeName = $store['storeName'] ?? ($store['name'] ?? 'Store #' . $storeId);
                                        @endphp
                                        <tr>
                                            <td>{{ $storeName }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $group['availableCount'] > 0 ? 'success' : 'danger' }}">
                                                    {{ $group['availableCount'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $group['totalCount'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <p class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Total : <strong>{{ array_sum(array_column($storeGroups, 'totalCount')) }}</strong> exemplaire(s) répartis sur <strong>{{ count($storeGroups) }}</strong> point(s) de distribution
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection