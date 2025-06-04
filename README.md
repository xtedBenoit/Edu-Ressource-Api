🌟 API Ressources Educatives - Version 1.0
Bienvenue sur l’API Ressources Educatives, une solution moderne, sécurisée et intuitive pour gérer, partager et enrichir des contenus pédagogiques !
Cette API est conçue pour accompagner les enseignants, étudiants et passionnés d’éducation à travers une plateforme fiable, performante et pensée pour l’avenir.

🚀 Pourquoi utiliser cette API ?
Sécurité avancée : authentification via JWT pour protéger vos données avec des messages clairs et personnalisés en cas d’erreur.

Simplicité d’intégration : routes RESTful bien organisées avec un découpage logique par ressources.

Flexibilité : accès public pour consulter les ressources et accès authentifié pour créer, modifier et gérer vos contenus.

Richesse fonctionnelle : gestion des utilisateurs, ressources, likes, messages et bien plus encore !

Évolutive et maintenable : architecture propre et conforme aux meilleures pratiques Laravel 12.

🎯 Fonctionnalités clés
Inscription, connexion, récupération et réinitialisation de mot de passe.

Gestion complète du profil utilisateur (modification, avatar, historique d’actions).

Publication, consultation, téléchargement et suppression des ressources.

Système de likes simple et efficace.

Messagerie pour échanger sur les ressources.

Gestion des matières, séries et classes liées aux ressources éducatives.

Contrôle des quotas d’utilisation et parrainage pour une communauté grandissante.

🔥 Quick Start
1. Installation
git clone https://the-repo.git
cd ton-projet-api
composer install
php artisan migrate --seed
2. Configuration
Configure .env avec ta base de données, clés JWT et autres paramètres.

Génère la clé JWT :
php artisan jwt:secret
3. Lancement
php artisan serve
4. Utilisation
Utilise les routes api/v1/auth pour t’inscrire, te connecter et gérer ton compte.

Consulte les ressources publiques avec GET /api/v1/ressources.

Pour toutes actions protégées, utilise le header Authorization: Bearer {token}.

📚 Routes principales
Auth & Utilisateur :
/api/v1/auth/register - Inscription
/api/v1/auth/login - Connexion
/api/v1/me - Profil et actions utilisateur

Ressources :
/api/v1/ressources - Liste publique
/api/v1/ressources/{id} - Détails
/api/v1/ressources (POST) - Création (auth)
/api/v1/ressources/{id}/download - Téléchargement (auth)

Matières, séries, classes (GET & CRUD protégés)
/api/v1/subjects, /api/v1/series, /api/v1/classes

Interactions : likes, messages, quotas, parrainage.

🔐 Sécurité
JWT pour un accès sécurisé et sans état (stateless).

Gestion personnalisée des erreurs d’authentification pour une meilleure expérience.

Middleware auth:api protégeant les routes sensibles.

🤝 Contribuer
Tu souhaites améliorer cette API ou proposer de nouvelles fonctionnalités ?
Les contributions sont les bienvenues !
Merci de forker le projet, créer une branche dédiée et soumettre une Pull Request claire.

📞 Contact
Pour toute question, suggestion ou rapport de bug, n’hésite pas à me contacter via [ton email] ou ouvrir une issue.

🎉 Merci !
Merci d’utiliser l’API Ressources Educatives. Ensemble, rendons l’éducation plus accessible, interactive et joyeuse ! 🌈