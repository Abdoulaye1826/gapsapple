@extends('layouts.dashboard')

@section('title', 'Modifier utilisateur')
@section('page-title', 'Modifier utilisateur')

@section('content')
<div class="page-header">
  <h1><i class="bi bi-pencil me-2"></i>Modifier utilisateur : {{ $user->name }}</h1>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body">
    <form method="POST" action="{{ route('users.update', $user) }}">
      @csrf @method('PUT')
      @include('users._form')
      <div class="d-flex gap-2 mt-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Annuler</a>
      </div>
    </form>
  </div>
</div>
@endsection
