# Analyse fonctionnelle — SI Boutique Gaming

## 1. Présentation du projet

### 1.1 Contexte
Système d'information web destiné à une boutique spécialisée dans la vente de matériels et accessoires de jeux vidéo (consoles PlayStation, Nintendo, Xbox, manettes, casques, jeux, cartes cadeaux, accessoires et produits dérivés).

### 1.2 Objectifs
- Centraliser la gestion commerciale et logistique
- Automatiser les flux stock → vente → facture → livraison
- Fournir un tableau de bord décisionnel en temps réel
- Sécuriser l'accès par rôles et permissions
- Garantir la traçabilité des opérations

### 1.3 Périmètre fonctionnel

| Module | Description |
|--------|-------------|
| Authentification | Connexion, déconnexion, réinitialisation MDP, sessions |
| Utilisateurs & Rôles | CRUD utilisateurs, 4 rôles, permissions granulaires |
| Produits & Catégories | Catalogue complet, import/export Excel |
| Stock | Entrées, sorties, inventaire, alertes, historique |
| Fournisseurs | CRUD, historique approvisionnement |
| Clients | CRUD, historique achats |
| Ventes | Création, validation, annulation, déduction stock auto |
| Factures | Génération auto, PDF (DomPDF), impression |
| Livraisons | Suivi statuts, affectation livreur |
| Livreurs | CRUD, missions, zones |
| Rapports | Ventes, CA, stock, livraisons — export PDF/Excel |
| Notifications | Stock faible, rupture, commandes, livraisons |
| Dashboard | KPIs, graphiques, alertes |

### 1.4 Acteurs du Système

| Acteur | Description |
|--------|-------------|
| **Administrateur** | Super-utilisateur, accès total au SI |
| **Gestionnaire** | Gestion produits, stock, ventes, rapports |
| **Caissier** | Création ventes, gestion clients, factures |
| **Livreur** | Consultation et mise à jour de ses livraisons |

### 1.5 Contraintes techniques
- Laravel 10, PHP 8.2+, MySQL
- Blade + Bootstrap 5 (pas de Vue.js)
- Architecture MVC, Services, Policies, Repository si pertinent
- Responsive (mobile, tablette, desktop)
- Sécurité : CSRF, middleware rôles, Form Requests, journalisation

---

## 2. Besoins fonctionnels détaillés

### BF-01 — Authentification
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-01.1 | Connexion par email/mot de passe | Haute |
| BF-01.2 | Déconnexion sécurisée | Haute |
| BF-01.3 | Réinitialisation mot de passe par email | Haute |
| BF-01.4 | Gestion des sessions (timeout, remember me) | Moyenne |
| BF-01.5 | Blocage compte inactif | Haute |

### BF-02 — Gestion des rôles et permissions
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-02.1 | 4 rôles prédéfinis (Admin, Gestionnaire, Caissier, Livreur) | Haute |
| BF-02.2 | Middleware de contrôle d'accès par rôle | Haute |
| BF-02.3 | CRUD utilisateurs (Admin uniquement) | Haute |
| BF-02.4 | Affectation rôle à l'utilisateur | Haute |

### BF-03 — Gestion des produits
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-03.1 | CRUD produit (référence, code-barres, nom, description, catégorie, marque, prix achat/vente, stock, stock min, image, statut) | Haute |
| BF-03.2 | Recherche et filtres (catégorie, marque, statut, stock) | Haute |
| BF-03.3 | Import Excel produits | Moyenne |
| BF-03.4 | Export Excel produits | Moyenne |
| BF-03.5 | Upload image produit | Moyenne |

### BF-04 — Gestion des catégories
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-04.1 | CRUD catégories (Consoles, Jeux, Manettes, Casques, Accessoires, Cartes Cadeaux, Autres) | Haute |
| BF-04.2 | Association produit ↔ catégorie | Haute |

### BF-05 — Gestion du stock
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-05.1 | Entrée de stock (approvisionnement) | Haute |
| BF-05.2 | Sortie de stock (hors vente) | Haute |
| BF-05.3 | Ajustement / inventaire | Haute |
| BF-05.4 | Historique mouvements (date, produit, qté, utilisateur, motif) | Haute |
| BF-05.5 | Alertes rupture et stock faible | Haute |
| BF-05.6 | Déduction automatique à la validation vente | Haute |

### BF-06 — Gestion des fournisseurs
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-06.1 | CRUD fournisseur | Haute |
| BF-06.2 | Historique approvisionnement par fournisseur | Moyenne |

### BF-07 — Gestion des clients
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-07.1 | CRUD client | Haute |
| BF-07.2 | Historique des achats | Haute |
| BF-07.3 | Recherche rapide (nom, téléphone, email) | Haute |

### BF-08 — Gestion des ventes
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-08.1 | Création vente multi-produits | Haute |
| BF-08.2 | Modification avant validation (brouillon) | Haute |
| BF-08.3 | Validation vente → déduction stock + génération facture | Haute |
| BF-08.4 | Annulation vente (avec restitution stock si validée) | Haute |
| BF-08.5 | Calcul automatique remise, TVA, totaux | Haute |
| BF-08.6 | Numéro de vente unique auto-généré | Haute |

### BF-09 — Gestion des factures
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-09.1 | Génération automatique à la validation vente | Haute |
| BF-09.2 | Numéro facture unique | Haute |
| BF-09.3 | Export PDF (DomPDF) | Haute |
| BF-09.4 | Impression et téléchargement | Haute |
| BF-09.5 | Historique factures | Haute |

### BF-10 — Gestion des livraisons
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-10.1 | Création livraison liée à une vente/client | Haute |
| BF-10.2 | 6 statuts (en attente, assignée, en cours, livrée, échouée, annulée) | Haute |
| BF-10.3 | Affectation livreur | Haute |
| BF-10.4 | Historique des changements de statut | Haute |
| BF-10.5 | Architecture extensible pour carte de suivi GPS | Moyenne |

### BF-11 — Gestion des livreurs
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-11.1 | CRUD livreur | Haute |
| BF-11.2 | Affectation livraisons | Haute |
| BF-11.3 | Historique missions | Haute |

### BF-12 — Rapports et statistiques
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-12.1 | Rapport ventes (jour, mois, année) | Haute |
| BF-12.2 | Rapport produits les plus vendus | Haute |
| BF-12.3 | Rapport chiffre d'affaires | Haute |
| BF-12.4 | Rapport stock | Haute |
| BF-12.5 | Rapport livraisons | Moyenne |
| BF-12.6 | Export PDF et Excel | Haute |

### BF-13 — Tableau de bord
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-13.1 | KPIs : CA jour/mois, nb ventes, nb produits, ruptures, stock faible, clients, livraisons en attente | Haute |
| BF-13.2 | Graphique ventes par mois | Haute |
| BF-13.3 | Graphique ventes par catégorie | Haute |
| BF-13.4 | Top produits vendus | Haute |

### BF-14 — Notifications
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-14.1 | Alerte stock faible | Haute |
| BF-14.2 | Alerte rupture stock | Haute |
| BF-14.3 | Notification nouvelle commande | Moyenne |
| BF-14.4 | Notification livraison assignée | Haute |

### BF-15 — Sécurité et traçabilité
| ID | Besoin | Priorité |
|----|--------|----------|
| BF-15.1 | Protection CSRF sur tous les formulaires | Haute |
| BF-15.2 | Validation via Form Requests | Haute |
| BF-15.3 | Journalisation des activités utilisateur | Haute |
| BF-15.4 | Gestion centralisée des erreurs | Moyenne |

---

## 3. Besoins non fonctionnels

| ID | Catégorie | Exigence |
|----|-----------|----------|
| BNF-01 | Performance | Temps de réponse < 2s pour les pages courantes |
| BNF-02 | Sécurité | Hachage bcrypt, protection XSS/CSRF/SQL injection |
| BNF-03 | Disponibilité | Compatible XAMPP / serveur LAMP local |
| BNF-04 | Maintenabilité | Code documenté, architecture MVC + Services |
| BNF-05 | Ergonomie | Interface dashboard moderne, responsive Bootstrap 5 |
| BNF-06 | Évolutivité | Architecture extensible (carte GPS, API future) |
| BNF-07 | Traçabilité | Historique complet stock, livraisons, activités |

---

## 4. Matrice des permissions par rôle

| Fonctionnalité | Admin | Gestionnaire | Caissier | Livreur |
|----------------|:-----:|:------------:|:--------:|:-------:|
| Dashboard complet | ✓ | ✓ | Partiel | — |
| Gestion utilisateurs | ✓ | — | — | — |
| Produits (CRUD) | ✓ | ✓ | Lecture | — |
| Catégories | ✓ | ✓ | — | — |
| Stock | ✓ | ✓ | — | — |
| Fournisseurs | ✓ | ✓ | — | — |
| Clients | ✓ | ✓ | ✓ | — |
| Ventes | ✓ | ✓ | ✓ | — |
| Factures | ✓ | ✓ | ✓ | — |
| Livraisons | ✓ | ✓ | Lecture | ✓ (assignées) |
| Livreurs | ✓ | ✓ | — | — |
| Rapports | ✓ | ✓ | — | — |
| Notifications | ✓ | ✓ | ✓ | ✓ |

---

## 5. Règles de gestion (RG)

| ID | Règle |
|----|-------|
| RG-01 | Un produit inactif ne peut pas être ajouté à une nouvelle vente |
| RG-02 | La validation d'une vente décrémente le stock et est irréversible sans annulation |
| RG-03 | L'annulation d'une vente validée restitue le stock |
| RG-04 | Une facture est générée automatiquement à la validation (1 vente = 1 facture) |
| RG-05 | Le stock ne peut pas devenir négatif (contrôle à la validation) |
| RG-06 | Alerte stock faible si `stock_quantity <= minimum_stock` et > 0 |
| RG-07 | Alerte rupture si `stock_quantity = 0` |
| RG-08 | Seul un Admin peut gérer les utilisateurs et rôles |
| RG-09 | Un livreur ne voit que ses livraisons assignées |
| RG-10 | Chaque mouvement de stock est historisé avec utilisateur et motif |
| RG-11 | Chaque changement de statut livraison est historisé |
| RG-12 | Numéros vente/facture/livraison générés automatiquement et uniques |

---

## 6. Flux métier principaux

### 6.1 Flux de vente
```
Création brouillon → Ajout produits → Calcul totaux → Validation
    → Déduction stock → Génération facture → (Optionnel) Création livraison
```

### 6.2 Flux d'approvisionnement
```
Sélection fournisseur → Entrée stock → Mise à jour quantité produit
    → Enregistrement mouvement → Notification si seuil atteint
```

### 6.3 Flux de livraison
```
Création → En attente → Assignation livreur → Assignée
    → En cours → Livrée / Échouée / Annulée
```

---

## 7. Arborescence cible du projet

```
gaming-store-si/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Policies/
│   ├── Repositories/
│   ├── Services/
│   └── Notifications/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/
│   └── views/
│       ├── layouts/
│       ├── auth/
│       ├── dashboard/
│       ├── products/
│       ├── categories/
│       ├── stock/
│       ├── suppliers/
│       ├── customers/
│       ├── sales/
│       ├── invoices/
│       ├── deliveries/
│       ├── drivers/
│       ├── reports/
│       └── users/
├── routes/
│   └── web.php
├── docs/
└── public/
```
