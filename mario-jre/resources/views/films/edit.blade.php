@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Modifier un film</div>

                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('films.update', $film['filmId'] ?? $film['id']) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="title" class="form-label">Titre</label>
                            <input id="title" name="title" value="{{ old('title', $film['title'] ?? '') }}" class="form-control" required>
                            @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control">{{ old('description', $film['description'] ?? '') }}</textarea>
                            @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="releaseYear" class="form-label">Année</label>
                                <input id="releaseYear" name="releaseYear" value="{{ old('releaseYear', $film['releaseYear'] ?? '') }}" class="form-control" type="number">
                                @error('releaseYear') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="length" class="form-label">Durée (min)</label>
                                <input id="length" name="length" value="{{ old('length', $film['length'] ?? '') }}" class="form-control" type="number">
                                @error('length') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="rating" class="form-label">Note</label>
                                <select id="rating" name="rating" class="form-select">
                                    <option value="">-- Sélectionnez --</option>
                                    @php
                                        $ratings = ['G','PG','PG-13','R','NC-17'];
                                        $oldRating = old('rating', $film['rating'] ?? '');
                                    @endphp
                                    @foreach($ratings as $r)
                                        <option value="{{ $r }}" {{ $oldRating === $r ? 'selected' : '' }}>{{ $r }}</option>
                                    @endforeach
                                </select>
                                @error('rating') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Langue originale</label>
                            <input type="text" class="form-control" value="{{ $language }}" disabled>
                            <input type="hidden" name="originalLanguageId" value="{{ $film['originalLanguageId'] }}">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="rentalDuration" class="form-label">Rental Duration</label>
                                <input id="rentalDuration" name="rentalDuration" value="{{ old('rentalDuration', $film['rentalDuration'] ?? 6) }}" class="form-control" type="number">
                                @error('rentalDuration') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="rentalRate" class="form-label">Rental Rate</label>
                                <input id="rentalRate" name="rentalRate" value="{{ old('rentalRate', $film['rentalRate'] ?? '0.99') }}" class="form-control" type="text">
                                @error('rentalRate') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="replacementCost" class="form-label">Replacement Cost</label>
                                <input id="replacementCost" name="replacementCost" value="{{ old('replacementCost', $film['replacementCost'] ?? '20.99') }}" class="form-control" type="text">
                                @error('replacementCost') <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="specialFeatures" class="form-label">Special Features (comma separated)</label>
                            <input id="specialFeatures" name="specialFeatures" value="{{ old('specialFeatures', $film['specialFeatures'] ?? '') }}" class="form-control" type="text" placeholder="Deleted Scenes,Behind the Scenes">
                            @error('specialFeatures') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('films.show', $film['filmId'] ?? $film['id']) }}" class="btn btn-secondary me-2">Annuler</a>
                            <button class="btn btn-primary" type="submit">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
