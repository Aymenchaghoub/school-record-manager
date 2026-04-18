# Architecture Decision Record - UI Layer

## Decision
La SPA React est la couche UI cible unique du projet.
Les vues Blade sont en deprecation. Aucune nouvelle fonctionnalite ne doit etre ajoutee cote Blade.

## Plan de deprecation Blade
- Phase 1 (immediate) : Blade sert uniquement les pages non encore migrees.
- Phase 2 : Toutes les routes Blade `/admin/*`, `/teacher/*`, `/student/*`, `/parent/*` redirigent vers la SPA.
- Phase 3 : Suppression des controleurs Web legacy apres validation des controleurs API.

## Routes SPA
La SPA est servie par une route catch-all Blade :

```php
Route::get('/app/{any?}', function () {
    return view('app');
})->where('any', '.*');
```

## Regle d'or
Controleurs API = source de verite.
Controleurs Web = wrappers temporaires a supprimer.
