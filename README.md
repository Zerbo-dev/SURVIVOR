#  Survivor — Plateforme de survie pour les 1ères années

Survivor est une plateforme collaborative conçue pour aider les étudiants de première année à réussir dans les modules les plus difficiles.

Le site centralise :
- 📚 Les ressources de cours (PDF, TD, TP, devoirs)
- 🧠 Des conseils pratiques et retours d’expérience
- 🧵 Un forum par module appelé **Le Donjon du Module**

---

## 🚀 Objectif

Faciliter la réussite des étudiants en leur donnant :
- un accès rapide aux ressources
- un espace d’entraide structuré
- une vision claire des attentes par module

---

## 🧱 Architecture du site

### 🏠 Accueil
- Présentation du projet
- Modules populaires
- Derniers posts du forum
- Astuce du jour

### 📦 Modules
- Liste complète des modules
- Filtrage par semestre / UE
- Accès à chaque module

### 📘 Page Module
Chaque module contient :

- **Cours** → PDF uniquement  
- **TD** → PDF uniquement  
- **Devoirs** → PDF uniquement  
- **TP** → tous formats (PDF, ZIP, notebooks, vidéos…)  
- **Conseils** → astuces + méthodes  
- **Ressources annexes** → annales, corrigés, liens externes  
- **🗡️ Le Donjon du Module** → forum dédié  

---


### ℹ️ À propos
- Objectif du projet
- Équipe
- Contact
- Disclaimer

---

## ⚡ Fonctionnalités principales

- 🔐 Authentification 
- 📂 Upload de ressources par module
- 💬 Forum en temps réel
- 🏷️ Tag des posts par module
- 🔎 Recherche dans les discussions


---

## 📁 Structure du projet (exemple Next.js)



│   admin.php
│   connexion.php
│   deconnexion.php
│   index.php
│   inscription.php
│   module.php
│   Nouvelle image bitmap.bmp
│   proposer_ressource.php
│   README.md
│
├───assets
│       .gitignore
│       img1.jpg
│
├───data
│   │   modules.json
│   │   users.json
│   │
│   └───modules
│           module_1.json
│           module_2.json
│           module_3.json
│
└───includes
    │   footer.php
    │   header.php
    │
    └───compte
            header.php




