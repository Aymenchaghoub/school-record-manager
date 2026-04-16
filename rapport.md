# Rapport D'audit Complet - School Record Manager

Date d'audit: 2026-04-16
Branche observée: feature/react-frontend
Méthode: lecture statique large + vérification runtime ciblée + extraction des routes + inventaire de fichiers

## 0) Cadre Et Traçabilité

Objectif du rapport:
Produire un audit exhaustif, concret, orienté risques et plan d'exécution.

Périmètre analysé:
Fichiers suivis et artefacts de travail du dépôt courant.

Volume observé:
- Fichiers suivis (manifest): 265
- Lignes totales (manifest): 24918
- Contrôleurs: 23
- Modèles: 7
- Migrations suivies: 13
- Tests: 13
- Vues Blade: 77
- Frontend src: 67
- Routes exportées: 149

État runtime testé:
- Commande lancée: php artisan test
- Résultat: 88 tests en échec, 0 assertion
- Erreur principale: table sessions déjà existante

État du working tree:
- Changements détectés: 30
- Fichiers suivis modifiés: 18
- Fichiers non suivis: 12

Limites et ambiguïtés de méthode:
- ⚠️ AMBIGUOUS: l'exigence lire tous les fichiers peut être interprétée comme tous les fichiers du disque (vendor, caches, binaires) ou tous les fichiers applicatifs versionnés.
- ⚠️ AMBIGUOUS: certaines vues compilées dans storage/framework/views reflètent le runtime local et non la source canonique.
- ⚠️ AMBIGUOUS: présence de migrations non suivies qui impactent fortement les tests, sans historique Git stabilisé.
- ⚠️ AMBIGUOUS: coexistence de deux couches UI (Blade et React SPA), certaines routes servent de stub et brouillent la cible fonctionnelle finale.

Conclusion de traçabilité:
Ce rapport se base sur les sources lues, les routes exportées, l'exécution des tests et l'état Git local.

---

## 1) Vue D'ensemble Du Projet

Nature du produit:
Application de gestion scolaire multi-rôles.

Rôles métier observés:
- Admin
- Teacher
- Student
- Parent

Stack backend:
- Laravel 11
- PHP requis dans composer: ^8.2
- Sanctum (auth session SPA)
- DomPDF
- PHPUnit 11

Stack frontend:
- React
- Vite
- react-router-dom
- Chart.js via react-chartjs-2
- Tailwind

Architecture fonctionnelle actuelle:
- Couche Web Blade historique
- Couche API JSON
- Couche SPA React en cours de migration

Observations de maturité:
- L'application couvre un scope fonctionnel large.
- L'architecture est riche mais hétérogène.
- Le produit est en transition active.
- La dette de cohérence est élevée.

Forces globales:
- Couverture métier déjà importante.
- Modélisation domaine présente (notes, absences, bulletins, classes, événements).
- Gestion de rôles appliquée à la plupart des routes.
- Effort de tests significatif en volume.

Fragilités globales:
- Divergence entre modèles, contrôleurs et migrations.
- Régressions runtime bloquantes (tests cassés globalement).
- Conventions de nommage non uniformes.
- Duplication logique entre couches API et Web.

Niveau de risque global:
Élevé à court terme pour la livraison continue.
Moyen à long terme si un plan de normalisation est appliqué rapidement.

---

## 2) Audit Backend

### 2.1 Structure Backend

Répertoires principaux:
- app/Http/Controllers
- app/Models
- app/Http/Requests
- database/migrations
- routes
- tests

Constat:
La structure suit globalement Laravel, mais plusieurs conventions sont partiellement brisées.

### 2.2 Contrôleurs

Points positifs:
- Contrôleurs API récents utilisent souvent des réponses normalisées.
- Plusieurs contrôleurs intègrent des scopes de rôle explicites.
- Pagination et filtres présents sur des modules clés.

Points critiques:
- Contrôleurs Web legacy s'appuient encore sur des colonnes alias ou historiques.
- Mélange d'accès Eloquent et DB::table brut.
- Divergence dans la logique d'autorisation entre API et Web.

Exemples de divergence:
- Classe responsable teacher_id vs responsible_teacher_id.
- EventController Web et EventApiController n'ont pas le même contrat exact.
- GradeController teacher utilise des hypothèses différentes du GradeApiController.

Impact:
Comportements différents selon la porte d'entrée.
Risque de bug fonctionnel non détecté côté UI.

### 2.3 Modèles

Forces:
- Relations riches déjà présentes.
- Scopes utiles (absences justifiées, current month, etc.).
- Helpers métier (pourcentages, labels de performance).

Faiblesses:
- Présence d'aliases dans certains modèles pour compenser le schéma.
- Méthodes de service qui référencent des champs obsolètes.
- Certains calculs utilisent des conventions implicites non centralisées.

Risque:
Dette de domaine et dette de schéma simultanées.

### 2.4 Requests/Validation

Points positifs:
- FormRequests API bien avancées sur grades/absences/classes/users.
- Validation des rôles en authorize globalement cohérente côté API.

Points faibles:
- Coexistence de FormRequests API et Web avec contrats différents.
- Certains schémas attendus ne correspondent plus aux migrations récentes.

Effet:
Incohérences de validation selon le canal d'entrée.

### 2.5 Middleware Et Auth

Points positifs:
- Middleware CheckRole robuste pour API et Web.
- Sanctum configuré en mode stateful SPA.

Points faibles:
- Deux middlewares de rôle présents (CheckRole et RoleMiddleware), redondance.
- Risque de confusion si les deux sont utilisés à terme.

### 2.6 Services Applicatifs

Constat:
GradeService et UserService existent mais ne sont pas alignés avec le schéma actuel.

Exemples:
- GradeService utilise grade/exam_type/semester dans des portions legacy.
- Certaines méthodes reposent sur un contrat qui ne correspond plus aux champs value/type/term.

Verdict:
Services partiellement obsolètes.

### 2.7 Routes Backend

Surface totale:
149 routes exportées.

Répartition indicative:
- admin: 73
- teacher: 27
- student: 13
- parent: 13
- closures: 5

Lecture:
La surface est large et couvre bien les rôles.
Mais des routes closures existent encore comme stubs temporaires.

### 2.8 Tests Backend

État actuel:
- 88 tests échouent
- 0 assertion
- durée observée: ~21.87s

Cause racine dominante:
SQLSTATE HY000 table sessions already exists.

Diagnostic direct:
Le schéma crée sessions dans la migration users historique.
Puis une migration additionnelle crée aussi sessions.

Conséquence:
Le pipeline de test ne valide plus la logique métier.
Le signal qualité est actuellement invalide.

### 2.9 Sécurité Backend

Signaux positifs:
- Auth + session invalide sur logout.
- Contrôle de rôle présent sur les groupes de routes.

Signaux de risque:
- Sorties Blade non échappées dans composants modernes.
- Input SVG/html potentiellement rendu via variables icon.

Points à surveiller:
- Vérifier l'origine des variables injectées dans {!! !!}.
- Introduire whitelist stricte ou sanitizer.

### 2.10 Performance Backend

Constats:
- Plusieurs eager loads présents.
- Quelques selectRaw/groupByRaw raisonnables.

Risques:
- N+1 potentiel selon vues Blade legacy riches.
- Duplication de calculs dashboards entre Web et API.

---

## 3) Audit Frontend

### 3.1 Architecture React

Forces:
- Structure claire par composants, pages, services, utils.
- Route guards présents (PrivateRoute/ProtectedRoute).
- Pattern CrudPage factorise bien les pages CRUD.

Faiblesses:
- Incohérence partielle entre couleurs hardcodées et variables thème.
- Mélange français/anglais dans textes UI.
- Certaines permissions SPA diffèrent des attentes métier complètes.

### 3.2 Auth Et Session

Points positifs:
- Bootstrap utilisateur au démarrage.
- Gestion 401 globale via événement.
- CSRF forcé avant mutations via interceptor.

Points d'attention:
- Dépendance forte à endpoint fallback.
- Gestion des erreurs backend parfois générique.

### 3.3 Services API Frontend

Bonnes pratiques observées:
- resourceServiceFactory par rôle.
- fallback endpoint contrôlé.
- toasts sur opérations CRUD.

Risque:
Si le mapping endpoints change côté backend, la SPA peut échouer silencieusement via fallback 404 avant erreur finale.

### 3.4 Pages Métier

Couverture:
- users
- classes
- subjects
- grades
- absences
- report cards
- events
- dashboards par rôle

Observation:
Le scope est large et bien structuré.

Limite:
Certaines pages utilisent des champs ID bruts au lieu de sélecteurs enrichis, ce qui dégrade UX et augmente les erreurs utilisateur.

### 3.5 UI Components

Points positifs:
- Bibliothèque UI simple et réutilisable.
- ConfirmModal et EmptyState bien intégrés.

Points faibles:
- Accessibilité incomplète (focus management modal, contrastes à vérifier sur dark).
- Design très utilitaire, peu de hiérarchie visuelle métier.

### 3.6 Dashboard Frontend

Points positifs:
- Widgets utiles.
- Gestion de skeleton/loading/empty.
- Graphiques configurés proprement.

Points faibles:
- Multiplication des appels API.
- Données redondantes entre endpoints.
- Couche de fallback parfois implicite.

### 3.7 Qualité Frontend

Signaux debug:
- console.error présent dans AuthContext.
- console.error présent dans resources/js bootstrap.

Analyse:
Peu de bruit debug, plutôt maîtrisé.

### 3.8 Risques Frontend

Risque fonctionnel:
Désalignement route guard SPA vs rôles attendus métier (ex: report cards/events côté non-admin selon stratégie produit).

Risque de maintenance:
Convergence Blade + SPA pas complètement actée.

---

## 4) Audit Conception BDD

### 4.1 Modèle relationnel

Tables cœur:
- users
- classes
- subjects
- student_classes
- class_subjects
- parent_students
- grades
- absences
- report_cards
- events

Tables techniques:
- sessions
- password_reset_tokens
- personal_access_tokens
- cache/cache_locks (non suivi)
- jobs/failed_jobs (non suivi)

### 4.2 Points solides

- Relations n-n explicites pour inscriptions et enseignements.
- Index sur plusieurs axes métier utiles.
- JSON subject_grades pour report_cards permet flexibilité.

### 4.3 Points critiques

- Collision de migration sur sessions.
- Évolution events partiellement additive avec champs doublons (type/event_type, start_date/event_date).
- champs legacy encore référencés dans du code service.

### 4.4 Intégrité

Forces:
FK présentes sur la majorité des tables centrales.

Faiblesses:
Certaines insertions en DB::table legacy contournent conventions Eloquent et peuvent ignorer invariants métier.

### 4.5 Cohérence de normalisation

grades:
Migration normalize_grades_to_twenty_scale existe (non suivie), intention correcte.

Risque:
⚠️ AMBIGUOUS sur l'ordre d'application réel des migrations selon environnements.

### 4.6 Recommandations BDD immédiates

- Supprimer la duplication de création sessions.
- Stabiliser events autour d'un seul contrat temporel.
- Introduire migration de convergence explicite pour aliases teacher_id/responsible_teacher_id.

---

## 5) Matrice Des Fonctionnalités

Échelle:
✅ implémenté et cohérent
⚠️ partiel ou incohérent
❌ manquant
🔲 non vérifiable dans ce contexte

| Domaine | Fonctionnalité | Backend | Frontend | Tests | Statut global | Commentaire |
|---|---|---|---|---|---|---|
| Auth | Login session | ✅ | ✅ | ⚠️ | ⚠️ | tests cassés globalement par migration |
| Auth | Logout | ✅ | ✅ | ⚠️ | ⚠️ | logique bonne mais non validée runtime |
| Auth | CSRF SPA | ✅ | ✅ | ⚠️ | ⚠️ | dépend de config stateful stricte |
| Users | CRUD admin | ✅ | ✅ | ⚠️ | ⚠️ | couche web/api divergente |
| Users | Soft delete/restore | ✅ | 🔲 | ⚠️ | ⚠️ | SPA pas totalement alignée fonctionnalités web |
| Classes | CRUD admin | ✅ | ✅ | ⚠️ | ⚠️ | teacher_id alias historique |
| Subjects | CRUD admin | ✅ | ✅ | ⚠️ | ⚠️ | teacher principal vs teacher pivot |
| Grades | CRUD admin | ✅ | ✅ | ⚠️ | ⚠️ | convergence récente en cours |
| Grades | CRUD teacher | ✅ | ✅ | ⚠️ | ⚠️ | logique web teacher encore legacy partielle |
| Grades | Lecture student | ✅ | ✅ | ⚠️ | ⚠️ | dépend des scopes corrects |
| Grades | Lecture parent | ✅ | ✅ | ⚠️ | ⚠️ | dépend relation parent_students |
| Absences | CRUD admin | ✅ | ✅ | ⚠️ | ⚠️ | bonne base API |
| Absences | CRUD teacher | ✅ | ✅ | ⚠️ | ⚠️ | contrôleur web teacher legacy partiel |
| Absences | Lecture student | ✅ | ✅ | ⚠️ | ⚠️ | ok conceptuellement |
| Absences | Lecture parent | ✅ | ✅ | ⚠️ | ⚠️ | ok conceptuellement |
| Report cards | CRUD admin | ✅ | ✅ | ⚠️ | ⚠️ | routes SPA limitées par rôle |
| Report cards | Consultation student | ✅ | ⚠️ | ⚠️ | ⚠️ | web ok, SPA role gating différent |
| Report cards | Consultation parent | ✅ | ⚠️ | ⚠️ | ⚠️ | web ok, SPA rôle à clarifier |
| Events | CRUD admin | ✅ | ✅ | ⚠️ | ⚠️ | contrat fields dual (event_date/start_date) |
| Events | CRUD teacher | ✅ | ⚠️ | ⚠️ | ⚠️ | API autorise, SPA non exposée de base |
| Events | Lecture student | ✅ | ⚠️ | ⚠️ | ⚠️ | web stub pour certains cas |
| Events | Lecture parent | ✅ | ⚠️ | ⚠️ | ⚠️ | web stub pour certains cas |
| Dashboard | Admin KPI | ✅ | ✅ | ⚠️ | ⚠️ | endpoints présents |
| Dashboard | Teacher KPI | ✅ | ✅ | ⚠️ | ⚠️ | endpoints présents |
| Dashboard | Student KPI | ✅ | ✅ | ⚠️ | ⚠️ | endpoints présents |
| Dashboard | Parent KPI | ✅ | ✅ | ⚠️ | ⚠️ | endpoints présents |
| PDF | Download report card | ✅ | 🔲 | ⚠️ | ⚠️ | test existe mais suite cassée |
| Scripts | Start local env | ⚠️ | n/a | 🔲 | ⚠️ | script utile mais dépendances locales |
| CI | Front build | ✅ | ✅ | n/a | ✅ | job dédié présent |
| CI | Backend tests | ⚠️ | n/a | ⚠️ | ⚠️ | pipeline fail tant que migration collision |

Lecture globale de la matrice:
La majorité des fonctionnalités est présente.
Le problème principal n'est pas l'absence de modules.
Le problème principal est la cohérence transversale et la stabilité runtime.

---

## 6) Audit Design Et UX

### 6.1 Expérience générale

Points positifs:
- Interface propre et lisible.
- Structure dashboard claire.
- Composants réutilisables cohérents.

Points faibles:
- Mélange de langues dans labels et messages.
- Formulaires avec IDs bruts (friction forte côté utilisateur final).
- Parcours multi-rôle incomplets côté SPA par rapport aux possibilités backend.

### 6.2 Accessibilité

Forces:
- Structure simple, composants standards HTML.

Faiblesses:
- Gestion focus modal perfectible.
- Contrastes et états dark à revalider par audit a11y dédié.
- Peu d'indications aria avancées sur tableaux dynamiques.

### 6.3 Cohérence visuelle

Constat:
Présence d'un thème et de composants.

Mais:
- couleurs parfois hardcodées dans certains composants
- hiérarchie visuelle métier encore basique

### 6.4 UX métier

Exemples de pain points:
- saisie manuelle des IDs pour liaisons (student_id, class_id, etc.)
- ambiguïté sur certaines pages selon rôle
- fallback endpoint non visible utilisateur

Recommandation UX immédiate:
Remplacer la saisie ID par sélecteurs auto-complétés sur toutes les pages CRUD relationnelles.

---

## 7) Audit DevOps Et Outillage

### 7.1 CI

Pipeline observé:
- job build frontend
- job tests backend

Point fort:
Chaîne CI déjà en place.

Point critique:
Le job backend est actuellement non fiable à cause de la collision migration sessions.

### 7.2 Scripts locaux

Scripts présents:
- start-project.ps1
- check-views.ps1
- create-views.ps1

Utilité:
Bonne ergonomie pour développement local Windows.

Risque:
Scripts dépendants d'un setup local spécifique (XAMPP, chemins).

### 7.3 Configuration

Fichiers importants:
- .env.example
- config/cors.php
- config/sanctum.php
- phpunit.xml

Observations:
- Config Sanctum/CORS cohérente pour localhost.
- Valeurs dev hardcodées nombreuses (normal en local).

### 7.4 Qualité de livraison

État actuel:
- build frontend potentiellement passe
- tests backend bloqués
- dette de convergence élevée

Conclusion DevOps:
La chaîne existe mais la gate qualité backend est cassée.

---

## 8) Plan D'action Priorisé

### P0 - Blocage immédiat (0 à 2 jours)

1. Corriger définitivement la duplication migration sessions.
2. Valider une exécution complète de php artisan test.
3. Geler les migrations non suivies ou les intégrer proprement.
4. Éliminer les stubs closure de production pour routes critiques.

Résultat attendu P0:
CI backend redevient crédible.

### P1 - Cohérence fonctionnelle (3 à 7 jours)

1. Aligner contrôleurs Web et API sur un même contrat de champ.
2. Uniformiser teacher_id vs responsible_teacher_id.
3. Normaliser events autour d'un schéma unique.
4. Déprécier le code legacy service non aligné.

Résultat attendu P1:
Réduction forte des régressions cross-layer.

### P2 - UX et robustesse (1 à 2 semaines)

1. Remplacer les champs ID par sélecteurs relationnels UX.
2. Harmoniser la couverture SPA des rôles (events/report cards).
3. Renforcer validations côté front et messages d'erreur contextualisés.
4. Ajouter tests e2e ciblés parcours rôles.

Résultat attendu P2:
Meilleure adoption utilisateur + baisse incidents de saisie.

### P3 - Durcissement sécurité/perf (2 à 4 semaines)

1. Encadrer strictement les rendus Blade non échappés.
2. Ajouter audit SAST/DAST léger en CI.
3. Mesurer N+1 et optimiser dashboards.
4. Consolidation des politiques d'autorisation.

Résultat attendu P3:
Plateforme plus sûre et scalable.

---

## 9) Roadmap Manquante

Constat:
Le dépôt montre des indices de migration en cours mais manque d'une roadmap technique explicite versionnée.

Éléments manquants recommandés:

1. Roadmap de convergence Web vs SPA.
2. Plan de dépréciation des contrôleurs legacy.
3. Politique de compatibilité schéma/migrations.
4. Stratégie de tests cible (unitaires, feature, e2e).
5. Critères de Done par module métier.
6. Calendrier de stabilisation CI.
7. Tableau de dette technique priorisée.
8. Politique de versioning API.
9. Plan de sécurité applicative trimestriel.
10. Plan de monitoring production (logs, métriques, alertes).

Proposition de milestones:

Milestone A - Stabilisation runtime:
- migrations cohérentes
- tests verts
- CI fiable

Milestone B - Convergence de contrat:
- alias supprimés
- services alignés
- routes stubs remplacées

Milestone C - Expérience produit:
- formulaires relationnels UX
- parcours rôles complets
- a11y baseline validée

Milestone D - Industrialisation:
- qualité continue
- sécurité continue
- observabilité standardisée

---

## 10) Scores Globaux

Méthode de scoring:
Chaque axe est noté sur 10 selon stabilité, cohérence, maintenabilité et fiabilité observées.

| Axe | Note /10 | Commentaire synthétique |
|---|---:|---|
| Architecture globale | 6 | Bonne base, forte hétérogénéité transitionnelle |
| Qualité backend | 5 | Logique riche mais divergence legacy/API |
| Qualité frontend | 7 | Bonne structuration React, écarts UX/permissions |
| Modélisation BDD | 6 | Schéma métier riche, collisions et doublons à corriger |
| Sécurité applicative | 6 | Contrôles présents, rendus non échappés à encadrer |
| Robustesse tests | 2 | Suite totalement bloquée par migration sessions |
| Expérience utilisateur | 6 | Solide visuellement, formulaires relationnels perfectibles |
| Maintenabilité | 5 | Dette de convergence notable |
| DevOps / CI | 5 | Pipeline présent mais gate backend cassée |
| Prêt production | 4 | Stabilisation nécessaire avant mise en confiance |

Score global pondéré:
52 / 100

Interprétation:
Produit prometteur et déjà dense fonctionnellement.
Niveau actuel insuffisant pour un cycle de livraison serein sans correction prioritaire de la stabilité.

Décision recommandée:
Ne pas élargir le scope fonctionnel avant fermeture des risques P0 et P1.

---

## Synthèse Exécutive Finale

Ce projet n'est pas vide de qualité.
Il est riche et déjà utile.

Le frein principal n'est pas le manque de fonctionnalités.
Le frein principal est la convergence technique en période de transition.

Trois priorités absolues:
- rendre les tests fiables
- unifier le contrat de données
- supprimer les chemins legacy ambigus

Si ces trois priorités sont traitées immédiatement, le projet peut passer rapidement d'un état fragile à un état robuste.

---

## Annexe A - Ambiguïtés Formelles Relevées

- ⚠️ AMBIGUOUS: Cible finale unique non explicitée entre Blade historique et SPA React.
- ⚠️ AMBIGUOUS: Contrat canonical des champs classe enseignant non figé (teacher_id vs responsible_teacher_id).
- ⚠️ AMBIGUOUS: Contrat canonical des événements non figé (event_date/event_time vs start_date/end_date).
- ⚠️ AMBIGUOUS: Périmètre exact du terme exhaustif (tous fichiers disque vs tous fichiers applicatifs versionnés).
- ⚠️ AMBIGUOUS: Migrations non suivies présentes localement sans validation d'équipe.

---

## Annexe B - Preuves Runtime (Résumé)

Commande:
php artisan test

Sortie consolidée observée:
- Tests: 88 failed (0 assertions)
- Duration: 21.87s
- Erreur dominante: SQLSTATE HY000 table sessions already exists

Interprétation:
Le framework de test ne valide actuellement aucune logique métier.
Le premier défaut bloquant est de nature migration/base.

---

## Annexe C - Décision Produit Recommandée

Option recommandée:
Stabilisation technique immédiate.

Option non recommandée:
Ajout de nouvelles fonctionnalités avant correction des blocages de base.

Raison:
Chaque ajout de scope augmente un coût de correction qui est déjà en croissance.

---

## Annexe D - Plan De Contrôle Après Correctifs

Checkpoint 1:
Les migrations passent en base vierge sans collision.

Checkpoint 2:
La suite tests backend s'exécute sans échec bloquant de bootstrap.

Checkpoint 3:
Les endpoints critiques par rôle sont validés en smoke test.

Checkpoint 4:
Les pages SPA CRUD principales sont validées en parcours réel.

Checkpoint 5:
Le pipeline CI est vert de manière stable sur plusieurs runs.

---

## Annexe E - Résumé Court Pour Direction

État:
Produit fonctionnel partiellement stabilisé.

Risque majeur:
Instabilité de la chaîne qualité backend.

Investissement prioritaire:
1 semaine de convergence technique ciblée.

Bénéfice attendu:
Rétablissement de la vélocité et réduction nette du risque de régression.

---

Fin du corps principal du rapport.
Les annexes de traçabilité détaillées (manifest fichiers, inventaire routes, état git) sont ajoutées ci-dessous.

## Annexe F - Manifest Complet Des Fichiers (audit_manifest.csv)

Format: chemin | taille | lignes | texte | sha256

- .env.example | bytes=1332 | lines=67 | text=True | sha256=2F2FDDE50613F158A1973416ED1D6064D2C5B62F22059B069BE2816E60EDFC48
- .github/workflows/ci.yml | bytes=1139 | lines=50 | text=True | sha256=0A9AF98D134B8FC072295D1F07989BCD01F63EED77BCAE8EFBE07E9E26F33DB5
- .gitignore | bytes=383 | lines=27 | text=True | sha256=E55CEEFEF016D01AC9BEC8259A0FF7074865C3B80D489F00C8073DFB3D16D4A7
- README.md | bytes=12304 | lines=428 | text=True | sha256=1E880F596B10DD081791736D986EA9722C4F79DB55D90C2D58FB5928F29F9E08
- app/Http/Controllers/Admin/ClassController.php | bytes=4286 | lines=136 | text=True | sha256=1321D83CBD0FEB62F1A1646D26991C34E3025659D6D0147F5889D9DC19C1916A
- app/Http/Controllers/Admin/SubjectController.php | bytes=2895 | lines=89 | text=True | sha256=0D9406285306605E348AD3FF890CF5652A0D6399A1B688598E253C8114503A2A
- app/Http/Controllers/Admin/UserController.php | bytes=10467 | lines=315 | text=True | sha256=B450357D44C19973A19079921F60C04940720D0258B2D297F8C7697C04394488
- app/Http/Controllers/Api/AbsenceApiController.php | bytes=6286 | lines=196 | text=True | sha256=022A31E7B3F08A29F86A6E01147EEA45E418788D1FBB34599EFCBA5C20F7DEEB
- app/Http/Controllers/Api/AuthController.php | bytes=1998 | lines=76 | text=True | sha256=7FC0596BBB41B93266501EF03A2DD26D0AD6DF2590DF522BF746653ED90A7652
- app/Http/Controllers/Api/ClassApiController.php | bytes=2972 | lines=96 | text=True | sha256=BF4B2AF31FE2117D4AC73675A082DA4EDA8CC26650FF34EAF1AB277D309A0067
- app/Http/Controllers/Api/Concerns/ApiResponse.php | bytes=1333 | lines=46 | text=True | sha256=A3FBAF604E97E3F2E3BDBA2701340DF5360D8FB30BCFF323BEC9F9B019B389A4
- app/Http/Controllers/Api/DashboardApiController.php | bytes=6688 | lines=183 | text=True | sha256=D2176380977039A5F03558E009160285BA12D2538BCBB699645C32CE206A2AFB
- app/Http/Controllers/Api/DashboardController.php | bytes=5693 | lines=177 | text=True | sha256=FAB88C4D1EEFD9558D29D08C423D096DCEC9522CBEF9E2C1DF00898E7B5E231D
- app/Http/Controllers/Api/EventApiController.php | bytes=8904 | lines=267 | text=True | sha256=FE18C0B6CCC1FF4658633086C93BA508EA02D61157DE8DD36ADF6B775FB57317
- app/Http/Controllers/Api/GradeApiController.php | bytes=6711 | lines=213 | text=True | sha256=11D5E843EF47DDEA18D562CF98B1FE6A97683C02F146480501BF4B1AD7E43068
- app/Http/Controllers/Api/ReportCardApiController.php | bytes=5831 | lines=172 | text=True | sha256=A2C763B1CBCA11D23DEDCF9AE65A37B0AD9F78B359CEEB6A4DDF1A9DD99AFC45
- app/Http/Controllers/Api/SubjectApiController.php | bytes=3369 | lines=103 | text=True | sha256=62F683DF34D09D69306EA9A867A5E5DDEB87C8BD5A2710F76028C9147A1660CC
- app/Http/Controllers/Api/UserApiController.php | bytes=1936 | lines=68 | text=True | sha256=019C7FE94F96E7700C35CF24DC1496D24C046323B50B394A95FC5641DD146A24
- app/Http/Controllers/Auth/AuthController.php | bytes=6108 | lines=200 | text=True | sha256=F86B98115C84FDA66E614B9A1C55CC55EF5E8EF639074833B0FC201974399EAF
- app/Http/Controllers/Controller.php | bytes=320 | lines=13 | text=True | sha256=A031E1D490000F199CEA4C45000C97905093C3324AB904AA8BE60FADF4551A0F
- app/Http/Controllers/DashboardController.php | bytes=11805 | lines=338 | text=True | sha256=541DD50B048D498D5C034CC9798E72D6C95A4FE432D0AE24927349A338AE7746
- app/Http/Controllers/EventController.php | bytes=3928 | lines=120 | text=True | sha256=B6DCF1056E5E57BCBFAD10DF0C6D398496A2482F6910BBA3E6DDC7DB730E540B
- app/Http/Controllers/Parent/ChildrenController.php | bytes=5674 | lines=169 | text=True | sha256=31272720401532DCD7B1F26A9FDA910C60A370A9315E5C598C48042483E97A2C
- app/Http/Controllers/Student/ReportCardController.php | bytes=1758 | lines=61 | text=True | sha256=53E1C8D8BF0979297E9CAEFA03DCC0C7FF3390EFED686796FFA718E07D416F09
- app/Http/Controllers/Student/StudentGradeController.php | bytes=2497 | lines=72 | text=True | sha256=EE9A3D7A6C0D828AB77F0D3FD337EDF27664EABC9E23B2B518885F57CA5F809E
- app/Http/Controllers/Teacher/AbsenceController.php | bytes=4353 | lines=130 | text=True | sha256=C168704AEC0E175D719E1DAB00217EC3E2E1DC1B2B6A14FAC1F9CDC195408E3C
- app/Http/Controllers/Teacher/GradeController.php | bytes=7571 | lines=209 | text=True | sha256=DDE2E1EE9B4D37679ECB970AF9C467DA6975D302ADEBC7FEB61BD15236CAFAB0
- app/Http/Middleware/CheckRole.php | bytes=1779 | lines=67 | text=True | sha256=151186CEFB67B5DCE268CE8FD731ACA9322194859A80BED88279DEDEF88ED9B0
- app/Http/Middleware/RoleMiddleware.php | bytes=877 | lines=36 | text=True | sha256=4F28B1CA9B9E513C7B2C6E576CE64E51160478E69FDE266E6C0742939CCE03B1
- app/Http/Requests/Api/Absences/StoreAbsenceRequest.php | bytes=1383 | lines=40 | text=True | sha256=5A8529587BFBDB4B4CED7A4C3642C39868C349C3E040FC7571F2B9B155876FEE
- app/Http/Requests/Api/Absences/UpdateAbsenceRequest.php | bytes=1436 | lines=40 | text=True | sha256=35D498E0343C7ED0651A826F5E14F466FAE51C197B362FA5357A0DB3CDEF8ADE
- app/Http/Requests/Api/Classes/StoreClassRequest.php | bytes=1081 | lines=31 | text=True | sha256=C8B7986870CD3D97230F9194F89FDDD7E58F70F213CC501A8CEF46D43AB9C16D
- app/Http/Requests/Api/Classes/UpdateClassRequest.php | bytes=1313 | lines=39 | text=True | sha256=955CBD56FE89F76FB3226848908FA445479B692EF2212628837562E234BE0EFA
- app/Http/Requests/Api/Grades/StoreGradeRequest.php | bytes=1407 | lines=43 | text=True | sha256=91CC471BBD9B506E56C0C16A0A0EC86266DE803712F82E35D7B770D970CEBC0A
- app/Http/Requests/Api/Grades/UpdateGradeRequest.php | bytes=1486 | lines=43 | text=True | sha256=85100F6DCF91F6CDAACA890AF84E4C945DC34BECC28222C332BF2C1C67EDD28D
- app/Http/Requests/Api/Users/StoreUserRequest.php | bytes=948 | lines=30 | text=True | sha256=2C285D369E5ABC18E9E7AC6E472E1E230FBE1FD199486A1A4461B95CC9E3F1A4
- app/Http/Requests/Api/Users/UpdateUserRequest.php | bytes=1164 | lines=38 | text=True | sha256=F42B2397EC5FB55550A4CFDEFC3E21F8158AB83BEB2A3A32E8DDB49678DE6714
- app/Http/Requests/StoreGradeRequest.php | bytes=1391 | lines=47 | text=True | sha256=085074A5F948EFCC1D242D702B6F9753E2E1B5ACC1C32A0E85488EDBC84C01D8
- app/Http/Requests/StoreUserRequest.php | bytes=2047 | lines=57 | text=True | sha256=C90D2DCC04106D181D46D0E55274712B489C3C112BEE4F7F078D8BF045C214AF
- app/Models/Absence.php | bytes=3909 | lines=160 | text=True | sha256=AF7A6D2680D73E12D195D0CE8C09D51006DF786C1E12330200E1DB8031ACFD2F
- app/Models/ClassModel.php | bytes=3965 | lines=164 | text=True | sha256=35DE52DE39F46F7622FD28D9D2DEA071BA4885186E9DC820313FB09F39320C69
- app/Models/Event.php | bytes=4249 | lines=190 | text=True | sha256=FA744E8E009CFCD113B98E31D84FFFA559864019A5A757EA82AC4F65C313273C
- app/Models/Grade.php | bytes=3196 | lines=137 | text=True | sha256=F48A8AD6F743A961DF04B15BA3A0F3EA178E1533E5AD73B0FBBACD4EA97C3616
- app/Models/ReportCard.php | bytes=6557 | lines=203 | text=True | sha256=4C396059C9B44431B54306A6E2446A0427B8F3F85C6B9F2CE9E40C9BB1454B66
- app/Models/Subject.php | bytes=2585 | lines=109 | text=True | sha256=5F9E5B51C1C496DB4496832706BC8B3BA0AA6D715BCD4B4797F161854FD30B78
- app/Models/User.php | bytes=4963 | lines=206 | text=True | sha256=47A12C12A099F4A644ABA2F0D20BE21B14CEEA8BE041613C9C561695301BBD25
- app/Services/GradeService.php | bytes=3078 | lines=105 | text=True | sha256=60433AE4764C4B625203350098FAE225A53B1ED9BABF8A009E206B977EF3D961
- app/Services/UserService.php | bytes=4874 | lines=156 | text=True | sha256=64968FE6BE67587C4FD1278225D2E4DD380CC778900C70DCBA1535D586DA9D54
- artisan | bytes=347 | lines=14 | text=True | sha256=2F1D1B0F2F73B5389C26464E2C7EAAE1BEB047ADF47D11FE38367795B7605C93
- bootstrap/app.php | bytes=722 | lines=25 | text=True | sha256=66E93E6379054A41BCFE11BBC6AFE3DD7E7F7D4243C0F22D640FF8E2023AA242
- bootstrap/cache/packages.php | bytes=1479 | lines=74 | text=True | sha256=64F3EBC97C1A9FC3CB0F3DC85FAA51B8E0B2DE196F7CE6D144E051A16AE32941
- bootstrap/cache/services.php | bytes=21630 | lines=263 | text=True | sha256=8136D0281B19FC9FEEF060746F4F833AFF9DD0FAC5777679430206E7E9CAD61F
- check-views.ps1 | bytes=3103 | lines=98 | text=True | sha256=48951497FB04FAE2752D1D2937BECF1C7EF9D2EA2F3CF904893D5C4D568DFDFC
- composer.json | bytes=2229 | lines=71 | text=True | sha256=FDABA2064FABA8D8714441D7DBA7AE9DA72F4190C0B80804181F4245559FBD9F
- config/cors.php | bytes=408 | lines=20 | text=True | sha256=4C7F9F1F2F965C8B16C44E615D608CABED12DF71486F99C0A855BB51E49A48F0
- config/sanctum.php | bytes=3151 | lines=85 | text=True | sha256=C79CEECE147B11C35F15864C48339D60FC381760EAA08B8FF485CD9D0F9FDF8A
- create-views.ps1 | bytes=5700 | lines=94 | text=True | sha256=F00ECBC51FE1FB7754AD143B6A153D9913F3AACDA3013D4E6B297E858C74CBD4
- database/factories/AbsenceFactory.php | bytes=2144 | lines=69 | text=True | sha256=B87A491438D69D7386E95137EDFB7AFEEB270A2A96CE5CEC7FBA0EE4E20082DE
- database/factories/ClassModelFactory.php | bytes=1374 | lines=46 | text=True | sha256=124199A1FCF0819577B300E7F9DAFA5168CD4D76064305056903C79985B9BE00
- database/factories/GradeFactory.php | bytes=1306 | lines=41 | text=True | sha256=9D1C91A1E2EF3DE140186557FBC5D14AAF859850CBAFD5319A2E0C54EC58B517
- database/factories/ReportCardFactory.php | bytes=2352 | lines=65 | text=True | sha256=BA48B51E4713FE913EBAD743D53BD3E4A345A9D6BB4B6FD717CB5F3DCDE58CB1
- database/factories/SubjectFactory.php | bytes=1488 | lines=45 | text=True | sha256=2DDFB148F0CCF7BDEC45354693AD789C47DDB4B3207E5B93B4483CE013976E19
- database/factories/UserFactory.php | bytes=2619 | lines=97 | text=True | sha256=40B11F4F95FF8DDC285F6E0A3013EC241E4E1E30B580837A6E92E3DC0BD784ED
- database/migrations/2024_01_01_000001_create_users_table.php | bytes=2077 | lines=61 | text=True | sha256=52BC84028B1E159A38D36C4A6BB2BDC6E7ACCEA92D4EE747A6B22394F4BB1F15
- database/migrations/2024_01_01_000002_create_classes_table.php | bytes=1320 | lines=41 | text=True | sha256=B4C17070F62C495A51F8F710F3690F801F67503ABA3EB9B5D01F176D1F9DC244
- database/migrations/2024_01_01_000003_create_subjects_table.php | bytes=993 | lines=37 | text=True | sha256=5954A86B45BFFE61073CC1255B0DEFFC39F4ED5C84A2CACF17D3C33243CC3B71
- database/migrations/2024_01_01_000004_create_student_classes_table.php | bytes=1150 | lines=38 | text=True | sha256=0A0BF86785A535CC523E5DC08D2EDBE882177D21F5B7F47D500D765C01EA43BE
- database/migrations/2024_01_01_000005_create_class_subjects_table.php | bytes=1276 | lines=40 | text=True | sha256=27087D1CDDF2B93061580ACC446192A16AD9E2307C568257FC63B77CBACB1653
- database/migrations/2024_01_01_000006_create_parent_students_table.php | bytes=1185 | lines=38 | text=True | sha256=C9FF185D18F422C886ADB8867970054E93E04542F98292C7121E07801AD5B385
- database/migrations/2024_01_01_000007_create_grades_table.php | bytes=1954 | lines=51 | text=True | sha256=1791E07995A67752890DF6CFE244434744E6F99F9504C8D2912F1F3CFCBB0412
- database/migrations/2024_01_01_000008_create_absences_table.php | bytes=1983 | lines=50 | text=True | sha256=11ADC2C7E34BCBEF7D9C69C4956BF4FBCA5C66B1C301FFFAF0BD072D03856ADF
- database/migrations/2024_01_01_000009_create_report_cards_table.php | bytes=1919 | lines=50 | text=True | sha256=2CD0975D6B65B7DAE4B67B8AD0F8D97732AF30DD414C4F426556F16DB3BB03CF
- database/migrations/2024_01_01_000010_create_events_table.php | bytes=1569 | lines=45 | text=True | sha256=EC90E8D4E5385D585261F15046D86A424A5E1890D289C3BA8DC23014B6544C0A
- database/migrations/2024_11_17_000001_update_events_table_columns.php | bytes=1366 | lines=41 | text=True | sha256=A884AEC49FC223E5CD1E7EE02B5A9D74A0EE73AD71F8CE6351EF07D180E2119A
- database/migrations/2025_11_17_184036_add_teacher_id_to_subjects_table.php | bytes=898 | lines=33 | text=True | sha256=344537461AE063A9F2650BB11CE09BE6E94AF5AC1F2637646594498E7AF145E8
- database/migrations/2026_04_09_153223_create_personal_access_tokens_table.php | bytes=896 | lines=34 | text=True | sha256=98ECA17F82B2ED0FFF92729F5F26E31BAE8A6000BDEF95290F53AD06D9FF1DD1
- database/seeders/DatabaseSeeder.php | bytes=13870 | lines=328 | text=True | sha256=9D2FCED60381415DF59BCCDD83F148630266D2C3451F1BD07E97C21153AB5150
- docs/architecture-overview.md | bytes=7706 | lines=237 | text=True | sha256=D66DEE34DEBECE062AA6DB1DE7056317DC6A09CF5091E9BE16E496987B37BF74
- docs/database-schema.md | bytes=17270 | lines=385 | text=True | sha256=49CF83089CC0C1F8DBA0E81A5CAD20318CA47DDA3D416E56CE70D5ABE30F6FD9
- docs/project-report.md | bytes=14156 | lines=443 | text=True | sha256=043CF21AB0A645D5E15FD33C7738D14B5810384DE0F9A558966A229E6F3AA3C7
- docs/uml.md | bytes=11494 | lines=419 | text=True | sha256=67FAE1C322D66A6879B20E92B043322DB46E5987B768C575C8AA091AC3BBD965
- frontend/.env.example | bytes=20 | lines=2 | text=True | sha256=68890D67F6EC9B65FD8E9146BFC166EA3F2420CDA2DBCBBC9EED99C51266791F
- frontend/.gitignore | bytes=277 | lines=25 | text=True | sha256=D50AB07E11FA4BF3B1C7C3312E1D7BAEAB56CCA756832CCF40476823AD2A38B7
- frontend/eslint.config.js | bytes=787 | lines=30 | text=True | sha256=F6477B521C0D4C5AB0AC73D8CC8CE484795D23BECE7CBBD0CD2A9F4301337532
- frontend/index.html | bytes=373 | lines=14 | text=True | sha256=EFE2F9AA7779E8A21ED71C220218B25A67B9C12ACE66AD3ED02B181CE4E44A1D
- frontend/package.json | bytes=877 | lines=36 | text=True | sha256=28967C862D51286A762651F5E6100A9B4D0EA2D7315255D6518D1495DC21F86D
- frontend/postcss.config.js | bytes=86 | lines=7 | text=True | sha256=374F669F08B18E67D0A7264FA45B71228017E604FE7F6DB8A5C1BD5BA27E5C98
- frontend/public/favicon.svg | bytes=9522 | lines=1 | text=True | sha256=61BC9A161DE58248288E6905425D7180F0624C2865007B97D763FDAC12043A66
- frontend/public/icons.svg | bytes=5055 | lines=25 | text=True | sha256=7CA2D67C9C3AEBF50CDC8709EE8AACF8CDB8CC7EA6325EA94939CAEF5DA1C53D
- frontend/src/App.css | bytes=3075 | lines=185 | text=True | sha256=C2571CE7245629994B8ADC84F6C48E1920F047A4E7E29C89A4AEB51EEB1B5433
- frontend/src/App.jsx | bytes=450 | lines=19 | text=True | sha256=5321F7309EC9C20BE09DAF8E28521691EE1209FCBBAE44EB872031DD82EC796D
- frontend/src/assets/hero.png | bytes=44919 | lines=197 | text=True | sha256=72A860570EDDF1DD9988F26C7106C67BE286BC9F2FD3303C465CE87EDB1AE6CD
- frontend/src/assets/react.svg | bytes=4126 | lines=1 | text=True | sha256=35EF61ED53B323AE94A16A8EC659B3D0AF3880698791133F23B084085AB1C2E5
- frontend/src/assets/vite.svg | bytes=8710 | lines=2 | text=True | sha256=2F1F6C6F90A0EF7422CBB4CFAFCD8AD329C507A18AFDC34CC21FA72179B9C54A
- frontend/src/components/common/ConfirmModal.jsx | bytes=1686 | lines=66 | text=True | sha256=718134EBEBBC9EA14BEAD4F15E69AD5BCBB18C5F0A93B7FCBBBAA9F2006180C8
- frontend/src/components/common/CrudPage.jsx | bytes=15050 | lines=479 | text=True | sha256=6A3819D6C66EB61F8502E2D16A56A7DB9EE66EA9829441DCC0997A119CEB3E7F
- frontend/src/components/common/EmptyState.jsx | bytes=2011 | lines=72 | text=True | sha256=C636A08FEA54E96E03A2F9E8FF0B23CD1AC2345E99A3364349ACBCEA113614FC
- frontend/src/components/common/GlobalSpinner.jsx | bytes=217 | lines=8 | text=True | sha256=2929D44598183E24BEAE5CAAFDB85E170FB611504DFEC8C02AEF34081E75D71E
- frontend/src/components/common/PageHeader.jsx | bytes=422 | lines=12 | text=True | sha256=51D00855C00229323490C5DFBD701D48251295A460C7CDDC646551061E71CC7B
- frontend/src/components/common/Pagination.jsx | bytes=1992 | lines=70 | text=True | sha256=A965FE02867F618071FE5210E0B46F0690BD87477C54F8C0C12C73C8C3A4352F
- frontend/src/components/common/PrivateRoute.jsx | bytes=733 | lines=27 | text=True | sha256=12FD1AE0588A9CF9BD1AF2E97A90119B48E76EC3D03366DBAD6146F45FA73106
- frontend/src/components/common/ProtectedRoute.jsx | bytes=750 | lines=27 | text=True | sha256=0680B7C4BE963EE93053E6D96BB77D4E1820E71DC4F8FCF73EE417415538A97B
- frontend/src/components/common/StatCard.jsx | bytes=686 | lines=19 | text=True | sha256=3117A0EF8CB9B9794CD7C441D48B12C7912FA1FF16D8347C6A51CFD3DD4023D1
- frontend/src/components/ui/Alert.jsx | bytes=431 | lines=13 | text=True | sha256=833AF71F49AB082EC94CD8BA1BC82E86773FBF08594EF6B0E9B19CAB751B18F9
- frontend/src/components/ui/Badge.jsx | bytes=529 | lines=16 | text=True | sha256=2061BB0DFBDD619E7666E9B6813A6929472876AFFEE07C8154DD1F2E4F1D1416
- frontend/src/components/ui/Button.jsx | bytes=957 | lines=28 | text=True | sha256=E22090E97F05195C876F7BF2AAF804A36CD17B511FCB29BC8B218BE8F731ECC0
- frontend/src/components/ui/Input.jsx | bytes=571 | lines=13 | text=True | sha256=AA9221D593E04736C367F27081BFC07CE3C06C5E8ABCA774EEB2E6E93B946D49
- frontend/src/components/ui/Modal.jsx | bytes=969 | lines=29 | text=True | sha256=A33E9FAF617AFD96B77190BB80D6C4975687A37FDE2BF1E632F9EE31D9CFD08A
- frontend/src/components/ui/Select.jsx | bytes=761 | lines=19 | text=True | sha256=CD1FB933736C44BDDD44495B9FB3E68F0F7E0FCE277F0A6717B1E4CC14748364
- frontend/src/components/ui/Spinner.jsx | bytes=294 | lines=9 | text=True | sha256=12BD847AFE3886F52591C02848FEA031258D8DA56C952107735748CDA63741F1
- frontend/src/components/ui/Textarea.jsx | bytes=577 | lines=13 | text=True | sha256=330AD6DFFBD6DAA9020A25FE06A710B505301B10C26631F25C613E7DE30A1D34
- frontend/src/context/AuthContext.jsx | bytes=2302 | lines=88 | text=True | sha256=DA5FE2A355DA9330051CBFD01FF6EA9065C94F4FE75274EF2CBB73ACF0FAAC89
- frontend/src/context/LoadingContext.jsx | bytes=1430 | lines=59 | text=True | sha256=7572C15ECF16E5B18B2522C200610AC8C62242D08D6D3615AFB1206F3C76CE2C
- frontend/src/context/ThemeContext.jsx | bytes=1336 | lines=59 | text=True | sha256=67B8C77FB2DD1AA08A616A8328A05553CAA9EFCFEDB3B080F164FB0B3532EBFA
- frontend/src/hooks/useAsyncAction.js | bytes=672 | lines=31 | text=True | sha256=7797BA8D884C861309EE694E1E2B6FCFEBC25005A22CE93B93C81B591F89533F
- frontend/src/hooks/useAuth.js | bytes=283 | lines=13 | text=True | sha256=25DEAEB47BDF0CF8812F426322DFDF5A325F4B733F593642468A33C1ACFC7335
- frontend/src/hooks/useDebounce.js | bytes=407 | lines=18 | text=True | sha256=DF8DB34E892EDD7117AA553EDDAD99AF7EB35E16E3DD3AAEC7CCF754EA8D663A
- frontend/src/index.css | bytes=4458 | lines=223 | text=True | sha256=EC5920CD89A4E336EC582B5ABE4C3D0B8C0931AF08C8E4060BE3B1B971EE6B55
- frontend/src/layouts/AppLayout.jsx | bytes=1132 | lines=42 | text=True | sha256=3E59C25672C4C50431A110A89B6D1975EB1929C6A63A5E3D8BC4262BBEEEE2F4
- frontend/src/layouts/Sidebar.jsx | bytes=1502 | lines=50 | text=True | sha256=80575758A8AB827E085DEE5B80C5D716FF1032013EEC99A18A8E99E3D17E05BF
- frontend/src/layouts/TopBar.jsx | bytes=2192 | lines=53 | text=True | sha256=A4677B435765CAE0CD93FC94320142361FE90DDB0C0C3BC684A6408D8EA887CF
- frontend/src/main.jsx | bytes=610 | lines=21 | text=True | sha256=329DDDB2590CB95E4F2A23E6EA23F3B2B8021B2A399BE2B0845D05771E945761
- frontend/src/pages/NotFoundPage.jsx | bytes=776 | lines=20 | text=True | sha256=640ED80852E7605CC23341476E84589CC871F5CA2BDA5C2BB7CDE3F7D17A45E1
- frontend/src/pages/UnauthorizedPage.jsx | bytes=800 | lines=22 | text=True | sha256=712094B24EC4816031BDD4020D7068FF69BE7E18C3287EC06AB5D272E850F9A8
- frontend/src/pages/absences/AbsencesPage.jsx | bytes=4769 | lines=139 | text=True | sha256=A7F1AB3D568CD0CFDA4D122CB398A4155246D7BA8659A1547AEDD9F959906D81
- frontend/src/pages/auth/LoginPage.jsx | bytes=3234 | lines=94 | text=True | sha256=DC21617AEF04AA5AA990C458C2590169BC97C1A6A040067FE454AD6513F9B61B
- frontend/src/pages/classes/ClassesPage.jsx | bytes=2670 | lines=74 | text=True | sha256=9CF4BE728C0045061A428DFB21CB1B7D021DE20471104A1C68F439A54E903051
- frontend/src/pages/dashboard/AdminDashboard.jsx | bytes=14085 | lines=479 | text=True | sha256=7DC6D16D0C1F82DA1BB0B99897DA1F843C141AA16706FF27B3CA512180CD8724
- frontend/src/pages/dashboard/DashboardPage.jsx | bytes=3091 | lines=105 | text=True | sha256=1A3D3C34D60C7288094B70B5B6AF49066AF86161900857428B8A1896849CF9FC
- frontend/src/pages/dashboard/ParentDashboard.jsx | bytes=5364 | lines=187 | text=True | sha256=575F27D143636ABE0C84D1DF0854DA79919C6633ADFB48A6C8276B149B72DEF1
- frontend/src/pages/dashboard/StudentDashboard.jsx | bytes=5037 | lines=168 | text=True | sha256=67960E9EE1233B19AAD184A55AD6FAD925303D532A1A9F6D049B736C0ABDCD18
- frontend/src/pages/dashboard/TeacherDashboard.jsx | bytes=6467 | lines=228 | text=True | sha256=351B4BA2B42B1767F59B746449194790DFA1DCCB3EB529007E69640E3E5DEBE5
- frontend/src/pages/events/EventsPage.jsx | bytes=4227 | lines=108 | text=True | sha256=8709558578E3FA72A19EE9C694E5998AF5A0FCEFF9EDC83CE43F459BAAEC3303
- frontend/src/pages/grades/GradesPage.jsx | bytes=4892 | lines=138 | text=True | sha256=E1D99F84DE20867471355E5DD122119359D5C7CEEBDC480A4A8528A62EF58BDF
- frontend/src/pages/report-cards/ReportCardsPage.jsx | bytes=4711 | lines=115 | text=True | sha256=44FAD4B5577BEFFEEBFC76260A1F3F4274F8E3584436D237C7619A95BA0D97EC
- frontend/src/pages/subjects/SubjectsPage.jsx | bytes=2464 | lines=72 | text=True | sha256=C8F4F6B71E2344008DB6CB279F3DBC787B257CB7B405155009F2993B3F4CF88B
- frontend/src/pages/users/UsersPage.jsx | bytes=3171 | lines=97 | text=True | sha256=CEEE5953ABB9AF7FCEE3B7642903D2410DD3B343E97F7973AB1F77D8E9E86EB7
- frontend/src/routes/AppRouter.jsx | bytes=3295 | lines=100 | text=True | sha256=EA2FCDE2C2009E0115DFF0A2D44BDADCB4D373324DE5F254C9D17E5688407606
- frontend/src/services/absencesService.js | bytes=164 | lines=6 | text=True | sha256=8D03650239574F5192CA4564D3C733D0F303954EED3EE63C766909F5F98422D9
- frontend/src/services/apiClient.js | bytes=1711 | lines=63 | text=True | sha256=FA35DD0656DDC0268129C7510A78F86795F2161824D311E19F2DAD8DE676E9FD
- frontend/src/services/authService.js | bytes=1001 | lines=41 | text=True | sha256=4C08CA16706845AD91D8140C785AB2A0AAA48F1F29A741582234CC5212C45EC0
- frontend/src/services/classesService.js | bytes=162 | lines=6 | text=True | sha256=63FAD95DCB048D97CFBFCA58B7CD8DDC1CAD6CBD929808095A06F40D8201E766
- frontend/src/services/crudService.js | bytes=781 | lines=31 | text=True | sha256=DBCB49FC11BE9425AB898BBB2ED414467EA1652D600C2F3DA9C282A30B4DAE31
- frontend/src/services/dashboardService.js | bytes=1514 | lines=56 | text=True | sha256=5B4798CA3B325DD4F13FD9D4D74EACF0FBF8E991ED5EA551FB6FDE1DBBC152D9
- frontend/src/services/eventsService.js | bytes=160 | lines=6 | text=True | sha256=7055C6D3D355842D93B03808DAA34B58574ED87CE9C92B6151ADCDE894163C90
- frontend/src/services/gradesService.js | bytes=160 | lines=6 | text=True | sha256=18B0394C21F94A760652CB33AA1AF1C565070606F44ECD0C010F529D8EE7FD5E
- frontend/src/services/reportCardsService.js | bytes=170 | lines=6 | text=True | sha256=70E7BE54AA9DB58AB19A0EDB1315F7032FC96C79067CCED4DD1884339CA5A9EB
- frontend/src/services/requestWithFallback.js | bytes=772 | lines=29 | text=True | sha256=C49DCF5C4365AA0FF815B2273882E53BBA85A84B515E765ECC5CFD27F1C82753
- frontend/src/services/resourceServiceFactory.js | bytes=2131 | lines=81 | text=True | sha256=72C33317328F72EC53E60206B37A2A0D1D1F2725672FEC8C5A0B937EB719B16D
- frontend/src/services/subjectsService.js | bytes=164 | lines=6 | text=True | sha256=106B82E747148FA94997E002E497D6083E53247FF18106BBAA5743B8A212D08F
- frontend/src/services/usersService.js | bytes=158 | lines=6 | text=True | sha256=22E99C347105E08F3C9AB25553519E627A62FC9C1971B521BB5FBA4DBF247E97
- frontend/src/utils/chartSetup.js | bytes=292 | lines=23 | text=True | sha256=8FB8570C0548DEBEC9875841B1521924B2692CC8DD0E0AC45A2F8B9A40D7EA13
- frontend/src/utils/constants.js | bytes=2694 | lines=99 | text=True | sha256=9DFEAB54679EBDB1BDDEF27B417248A0B36F32219EC3EE6F7C0DBCA3A663130C
- frontend/src/utils/format.js | bytes=819 | lines=46 | text=True | sha256=0636C2019775F74BA20BB044318D94DA02F51AE4ADB51F41EFFAA562455D616B
- frontend/src/utils/response.js | bytes=1273 | lines=58 | text=True | sha256=8A2F07E32B84669320865D0853966ACE8C2F1D06DA30B4707EC072F0E3E7CF6A
- frontend/src/utils/storage.js | bytes=788 | lines=35 | text=True | sha256=60F747C0590C4E3E3FF80E12D60FDC969A8F2961738C634D6216F77893F28CFC
- frontend/tailwind.config.js | bytes=491 | lines=24 | text=True | sha256=0317C9B3A8A2DF705D7537C3EF052D8917968B3AE436076F5B96DFEB0D551E79
- frontend/vite.config.js | bytes=565 | lines=25 | text=True | sha256=692423C79011FDDFF0C96AEAA489E5AFC6161992387A5FD2959C4784CD6EC463
- package.json | bytes=468 | lines=20 | text=True | sha256=D7FB2182D04D70F716C7282146161DAF16E60D27EF0BBD1599EED390EB99F609
- phpunit.xml | bytes=1197 | lines=34 | text=True | sha256=070DD746BA624B6B90452FED0E24BBA01D37F7EC3AD47765FD81053C8C30CF4D
- public/index.php | bytes=485 | lines=18 | text=True | sha256=4D4AAC096DE2BD6AAF33A0FA10AF2FBDAE987C159CB7D9BAA1BF09B161281E55
- resources/css/app.css | bytes=890 | lines=49 | text=True | sha256=6982626F1A72C70A76BE10131AEAFAB13511CB29B33A43FF31DB4EDE4287DB5E
- resources/js/app.js | bytes=1227 | lines=41 | text=True | sha256=0874B1E1AE46BFF6E4A1C9228020B6A1D0D517C4B4619E879EF2AC5E94524500
- resources/js/bootstrap.js | bytes=640 | lines=19 | text=True | sha256=48EFE65B64040185088A28285744332C6084440EE5B49361BE8AEFA0D15CCF89
- resources/views/admin/absences/index.blade.php | bytes=10209 | lines=182 | text=True | sha256=BE9485AA584B84E7D479C077451667CD6AE1A0187179F52255F3527EDFC4C8F6
- resources/views/admin/classes/create.blade.php | bytes=9525 | lines=170 | text=True | sha256=6649FDA1A800ED5053AB9B5DC39268C0D240557752F6FC006C1026AA558EC092
- resources/views/admin/classes/edit.blade.php | bytes=9921 | lines=171 | text=True | sha256=AA1E66765D59E945C8AEAE90E7728DF395FDCD00FFE143D4A3964A1C9F13BC03
- resources/views/admin/classes/index.blade.php | bytes=11667 | lines=184 | text=True | sha256=D49413904AB53A6192E94E355A5C1C6676EF1D6A9D9F0C0F7B1CC85776ADBA40
- resources/views/admin/classes/show.blade.php | bytes=534 | lines=21 | text=True | sha256=4E1AA73E37EAC2C480D4B839AF91BBD2F8A6D421CC8AA6A2A97103B79C61AE24
- resources/views/admin/dashboard-new.blade.php | bytes=10172 | lines=204 | text=True | sha256=28488E9F6670C8B06501A953297D407601D1BD634F8CA6DD86EA5E5B8806E267
- resources/views/admin/dashboard.blade.php | bytes=30239 | lines=542 | text=True | sha256=4C3E5B448078EB1835E7C65E78AA2279038B468AAA1EB99A62FE1453642750D3
- resources/views/admin/events/create.blade.php | bytes=13898 | lines=229 | text=True | sha256=5456B0FDF676384ACF53B8312D94E8A1F0142213D17E87D245885CC9ABE368F4
- resources/views/admin/events/edit.blade.php | bytes=530 | lines=21 | text=True | sha256=AC0C1E56EABFC4AB9DBB41474A32AC8CD31D4ED26773C5EA024E686D6D220BEB
- resources/views/admin/events/index.blade.php | bytes=13213 | lines=220 | text=True | sha256=33988AAB9C52E22C790F0ABD75232628EF928E87AD60F72AD3B5ADA709ACF199
- resources/views/admin/events/show.blade.php | bytes=534 | lines=21 | text=True | sha256=FFE19B83AE5674FC90065582599900AB7F2EE0DA3E7346C23077510484FF9BAF
- resources/views/admin/grades/index.blade.php | bytes=10082 | lines=161 | text=True | sha256=EF3C5CBA7650C8DD25B60F507A6E795A277CBEC92A266C60B9A03EC85E930C54
- resources/views/admin/reports/index.blade.php | bytes=9345 | lines=156 | text=True | sha256=1EA81AFA79B4305A392EA7ED1115FB94534E1B55AD6334E109B105B05056D075
- resources/views/admin/subjects/create.blade.php | bytes=9675 | lines=167 | text=True | sha256=0D5C3F6F90852E0BD67A61C2A1816DB29E531FAF3230AEBABE35EBCBE24C74B2
- resources/views/admin/subjects/edit.blade.php | bytes=11026 | lines=186 | text=True | sha256=B508B370DFCF26EC83C96164B8432C4D3C825E21396D86CE2F13235483893A5C
- resources/views/admin/subjects/index.blade.php | bytes=10866 | lines=184 | text=True | sha256=A9DB9D900E00DE1322BBBEED85A4AA945E0D94323F582C98C0103991FF604B3C
- resources/views/admin/subjects/show.blade.php | bytes=540 | lines=21 | text=True | sha256=817E9CFB396903D9A1C2B87A3BE4C3A342F0DC612DAF6B168988C9A747CEDD9D
- resources/views/admin/users/create.blade.php | bytes=11213 | lines=214 | text=True | sha256=CFA494BE8C4DEB16A5B066644A302578A9DA42E805C065C8F1267B3A07D1A87A
- resources/views/admin/users/edit.blade.php | bytes=8364 | lines=137 | text=True | sha256=CB670867DF4A80DB9C47CF15923C0754BD7844A38706A70ED26109A21D4C3EAB
- resources/views/admin/users/index.blade.php | bytes=12618 | lines=212 | text=True | sha256=62A89DB91A12F82E3B3C58DC9023F17B8FD327505434EC72B2AB3D4985177C63
- resources/views/admin/users/show.blade.php | bytes=531 | lines=21 | text=True | sha256=03982DE041C861A9A30D4A880914316FDC32EA46A19693C7371A80804F156BC1
- resources/views/auth/login.blade.php | bytes=12087 | lines=226 | text=True | sha256=F3971955CC411B222D6DE9134CABE4DB415DCD95A751281254E8FDA963FDB3A5
- resources/views/auth/password-reset.blade.php | bytes=5409 | lines=111 | text=True | sha256=32AFF025129B33777936A0DC293CDCC9A596319874BCB4100EE1BA0188AF3CC5
- resources/views/auth/register.blade.php | bytes=11673 | lines=178 | text=True | sha256=8140B45E4CCF8C67127F0B41FEB4953888BB195E3CC27E6729A4F35BD8772CBF
- resources/views/components/badge.blade.php | bytes=1005 | lines=36 | text=True | sha256=262101EB1F14F1BD75C272A8563B537D03BB958966461A1A92641ED70BB495E2
- resources/views/components/button.blade.php | bytes=2609 | lines=56 | text=True | sha256=96198F3A615B5BBAA59883F641C83172EFB675D8B9734F897AA39DEA88FA2400
- resources/views/components/card.blade.php | bytes=1539 | lines=42 | text=True | sha256=E48EAB799931806F9FB3FA820425D40CFACCB25FC86232F876A2C2F15C24224D
- resources/views/components/input.blade.php | bytes=2121 | lines=63 | text=True | sha256=25F981DC255E5D92455D28B3FCF6BC1DAA0EC1E438D72DC2BAA5D331B5F832A2
- resources/views/components/logo.blade.php | bytes=1394 | lines=43 | text=True | sha256=BF9B7A3350193E727E66CCA96A580FE8DB9FEA09A41E672E5134846CE115AF78
- resources/views/components/modern/alert.blade.php | bytes=4445 | lines=82 | text=True | sha256=F21A7DF7B50026B665EB10A40305AEA9A34174AC82602362B471EF32E481BEB0
- resources/views/components/modern/badge.blade.php | bytes=1766 | lines=54 | text=True | sha256=F39DA7E29AE9D4957697C267FC8DEC2975E9D90EF0D71766B7AEFE4E5977F0E9
- resources/views/components/modern/button.blade.php | bytes=3696 | lines=72 | text=True | sha256=0115B1FA5922111E007526BAB629F936DD4EE5B4141BBD32EF1B9F3D27C32436
- resources/views/components/modern/card.blade.php | bytes=1938 | lines=52 | text=True | sha256=D036BCD7601AADD672745656E6CFA210B3914FC721D7712406FA84B1432D60EB
- resources/views/components/modern/empty-state.blade.php | bytes=1746 | lines=49 | text=True | sha256=013E269BF144CCC81A12564A9886BE27E2B5F951D6A5C4F297DF608FB441D517
- resources/views/components/modern/input.blade.php | bytes=3488 | lines=82 | text=True | sha256=07AFACC83A5D76E7FAE41D3E11EF2BA48F1006215C1451040C730AE82A7B7D5B
- resources/views/components/modern/placeholder.blade.php | bytes=3300 | lines=65 | text=True | sha256=A9CFE56A5CB461CCBEABBAD0C6AA65AD87600D6BABC964E69B063E0739452322
- resources/views/components/modern/select.blade.php | bytes=3335 | lines=82 | text=True | sha256=36B24C76C61BE7731C24212D94D68AD0AA030B4AAC8F992139DC3B99530BFFCC
- resources/views/components/modern/skeleton.blade.php | bytes=3630 | lines=88 | text=True | sha256=61BE34F9CCCAFA361E039A57470906AF6E06CF63F7B8F70F9A69A0734371B6B8
- resources/views/components/modern/stat-card.blade.php | bytes=3164 | lines=64 | text=True | sha256=A75ED8996B5E92C95169DE7FB418CCD9C416AFC5F5668152814D890C8A14F9E8
- resources/views/components/modern/table.blade.php | bytes=3120 | lines=76 | text=True | sha256=31786FDF574150850F4488660372AE157043AE6B26554DE84D1068B0FECE4E4E
- resources/views/components/stat-card.blade.php | bytes=2127 | lines=54 | text=True | sha256=F45E092890B4BD75EE20E599802E7A00FDB11208A09210D45BF3DDA915955D4B
- resources/views/components/table.blade.php | bytes=773 | lines=25 | text=True | sha256=0623B8375C00FAA9A28C5001B3D5E75F683FE210764B02F830C5BC3DB4369DC3
- resources/views/layouts/admin-nav.blade.php | bytes=1630 | lines=28 | text=True | sha256=1DC3AFCE195A6EA6EEE957DA51AC8AC013C1F2041E17A639F26AF86CEA9A71E6
- resources/views/layouts/admin.blade.php | bytes=100 | lines=6 | text=True | sha256=A67EDC3E4517D724B92B77E975E2C4E2CFB1395D1407E48CE12FCCECC0F3FDAC
- resources/views/layouts/app.blade.php | bytes=491 | lines=16 | text=True | sha256=38739AE39761A670F534DD258C41CCC6520A7339CFF945655744FA7DC23A4BF0
- resources/views/layouts/base.blade.php | bytes=15750 | lines=311 | text=True | sha256=5FC5D1287E3070C113AEA802288020D91705A195FF841FFC810CEB64749DD22C
- resources/views/layouts/parent-nav.blade.php | bytes=1172 | lines=23 | text=True | sha256=FBCE2580863E795B369BF47F3695C1CEECC0AE0D2356ECD02187111CBC0A2169
- resources/views/layouts/student-nav.blade.php | bytes=1379 | lines=25 | text=True | sha256=B98DE2506F46D2646E9BF91F7E4BB3AB63CC4B8373AFB15EC1C0029ECEB42BBC
- resources/views/layouts/teacher-nav.blade.php | bytes=1274 | lines=24 | text=True | sha256=4F10FAAF1F65762A9ABF9C5D7DA31FC06EF271EF2A42BC2C1232477B2176F63D
- resources/views/parent/children/absences.blade.php | bytes=537 | lines=21 | text=True | sha256=D1DFDB89EC68F9EA3309257347C59E865C9426F1F36044ED31ACA063F1EC1862
- resources/views/parent/children/grades.blade.php | bytes=531 | lines=21 | text=True | sha256=7AB5135B1F5761E179D3F29D03455059FD7C74196BB75044FA5006E3A9762EF7
- resources/views/parent/children/index.blade.php | bytes=528 | lines=21 | text=True | sha256=FAE8157F84C68324D39E5270B3CB69ADC8AC3ECA6C4D017BF8F71212B10C6D47
- resources/views/parent/children/report-card.blade.php | bytes=534 | lines=21 | text=True | sha256=3AD9AB9FB9462DAE0918480A4FC14546A31E3E77BBF89ABC0F58E75046279DAA
- resources/views/parent/children/report-cards.blade.php | bytes=549 | lines=21 | text=True | sha256=1ACE46D23EA97257FF92B8FCD7C49ACA04C40453CDB4FAB88AA1178D7AF1EA57
- resources/views/parent/children/show.blade.php | bytes=532 | lines=21 | text=True | sha256=C8354DF0ECA0D1D951212762FCAEAF0ED9C7517CA4735DD229303E24B7E6CE81
- resources/views/parent/dashboard.blade.php | bytes=16245 | lines=263 | text=True | sha256=79DFFDD2A84B83E32E49553C3AB95CC3F39A1FFD99F58DA39ADF8B42C71F5186
- resources/views/parent/events/index.blade.php | bytes=511 | lines=21 | text=True | sha256=4BF5BBE65EB1B9B43C00523EB976DAA5D762EF6A3DF9EFD4D9363629791BB2F7
- resources/views/profile.blade.php | bytes=13969 | lines=231 | text=True | sha256=AC15F498DCD6DAE1D1820541302F65380909E0FB732476FF1C0F9B334BEF8E2B
- resources/views/student/absences/index.blade.php | bytes=6906 | lines=122 | text=True | sha256=833489EAE11C6B7403A16B0653AF4675FB41EF44802509C0D717FACFAACCDA45
- resources/views/student/dashboard.blade.php | bytes=16885 | lines=266 | text=True | sha256=CBE142CC47F371ED726D353EEA355EE85AE84B7531857720C35A8B9A3C6C0189
- resources/views/student/events/index.blade.php | bytes=5934 | lines=108 | text=True | sha256=7D343E7FEC1BFDCC34D5DE5EF33490D42536B0C1FEA27536D3FB4A0615385782
- resources/views/student/grades/by-subject.blade.php | bytes=535 | lines=21 | text=True | sha256=DF802AC645B915C51D55C2D4EFB36D291303DD0BA47207AA1C3289CAED79FBDA
- resources/views/student/grades/index.blade.php | bytes=8318 | lines=137 | text=True | sha256=3CFF1DB3A904363682C958221AF7855694155212252C65593A1564B8F6EC2AEC
- resources/views/student/no-class.blade.php | bytes=7343 | lines=103 | text=True | sha256=22F1D78ADA6708C315329656B662B5D5498EC10B5B2629609648B2FD45BB3747
- resources/views/student/report-cards/index.blade.php | bytes=6571 | lines=122 | text=True | sha256=8AA7F324BAEBF1709402D708789B390E1BF4DB4C582CFCEEE7E83BADD15D5D31
- resources/views/student/report-cards/pdf.blade.php | bytes=524 | lines=21 | text=True | sha256=672EA517CF8486C64B32C57A5E679FE259654A3117A476C96EC9A3D84A6282E8
- resources/views/student/report-cards/show.blade.php | bytes=534 | lines=21 | text=True | sha256=3AD9AB9FB9462DAE0918480A4FC14546A31E3E77BBF89ABC0F58E75046279DAA
- resources/views/teacher/absences/by-class.blade.php | bytes=535 | lines=21 | text=True | sha256=35861E8924EA062C3C3C672F0C81865EBC9FAEF0B29E91733361AECC66EB7A01
- resources/views/teacher/absences/create.blade.php | bytes=533 | lines=21 | text=True | sha256=277356955F5205132A90E7D9BE89CDD2950E106DBDEF48BFB31F3C7385941F22
- resources/views/teacher/absences/index.blade.php | bytes=522 | lines=21 | text=True | sha256=60B81F39C6B49C70A55C03E0F573F05F832CB4B424B4C57E317A8AF29F05C257
- resources/views/teacher/classes/index.blade.php | bytes=6658 | lines=127 | text=True | sha256=478A321CE52D08A80B835A3FA3FE3FCD5D605C95EF9052298006EDBA1414379C
- resources/views/teacher/dashboard.blade.php | bytes=14151 | lines=228 | text=True | sha256=CDBE130B1E28163BD0A0F08B6F004118F4B5D4122086BA217FCF47CE8A4605C0
- resources/views/teacher/grades/by-class.blade.php | bytes=529 | lines=21 | text=True | sha256=67791BE95BE2AEF9193172B0DDDC76DFFA82B14788903D5BAA8D66FFB816C50A
- resources/views/teacher/grades/create.blade.php | bytes=530 | lines=21 | text=True | sha256=D0072EED8C09EAC78A4479584A093785353E479E3947DB0E7DE814F3D73470DE
- resources/views/teacher/grades/edit.blade.php | bytes=528 | lines=21 | text=True | sha256=46A229FF0A15CB9D41A3EB5866C27944D488E2BF3F195C0D2216C620F92257EB
- resources/views/teacher/grades/index.blade.php | bytes=1970 | lines=50 | text=True | sha256=53B3D4C754A443F771ABB4CC90AAECB7AD1D445DDEC6FB759290ACABE6597C32
- resources/views/teacher/students/profile.blade.php | bytes=538 | lines=21 | text=True | sha256=BB29641B4A50143A7EF426EB6A639D52C4ED37E8EEC453275E1EEFB50C2E1A54
- routes/api.php | bytes=3322 | lines=63 | text=True | sha256=A248540150C8A77583696C98822052F46EA75FDD46C66AF758A4AD76AF053E3F
- routes/console.php | bytes=218 | lines=9 | text=True | sha256=CAA76041FC460E95B575E17A285C68B6A344D4AA1C7A31DD2C6DFB752DB10423
- routes/web.php | bytes=7948 | lines=145 | text=True | sha256=59711A35600714290989895B29A5D9F3839342D3C0461C503773362735D14006
- start-project.ps1 | bytes=5314 | lines=153 | text=True | sha256=80252F63618E0B249187AE153C7CF911FB30746EEA055D7F75C82516B00F6F29
- storage/fonts/.gitkeep | bytes=0 | lines=0 | text=True | sha256=E3B0C44298FC1C149AFBF4C8996FB92427AE41E4649B934CA495991B7852B855
- storage/framework/cache/data/.gitignore | bytes=16 | lines=3 | text=True | sha256=4BB38BE6D6D9EF0D2F9BCC339850FF48D821ECCFA739369402B354C9BA946EA2
- storage/framework/sessions/.gitignore | bytes=16 | lines=3 | text=True | sha256=4BB38BE6D6D9EF0D2F9BCC339850FF48D821ECCFA739369402B354C9BA946EA2
- storage/framework/views/.gitignore | bytes=16 | lines=3 | text=True | sha256=4BB38BE6D6D9EF0D2F9BCC339850FF48D821ECCFA739369402B354C9BA946EA2
- tailwind.config.js | bytes=3022 | lines=116 | text=True | sha256=A28009DA6C990695BB658DB9C7F419F04411EF09B83D2744574551DC1CB60D3D
- tests/CreatesApplication.php | bytes=396 | lines=22 | text=True | sha256=EEC7252483126515F6FC8A30CE2D9A785D4C6FF9355145208C18951AE0B9C946
- tests/Feature/AbsenceManagementTest.php | bytes=10556 | lines=337 | text=True | sha256=BE3ABFC7E49DAC0B22ED690D570016C21407C43ABE527FF6A0DC3F7E07AFD3E3
- tests/Feature/ApiModulesAccessTest.php | bytes=2027 | lines=63 | text=True | sha256=3FA13C6BA72BAA322594FCB30197B25758565C1DB98F5F32E0EE4DA26F3A2C41
- tests/Feature/AuthenticationTest.php | bytes=5206 | lines=187 | text=True | sha256=5ADF818F2CBB7D1CD9CC366C29D9BFA697187A9B38E13FC21E3C09FB480E847B
- tests/Feature/GradeManagementTest.php | bytes=10184 | lines=325 | text=True | sha256=E56DC487E0EB9377208512752C9355EC70F4EDBC04E271C4D6283AA7A434C525
- tests/Feature/ProfileTest.php | bytes=4554 | lines=149 | text=True | sha256=0D45580CF46D5057D389F86ECE976241EFFD02D081DFFB8BD6D1096A23818F2E
- tests/Feature/ReportCardTest.php | bytes=10880 | lines=346 | text=True | sha256=6C47CA425C7070CD10693806E191099FC0D9997AD53D3A0B92289057A5DA5A00
- tests/Feature/SanctumAuthenticationApiTest.php | bytes=3567 | lines=127 | text=True | sha256=DE57953274D82B7BB6F654AC07555F37D939F5A8CE37036492E4E9252FA021EF
- tests/Feature/UIConsistencyTest.php | bytes=5950 | lines=190 | text=True | sha256=FD1D7612B0802FA6F4CD7ECE05BB256393948AFDFC03501F2A63EABDC0C0B8CC
- tests/Feature/UserFlowsTest.php | bytes=7162 | lines=241 | text=True | sha256=D8A4A64854D3FE263EF0E6A19DA9A0FBAAED4C172C85023537937AC3AC6E547D
- tests/Feature/UserManagementTest.php | bytes=8286 | lines=283 | text=True | sha256=E9AFF3950744B71730E19496A1719A0A5BAA9E2E211A575532520B61595B6AC8
- tests/TestCase.php | bytes=173 | lines=11 | text=True | sha256=265854028C0C14F635B8799C0820CAA0D81D8E7DACEA6647D11C34E46EE777BB
- tests/Unit/.gitkeep | bytes=0 | lines=0 | text=True | sha256=E3B0C44298FC1C149AFBF4C8996FB92427AE41E4649B934CA495991B7852B855
- vite.config.js | bytes=324 | lines=15 | text=True | sha256=899F3BC63F4DEFE7EBCAEEB104111A84ED23577F2339F3F890EAC52C513A8A68
- vite.config.mjs | bytes=324 | lines=15 | text=True | sha256=899F3BC63F4DEFE7EBCAEEB104111A84ED23577F2339F3F890EAC52C513A8A68

## Annexe G - Inventaire Complet Des Routes (route-list.json)

Format: méthode uri | nom | action | middleware

- GET|HEAD / | domain=null | name=null | action=Closure | middleware=web
- POST _ignition/execute-solution | domain=null | name=ignition.executeSolution | action=Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController | middleware=Spatie\LaravelIgnition\Http\Middleware\RunnableSolutionsEnabled
- GET|HEAD _ignition/health-check | domain=null | name=ignition.healthCheck | action=Spatie\LaravelIgnition\Http\Controllers\HealthCheckController | middleware=Spatie\LaravelIgnition\Http\Middleware\RunnableSolutionsEnabled
- POST _ignition/update-config | domain=null | name=ignition.updateConfig | action=Spatie\LaravelIgnition\Http\Controllers\UpdateConfigController | middleware=Spatie\LaravelIgnition\Http\Middleware\RunnableSolutionsEnabled
- GET|HEAD admin/absences | domain=null | name=admin.absences.index | action=App\Http\Controllers\Admin\UserController@absencesIndex | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/classes | domain=null | name=admin.classes.index | action=App\Http\Controllers\Admin\ClassController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/classes | domain=null | name=admin.classes.store | action=App\Http\Controllers\Admin\ClassController@store | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/classes/create | domain=null | name=admin.classes.create | action=App\Http\Controllers\Admin\ClassController@create | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/classes/{class} | domain=null | name=admin.classes.show | action=App\Http\Controllers\Admin\ClassController@show | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- PUT|PATCH admin/classes/{class} | domain=null | name=admin.classes.update | action=App\Http\Controllers\Admin\ClassController@update | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- DELETE admin/classes/{class} | domain=null | name=admin.classes.destroy | action=App\Http\Controllers\Admin\ClassController@destroy | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/classes/{class}/assign-students | domain=null | name=admin.classes.assign-students | action=App\Http\Controllers\Admin\ClassController@assignStudents | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/classes/{class}/assign-subjects | domain=null | name=admin.classes.assign-subjects | action=App\Http\Controllers\Admin\ClassController@assignSubjects | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/classes/{class}/edit | domain=null | name=admin.classes.edit | action=App\Http\Controllers\Admin\ClassController@edit | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/dashboard | domain=null | name=admin.dashboard | action=App\Http\Controllers\DashboardController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/events | domain=null | name=admin.events.index | action=App\Http\Controllers\EventController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/events | domain=null | name=admin.events.store | action=App\Http\Controllers\EventController@store | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/events/create | domain=null | name=admin.events.create | action=App\Http\Controllers\EventController@create | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/events/{event} | domain=null | name=admin.events.show | action=App\Http\Controllers\EventController@show | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- PUT|PATCH admin/events/{event} | domain=null | name=admin.events.update | action=App\Http\Controllers\EventController@update | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- DELETE admin/events/{event} | domain=null | name=admin.events.destroy | action=App\Http\Controllers\EventController@destroy | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/events/{event}/edit | domain=null | name=admin.events.edit | action=App\Http\Controllers\EventController@edit | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/grades | domain=null | name=admin.grades.index | action=App\Http\Controllers\Admin\UserController@gradesIndex | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/reports | domain=null | name=admin.reports.index | action=App\Http\Controllers\Admin\UserController@reportsIndex | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/subjects | domain=null | name=admin.subjects.index | action=App\Http\Controllers\Admin\SubjectController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/subjects | domain=null | name=admin.subjects.store | action=App\Http\Controllers\Admin\SubjectController@store | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/subjects/create | domain=null | name=admin.subjects.create | action=App\Http\Controllers\Admin\SubjectController@create | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/subjects/{subject} | domain=null | name=admin.subjects.show | action=App\Http\Controllers\Admin\SubjectController@show | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- PUT|PATCH admin/subjects/{subject} | domain=null | name=admin.subjects.update | action=App\Http\Controllers\Admin\SubjectController@update | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- DELETE admin/subjects/{subject} | domain=null | name=admin.subjects.destroy | action=App\Http\Controllers\Admin\SubjectController@destroy | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/subjects/{subject}/edit | domain=null | name=admin.subjects.edit | action=App\Http\Controllers\Admin\SubjectController@edit | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/users | domain=null | name=admin.users.index | action=App\Http\Controllers\Admin\UserController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/users | domain=null | name=admin.users.store | action=App\Http\Controllers\Admin\UserController@store | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/users/bulk-delete | domain=null | name=admin.users.bulk-delete | action=App\Http\Controllers\Admin\UserController@bulkDelete | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/users/create | domain=null | name=admin.users.create | action=App\Http\Controllers\Admin\UserController@create | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/users/{id}/restore | domain=null | name=admin.users.restore | action=App\Http\Controllers\Admin\UserController@restore | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/users/{user} | domain=null | name=admin.users.show | action=App\Http\Controllers\Admin\UserController@show | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- PUT|PATCH admin/users/{user} | domain=null | name=admin.users.update | action=App\Http\Controllers\Admin\UserController@update | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- DELETE admin/users/{user} | domain=null | name=admin.users.destroy | action=App\Http\Controllers\Admin\UserController@destroy | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD admin/users/{user}/edit | domain=null | name=admin.users.edit | action=App\Http\Controllers\Admin\UserController@edit | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- POST admin/users/{user}/toggle-status | domain=null | name=admin.users.toggle-status | action=App\Http\Controllers\Admin\UserController@toggleStatus | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/absences | domain=null | name=absences.index | action=App\Http\Controllers\Api\AbsenceApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- POST api/admin/absences | domain=null | name=absences.store | action=App\Http\Controllers\Api\AbsenceApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/absences/{absence} | domain=null | name=absences.show | action=App\Http\Controllers\Api\AbsenceApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- PUT|PATCH api/admin/absences/{absence} | domain=null | name=absences.update | action=App\Http\Controllers\Api\AbsenceApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- DELETE api/admin/absences/{absence} | domain=null | name=absences.destroy | action=App\Http\Controllers\Api\AbsenceApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/classes | domain=null | name=classes.index | action=App\Http\Controllers\Api\ClassApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- POST api/admin/classes | domain=null | name=classes.store | action=App\Http\Controllers\Api\ClassApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/classes/{class} | domain=null | name=classes.show | action=App\Http\Controllers\Api\ClassApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- PUT|PATCH api/admin/classes/{class} | domain=null | name=classes.update | action=App\Http\Controllers\Api\ClassApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- DELETE api/admin/classes/{class} | domain=null | name=classes.destroy | action=App\Http\Controllers\Api\ClassApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/dashboard | domain=null | name=null | action=App\Http\Controllers\Api\DashboardApiController@admin | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/events | domain=null | name=events.index | action=App\Http\Controllers\Api\EventApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- POST api/admin/events | domain=null | name=events.store | action=App\Http\Controllers\Api\EventApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/events/{event} | domain=null | name=events.show | action=App\Http\Controllers\Api\EventApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- PUT|PATCH api/admin/events/{event} | domain=null | name=events.update | action=App\Http\Controllers\Api\EventApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- DELETE api/admin/events/{event} | domain=null | name=events.destroy | action=App\Http\Controllers\Api\EventApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/grades | domain=null | name=grades.index | action=App\Http\Controllers\Api\GradeApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- POST api/admin/grades | domain=null | name=grades.store | action=App\Http\Controllers\Api\GradeApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/grades/{grade} | domain=null | name=grades.show | action=App\Http\Controllers\Api\GradeApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- PUT|PATCH api/admin/grades/{grade} | domain=null | name=grades.update | action=App\Http\Controllers\Api\GradeApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- DELETE api/admin/grades/{grade} | domain=null | name=grades.destroy | action=App\Http\Controllers\Api\GradeApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/report-cards | domain=null | name=report-cards.index | action=App\Http\Controllers\Api\ReportCardApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- POST api/admin/report-cards | domain=null | name=report-cards.store | action=App\Http\Controllers\Api\ReportCardApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/report-cards/{report_card} | domain=null | name=report-cards.show | action=App\Http\Controllers\Api\ReportCardApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- PUT|PATCH api/admin/report-cards/{report_card} | domain=null | name=report-cards.update | action=App\Http\Controllers\Api\ReportCardApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- DELETE api/admin/report-cards/{report_card} | domain=null | name=report-cards.destroy | action=App\Http\Controllers\Api\ReportCardApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/subjects | domain=null | name=subjects.index | action=App\Http\Controllers\Api\SubjectApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- POST api/admin/subjects | domain=null | name=subjects.store | action=App\Http\Controllers\Api\SubjectApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/subjects/{subject} | domain=null | name=subjects.show | action=App\Http\Controllers\Api\SubjectApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- PUT|PATCH api/admin/subjects/{subject} | domain=null | name=subjects.update | action=App\Http\Controllers\Api\SubjectApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- DELETE api/admin/subjects/{subject} | domain=null | name=subjects.destroy | action=App\Http\Controllers\Api\SubjectApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/users | domain=null | name=users.index | action=App\Http\Controllers\Api\UserApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- POST api/admin/users | domain=null | name=users.store | action=App\Http\Controllers\Api\UserApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/admin/users/{user} | domain=null | name=users.show | action=App\Http\Controllers\Api\UserApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- PUT|PATCH api/admin/users/{user} | domain=null | name=users.update | action=App\Http\Controllers\Api\UserApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- DELETE api/admin/users/{user} | domain=null | name=users.destroy | action=App\Http\Controllers\Api\UserApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin
- GET|HEAD api/dashboard/absences-per-month | domain=null | name=null | action=App\Http\Controllers\Api\DashboardController@absencesPerMonth | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin,teacher
- GET|HEAD api/dashboard/average-per-subject | domain=null | name=null | action=App\Http\Controllers\Api\DashboardController@averagePerSubject | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin,teacher
- GET|HEAD api/dashboard/grade-evolution | domain=null | name=null | action=App\Http\Controllers\Api\DashboardController@gradeEvolution | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin,teacher,student,parent
- GET|HEAD api/dashboard/kpis | domain=null | name=null | action=App\Http\Controllers\Api\DashboardController@kpis | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin,teacher
- GET|HEAD api/dashboard/students-per-class | domain=null | name=null | action=App\Http\Controllers\Api\DashboardController@studentsPerClass | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:admin,teacher
- POST api/login | domain=null | name=null | action=App\Http\Controllers\Api\AuthController@login | middleware=api,web,Illuminate\Auth\Middleware\RedirectIfAuthenticated,Illuminate\Routing\Middleware\ThrottleRequests:5,1
- POST api/logout | domain=null | name=null | action=App\Http\Controllers\Api\AuthController@logout | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum
- GET|HEAD api/parent/children/absences | domain=null | name=null | action=App\Http\Controllers\Api\AbsenceApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:parent
- GET|HEAD api/parent/children/absences/{absence} | domain=null | name=null | action=App\Http\Controllers\Api\AbsenceApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:parent
- GET|HEAD api/parent/children/grades | domain=null | name=null | action=App\Http\Controllers\Api\GradeApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:parent
- GET|HEAD api/parent/children/grades/{grade} | domain=null | name=null | action=App\Http\Controllers\Api\GradeApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:parent
- GET|HEAD api/parent/dashboard | domain=null | name=null | action=App\Http\Controllers\Api\DashboardApiController@parent | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:parent
- GET|HEAD api/student/absences | domain=null | name=absences.index | action=App\Http\Controllers\Api\AbsenceApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:student
- GET|HEAD api/student/absences/{absence} | domain=null | name=absences.show | action=App\Http\Controllers\Api\AbsenceApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:student
- GET|HEAD api/student/dashboard | domain=null | name=null | action=App\Http\Controllers\Api\DashboardApiController@student | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:student
- GET|HEAD api/student/grades | domain=null | name=grades.index | action=App\Http\Controllers\Api\GradeApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:student
- GET|HEAD api/student/grades/{grade} | domain=null | name=grades.show | action=App\Http\Controllers\Api\GradeApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:student
- GET|HEAD api/teacher/absences | domain=null | name=absences.index | action=App\Http\Controllers\Api\AbsenceApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- POST api/teacher/absences | domain=null | name=absences.store | action=App\Http\Controllers\Api\AbsenceApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- GET|HEAD api/teacher/absences/{absence} | domain=null | name=absences.show | action=App\Http\Controllers\Api\AbsenceApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- PUT|PATCH api/teacher/absences/{absence} | domain=null | name=absences.update | action=App\Http\Controllers\Api\AbsenceApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- DELETE api/teacher/absences/{absence} | domain=null | name=absences.destroy | action=App\Http\Controllers\Api\AbsenceApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- GET|HEAD api/teacher/dashboard | domain=null | name=null | action=App\Http\Controllers\Api\DashboardApiController@teacher | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- GET|HEAD api/teacher/grades | domain=null | name=grades.index | action=App\Http\Controllers\Api\GradeApiController@index | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- POST api/teacher/grades | domain=null | name=grades.store | action=App\Http\Controllers\Api\GradeApiController@store | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- GET|HEAD api/teacher/grades/{grade} | domain=null | name=grades.show | action=App\Http\Controllers\Api\GradeApiController@show | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- PUT|PATCH api/teacher/grades/{grade} | domain=null | name=grades.update | action=App\Http\Controllers\Api\GradeApiController@update | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- DELETE api/teacher/grades/{grade} | domain=null | name=grades.destroy | action=App\Http\Controllers\Api\GradeApiController@destroy | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum,App\Http\Middleware\CheckRole:teacher
- GET|HEAD api/user | domain=null | name=null | action=App\Http\Controllers\Api\AuthController@user | middleware=api,web,Illuminate\Auth\Middleware\Authenticate:sanctum
- GET|HEAD dashboard | domain=null | name=dashboard | action=App\Http\Controllers\DashboardController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate
- GET|HEAD login | domain=null | name=login | action=App\Http\Controllers\Auth\AuthController@showLoginForm | middleware=web,Illuminate\Auth\Middleware\RedirectIfAuthenticated
- POST login | domain=null | name=null | action=App\Http\Controllers\Auth\AuthController@login | middleware=web,Illuminate\Auth\Middleware\RedirectIfAuthenticated,Illuminate\Routing\Middleware\ThrottleRequests:5,1
- POST logout | domain=null | name=logout | action=App\Http\Controllers\Auth\AuthController@logout | middleware=web,Illuminate\Auth\Middleware\Authenticate
- GET|HEAD parent/children | domain=null | name=parent.children.index | action=App\Http\Controllers\Parent\ChildrenController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:parent
- GET|HEAD parent/children/{child} | domain=null | name=parent.children.show | action=App\Http\Controllers\Parent\ChildrenController@show | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:parent
- GET|HEAD parent/children/{child}/absences | domain=null | name=parent.children.absences | action=App\Http\Controllers\Parent\ChildrenController@absences | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:parent
- GET|HEAD parent/children/{child}/grades | domain=null | name=parent.children.grades | action=App\Http\Controllers\Parent\ChildrenController@grades | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:parent
- GET|HEAD parent/children/{child}/report-cards | domain=null | name=parent.children.report-cards | action=App\Http\Controllers\Parent\ChildrenController@reportCards | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:parent
- GET|HEAD parent/children/{child}/report-cards/{reportCard} | domain=null | name=parent.children.report-card | action=App\Http\Controllers\Parent\ChildrenController@viewReportCard | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:parent
- GET|HEAD parent/dashboard | domain=null | name=parent.dashboard | action=App\Http\Controllers\DashboardController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:parent
- GET|HEAD parent/events | domain=null | name=parent.events | action=Closure | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:parent
- GET|HEAD password/reset | domain=null | name=password.reset | action=App\Http\Controllers\Auth\AuthController@showPasswordResetForm | middleware=web,Illuminate\Auth\Middleware\Authenticate
- POST password/reset | domain=null | name=null | action=App\Http\Controllers\Auth\AuthController@resetPassword | middleware=web,Illuminate\Auth\Middleware\Authenticate
- GET|HEAD profile | domain=null | name=profile | action=App\Http\Controllers\Auth\AuthController@profile | middleware=web,Illuminate\Auth\Middleware\Authenticate
- PUT profile | domain=null | name=profile.update | action=App\Http\Controllers\Auth\AuthController@updateProfile | middleware=web,Illuminate\Auth\Middleware\Authenticate
- GET|HEAD profile/show | domain=null | name=profile.show | action=App\Http\Controllers\Auth\AuthController@profile | middleware=web,Illuminate\Auth\Middleware\Authenticate
- GET|HEAD sanctum/csrf-cookie | domain=null | name=sanctum.csrf-cookie | action=Laravel\Sanctum\Http\Controllers\CsrfCookieController@show | middleware=web
- GET|HEAD student/absences | domain=null | name=student.absences | action=App\Http\Controllers\Student\StudentGradeController@absences | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:student
- GET|HEAD student/dashboard | domain=null | name=student.dashboard | action=App\Http\Controllers\DashboardController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:student
- GET|HEAD student/events | domain=null | name=student.events | action=Closure | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:student
- GET|HEAD student/grades | domain=null | name=student.grades.index | action=App\Http\Controllers\Student\StudentGradeController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:student
- GET|HEAD student/grades/subject/{subject} | domain=null | name=student.grades.by-subject | action=App\Http\Controllers\Student\StudentGradeController@bySubject | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:student
- GET|HEAD student/report-cards | domain=null | name=student.report-cards.index | action=App\Http\Controllers\Student\ReportCardController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:student
- GET|HEAD student/report-cards/{reportCard} | domain=null | name=student.report-cards.show | action=App\Http\Controllers\Student\ReportCardController@show | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:student
- GET|HEAD student/report-cards/{reportCard}/download | domain=null | name=student.report-cards.download | action=App\Http\Controllers\Student\ReportCardController@download | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:student
- GET|HEAD teacher/absences | domain=null | name=teacher.absences.index | action=App\Http\Controllers\Teacher\AbsenceController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- POST teacher/absences | domain=null | name=teacher.absences.store | action=App\Http\Controllers\Teacher\AbsenceController@store | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- POST teacher/absences/batch | domain=null | name=teacher.absences.batch | action=App\Http\Controllers\Teacher\AbsenceController@batchEntry | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD teacher/absences/class/{class} | domain=null | name=teacher.absences.by-class | action=App\Http\Controllers\Teacher\AbsenceController@byClass | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD teacher/absences/create | domain=null | name=teacher.absences.create | action=App\Http\Controllers\Teacher\AbsenceController@create | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- POST teacher/absences/{absence}/justify | domain=null | name=teacher.absences.justify | action=App\Http\Controllers\Teacher\AbsenceController@justify | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD teacher/classes | domain=null | name=teacher.classes | action=Closure | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD teacher/dashboard | domain=null | name=teacher.dashboard | action=App\Http\Controllers\DashboardController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD teacher/grades | domain=null | name=teacher.grades.index | action=App\Http\Controllers\Teacher\GradeController@index | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- POST teacher/grades | domain=null | name=teacher.grades.store | action=App\Http\Controllers\Teacher\GradeController@store | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- POST teacher/grades/batch | domain=null | name=teacher.grades.batch | action=App\Http\Controllers\Teacher\GradeController@batchEntry | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD teacher/grades/class/{class} | domain=null | name=teacher.grades.by-class | action=App\Http\Controllers\Teacher\GradeController@byClass | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD teacher/grades/create | domain=null | name=teacher.grades.create | action=App\Http\Controllers\Teacher\GradeController@create | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- PUT teacher/grades/{grade} | domain=null | name=teacher.grades.update | action=App\Http\Controllers\Teacher\GradeController@update | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- DELETE teacher/grades/{grade} | domain=null | name=teacher.grades.destroy | action=App\Http\Controllers\Teacher\GradeController@destroy | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD teacher/grades/{grade}/edit | domain=null | name=teacher.grades.edit | action=App\Http\Controllers\Teacher\GradeController@edit | middleware=web,Illuminate\Auth\Middleware\Authenticate,App\Http\Middleware\CheckRole:teacher
- GET|HEAD up | domain=null | name=null | action=Closure | middleware=

## Annexe H - État Git Local (Synthèse)

Format: statut chemin

-  M app/Http/Controllers/Api/GradeApiController.php
-  M app/Http/Controllers/Teacher/GradeController.php
-  M app/Http/Requests/Api/Grades/StoreGradeRequest.php
-  M app/Http/Requests/Api/Grades/UpdateGradeRequest.php
-  M app/Http/Requests/StoreGradeRequest.php
-  M config/sanctum.php
-  M database/factories/GradeFactory.php
-  M database/migrations/2024_01_01_000007_create_grades_table.php
-  M database/seeders/DatabaseSeeder.php
-  M frontend/.env.example
-  M frontend/src/pages/absences/AbsencesPage.jsx
-  M frontend/src/pages/grades/GradesPage.jsx
-  M frontend/src/services/apiClient.js
-  M frontend/src/services/authService.js
-  M routes/web.php
-  M start-project.ps1
-  M tests/Feature/GradeManagementTest.php
-  M tests/Feature/ReportCardTest.php
- ?? audit_controllers_extract.txt
- ?? audit_frontend_extract.txt
- ?? audit_manifest.csv
- ?? audit_migration_extract.txt
- ?? audit_model_extract.txt
- ?? audit_tests_extract.txt
- ?? database/migrations/2026_04_09_181108_create_cache_table.php
- ?? database/migrations/2026_04_09_181108_create_sessions_table.php
- ?? database/migrations/2026_04_09_181109_create_failed_jobs_table.php
- ?? database/migrations/2026_04_09_181109_create_jobs_table.php
- ?? database/migrations/2026_04_16_000001_normalize_grades_to_twenty_scale.php
- ?? rapport.md
- ?? route-list.json

## Annexe I - Échantillon Des Erreurs De Tests (Signatures)

Extraits de signatures observées lors du run backend.

- SQLSTATE[HY000]: General error: 1 table sessions already exists
- Illuminate\\Database\\QueryException
- RefreshDatabase::migrateFreshUsing
- Aucun test métier atteint avant bootstrap DB
