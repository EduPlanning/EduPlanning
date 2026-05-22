Projet de Fin d'Études - Cahier des Charges

**Conception et Développement**  
**d'un Site Web de Gestion**  
**d'Emploi du Temps**

| **Établissement**       | Ecole des technique économiques et commercial |
| ----------------------- | --------------------------------------------- |
| **Filière**             | Développement informatique                    |
| **Niveau**              | Technicien spécialise                         |
| **Année universitaire** | 2025 - 2026                                   |
| **Encadrant**           | Prof Abderrahman                              |
| **Réalisé par**         | Nouhaila Elbouraqqady                         |

_Version 1.0 - 21 mai 2026_

# 1\. Introduction

## 1.1 Contexte général

La gestion des emplois du temps est une problématique centrale dans les établissements d'enseignement et les entreprises. La planification manuelle est chronophage, sujette aux erreurs et difficile à communiquer en temps réel à l'ensemble des parties prenantes.

Ce projet de fin d'études vise à concevoir et développer une application web moderne permettant d'automatiser et de centraliser la gestion des emplois du temps, en offrant une expérience utilisateur fluide aussi bien sur desktop que sur mobile.

## 1.2 Problématique

Les établissements éducatifs et organisations confrontés à la gestion d'emplois du temps rencontrent typiquement les difficultés suivantes :

- Chevauchements de créneaux horaires entre enseignants, salles ou groupes.
- Absence d'un canal centralisé de diffusion des plannings.
- Modifications fréquentes non répercutées instantanément auprès des concernés.
- Manque de visibilité globale et de rapports analytiques.
- Processus manuel gourmand en temps et en ressources humaines.

## 1.3 Objectifs du projet

Le projet a pour principaux objectifs :

- Concevoir une architecture logicielle robuste et évolutive.
- Développer une interface web intuitive, responsive et accessible.
- Mettre en place un système de rôles et de permissions granulaires.
- Implémenter la détection automatique des conflits de planification.
- Fournir des fonctionnalités de notification et d'export des plannings.
- Garantir la sécurité et la confidentialité des données.

# 2\. Périmètre et Portée du Projet

## 2.1 Périmètre fonctionnel inclus

- Authentification sécurisée (inscription, connexion, récupération de mot de passe).
- Gestion des utilisateurs avec rôles : Administrateur, Enseignant, Étudiant.
- Gestion des ressources : salles, matières, groupes, enseignants.
- Création, modification et suppression d'événements / créneaux horaires.
- Détection automatique des conflits (double réservation de salle ou d'enseignant).
- Vue calendrier (journalière, hebdomadaire, mensuelle).
- Filtres et recherche avancée par groupe, enseignant, salle, matière.
- Notifications par e-mail et/ou in-app lors de modification du planning.
- Export de l'emploi du temps (PDF, Excel, iCal).
- Tableau de bord statistique (taux d'occupation des salles, charge horaire, etc.).
- Historique des modifications pour traçabilité.

## 2.2 Périmètre exclu

- Application mobile native (iOS / Android) - uniquement web responsive.
- Gestion de la paie ou des ressources humaines avancées.
- Intégration avec des systèmes ERP tiers (hors du cadre du PFE).
- Module de visioconférence intégré.

# 3\. Acteurs et Cas d'Utilisation

## 3.1 Acteurs du système

| **Acteur**         | **Description**                                                        |
| ------------------ | ---------------------------------------------------------------------- |
| **Administrateur** | Gère l'ensemble des paramètres, utilisateurs et ressources du système. |
| **Enseignant**     | Consulte son planning, peut soumettre des demandes de modification.    |
| **Étudiant**       | Consulte l'emploi du temps de son groupe, reçoit les notifications.    |
| **Visiteur**       | Accède à un planning public en lecture seule (si activé).              |

## 3.2 Cas d'utilisation principaux

### 3.2.1 Administrateur

- Créer / modifier / supprimer des comptes utilisateurs.
- Configurer les années académiques, semestres, filières.
- Gérer les ressources (salles, capacités, équipements).
- Créer et valider les emplois du temps.
- Résoudre les conflits détectés par le système.
- Consulter les rapports et statistiques.

### 3.2.2 Enseignant

- Consulter son emploi du temps personnel.
- Signaler une indisponibilité ou demander un échange de créneau.
- Recevoir des notifications de modification.
- Exporter son planning au format PDF ou iCal.

### 3.2.3 Étudiant

- Consulter l'emploi du temps de son groupe / promotion.
- Filtrer par matière, enseignant ou salle.
- Recevoir des alertes en cas de modification.
- Exporter son planning.

# 4\. Exigences Fonctionnelles

## 4.1 Module d'authentification

- Inscription avec validation par e-mail.
- Connexion sécurisée (JWT ou sessions côté serveur).
- Gestion des rôles (RBAC - Role-Based Access Control).
- Réinitialisation de mot de passe par lien sécurisé.
- Protection contre les attaques par force brute (rate limiting).

## 4.2 Module de planification

- Interface calendrier glisser-déposer (drag & drop).
- Création de créneaux récurrents (hebdomadaire, bi-mensuel).
- Détection en temps réel des conflits lors de la saisie.
- Validation des créneaux avec confirmation avant enregistrement.
- Duplication d'un emploi du temps existant pour un nouveau semestre.

## 4.3 Module de notification

- Notification par e-mail lors de toute modification d'un créneau concernant l'utilisateur.
- Notification in-app avec badge et centre de notifications.
- Paramétrage des préférences de notification par utilisateur.

## 4.4 Module de rapports et exports

- Export de l'emploi du temps au format PDF (mise en page A4 hebdomadaire).
- Export au format Excel (.xlsx) pour traitement externe.
- Export au format iCal (.ics) pour intégration dans des agendas (Google, Outlook).
- Tableau de bord avec graphiques : taux d'occupation des salles, répartition horaire par matière.

# 5\. Exigences Non Fonctionnelles

| **Critère**       | **Objectif**                                         | **Méthode de vérification**             |
| ----------------- | ---------------------------------------------------- | --------------------------------------- |
| **Performance**   | Chargement < 2s (LCP)                                | Tests avec Lighthouse / WebPageTest     |
| **Disponibilité** | Uptime ≥ 99 %                                        | Monitoring (UptimeRobot)                |
| **Sécurité**      | OWASP Top 10 couvert                                 | Audit de sécurité, tests de pénétration |
| **Scalabilité**   | Support de 500 utilisateurs simultanés               | Tests de charge (JMeter / k6)           |
| **Accessibilité** | Conformité WCAG 2.1 AA                               | Audit axe / Lighthouse Accessibility    |
| **Responsive**    | Compatible mobiles & tablettes                       | Tests sur Chrome DevTools, BrowserStack |
| **Compatibilité** | Chrome, Firefox, Edge, Safari (2 dernières versions) | Tests cross-browser                     |

# 6\. Architecture Technique

## 6.1 Stack technologique proposée

| **Couche**               | **Technologie**        | **Justification**                                       |
| ------------------------ | ---------------------- | ------------------------------------------------------- |
| **Frontend**             | React.js + TypeScript  | Composants réutilisables, typage fort, large écosystème |
| **Styling**              | Tailwind CSS           | Utilitaire, responsive, personnalisable                 |
| **Calendrier UI**        | FullCalendar.js        | Bibliothèque spécialisée, drag & drop natif             |
| **Backend**              | Node.js + Express.js   | Léger, performant, JavaScript full-stack                |
| **Base de données**      | MySQL / PostgreSQL     | SGBD relationnel, requêtes complexes                    |
| **ORM**                  | Sequelize / Prisma     | Abstraction BDD, migrations, typage                     |
| **Authentification**     | JWT + Bcrypt           | Stateless, sécurisé, scalable                           |
| **Notifications e-mail** | Nodemailer + SMTP      | Envoi fiable de mails transactionnels                   |
| **Hébergement**          | VPS / Railway / Render | Déploiement simple pour PFE                             |

## 6.2 Architecture applicative

L'application suivra une architecture en couches MVC (Modèle - Vue - Contrôleur) côté serveur, couplée à une architecture de composants côté client :

- Couche Présentation : React SPA communiquant avec le backend via une API REST.
- Couche Métier : Contrôleurs Express gérant les règles de gestion (conflits, autorisations).
- Couche Données : ORM Prisma / Sequelize avec un SGBD relationnel.
- Couche Sécurité : Middleware JWT, validation des entrées (Joi / Zod), CORS configuré.

## 6.3 Modèle de données (entités principales)

- Utilisateur (id, nom, prénom, email, mot_de_passe, rôle, actif)
- Groupe (id, nom, niveau, filière, capacité)
- Salle (id, nom, capacité, équipements, disponible)
- Matière (id, nom, code, volume_horaire, coefficient)
- Enseignant (id, utilisateur_id, spécialité, disponibilités)
- Créneau (id, date, heure_début, heure_fin, matière_id, enseignant_id, salle_id, groupe_id, type, récurrent)
- Notification (id, utilisateur_id, message, lu, créé_le)

# 7\. Planification du Projet

## 7.1 Méthodologie

Le projet sera conduit selon une approche Agile / Scrum adaptée au contexte académique, avec des sprints de deux semaines et des livrables intermédiaires validés par l'encadrant.

## 7.2 Phasage et jalons

| **Phase** | **Activités principales**                                                  | **Livrables**                          | **Durée estimée** |
| --------- | -------------------------------------------------------------------------- | -------------------------------------- | ----------------- |
| **1**     | Analyse des besoins, étude de l'existant, rédaction du cahier des charges  | CDC validé, diagrammes UML             | 2 semaines        |
| **2**     | Conception BDD, maquettes UI/UX, architecture détaillée                    | Maquettes Figma, MCD/MLD               | 2 semaines        |
| **3**     | Développement backend : API REST, authentification, gestion des ressources | API fonctionnelle documentée (Swagger) | 4 semaines        |
| **4**     | Développement frontend : interfaces, calendrier, notifications             | Application web intégrée               | 4 semaines        |
| **5**     | Tests (unitaires, intégration, UI), correction des bugs                    | Rapport de tests                       | 2 semaines        |
| **6**     | Déploiement, documentation finale, rédaction du rapport PFE                | Application déployée, rapport final    | 2 semaines        |

**Durée totale estimée : 16 semaines (4 mois)**

# 8\. Description des Interfaces Principales

## 8.1 Page de connexion

- Formulaire email / mot de passe avec validation côté client.
- Lien « Mot de passe oublié ».
- Redirection automatique selon le rôle après authentification.

## 8.2 Tableau de bord Administrateur

- Statistiques clés : nombre d'enseignants, groupes, salles, conflits détectés.
- Graphiques : taux d'occupation des salles par semaine, répartition horaire par filière.
- Accès rapide aux modules de gestion.

## 8.3 Vue calendrier principal

- Vue semaine par défaut avec navigation par flèches.
- Codage couleur par matière ou par groupe.
- Clic sur un créneau pour afficher le détail (matière, enseignant, salle, groupe).
- Bouton « + » pour ajouter un créneau directement depuis le calendrier.
- Indicateur visuel rouge pour les conflits détectés.

## 8.4 Formulaire de création de créneau

- Sélecteurs pour : matière, enseignant, salle, groupe, date, heure début/fin.
- Option de récurrence (une fois, hebdomadaire, jusqu'à une date).
- Affichage en temps réel des conflits potentiels avant validation.

## 8.5 Interface Étudiant / Enseignant

- Vue filtrée automatiquement sur le groupe ou l'enseignant connecté.
- Bouton d'export (PDF, Excel, iCal) en haut à droite.
- Centre de notifications accessible depuis la barre de navigation.

# 9\. Sécurité et Conformité

## 9.1 Mesures de sécurité

- Hachage des mots de passe avec bcrypt (facteur de coût ≥ 12).
- Tokens JWT avec durée de vie limitée et mécanisme de refresh token.
- Validation stricte de toutes les entrées utilisateur (XSS, injection SQL).
- Protection CSRF (Cross-Site Request Forgery) sur les formulaires.
- Configuration HTTPS obligatoire en production.
- En-têtes de sécurité HTTP via Helmet.js (CSP, X-Frame-Options, etc.).
- Journalisation des actions sensibles (connexions, modifications de planning).

## 9.2 Gestion des données personnelles

Conformément aux bonnes pratiques et aux réglementations en vigueur (RGPD en Europe, loi 09-08 au Maroc), l'application respectera les principes suivants :

- Collecte minimale des données nécessaires au fonctionnement.
- Droit d'accès, de rectification et de suppression des données personnelles.
- Politique de confidentialité accessible depuis l'application.

# 10\. Stratégie de Tests

| **Type de test**           | **Outil(s)**         | **Objectif**                                  |
| -------------------------- | -------------------- | --------------------------------------------- |
| **Tests unitaires**        | Jest / Vitest        | Valider chaque fonction / composant isolément |
| **Tests d'intégration**    | Supertest (API REST) | Valider les interactions entre modules        |
| **Tests end-to-end**       | Cypress / Playwright | Simuler les parcours utilisateurs complets    |
| **Tests de performance**   | Lighthouse / k6      | Valider les temps de réponse et la charge     |
| **Tests de sécurité**      | OWASP ZAP            | Identifier les vulnérabilités connues         |
| **Tests de compatibilité** | BrowserStack         | Vérifier le rendu cross-browser               |

Un rapport de tests sera produit à la fin de la phase 5, documentant les cas de tests exécutés, les résultats obtenus et les défauts corrigés.

# 11\. Déploiement et Environnements

## 11.1 Environnements

- Développement : environnement local avec base de données locale.
- Test / Staging : serveur de pré-production pour la validation fonctionnelle.
- Production : hébergement cloud (VPS, Railway, Render ou équivalent).

## 11.2 Pipeline CI/CD (recommandé)

- Contrôle de version avec Git (GitHub / GitLab).
- GitHub Actions pour l'exécution automatique des tests à chaque push.
- Déploiement automatique sur la branche main validée.

## 11.3 Livrables finaux du projet

- Code source versionné sur un dépôt Git (public ou privé selon l'établissement).
- Application déployée et accessible en ligne.
- Documentation technique (README, Swagger / Postman pour l'API).
- Manuel utilisateur (guide d'utilisation par rôle).
- Rapport de projet final (mémoire PFE).
- Présentation de soutenance (PowerPoint / Canva).

# 12\. Glossaire

| **Terme**    | **Définition**                                                                                                    |
| ------------ | ----------------------------------------------------------------------------------------------------------------- |
| **API REST** | Interface de programmation utilisant le protocole HTTP pour l'échange de données au format JSON.                  |
| **CRUD**     | Create, Read, Update, Delete - opérations de base sur les données.                                                |
| **JWT**      | JSON Web Token - mécanisme de transmission sécurisée d'informations entre client et serveur.                      |
| **RBAC**     | Role-Based Access Control - contrôle d'accès basé sur les rôles des utilisateurs.                                 |
| **SPA**      | Single Page Application - application web ne rechargant qu'une seule page HTML.                                   |
| **ORM**      | Object-Relational Mapping - outil faisant le lien entre objets du code et tables de la BDD.                       |
| **WCAG**     | Web Content Accessibility Guidelines - standards d'accessibilité du W3C.                                          |
| **CI/CD**    | Continuous Integration / Continuous Deployment - automatisation des tests et du déploiement.                      |
| **Créneau**  | Unité de planification représentant un cours ou événement défini par une heure, une date, une salle et un groupe. |
| **Conflit**  | Situation où deux créneaux utilisent la même ressource (salle, enseignant) au même moment.                        |

**Fin du Cahier des Charges**