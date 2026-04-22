<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="/medias/favicon.png">
<link rel="icon" type="image/png" href="/medias/favicon.png">
  <title>SURVIVOR</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f8f8;
      color: #333;
    }

    header {
      background-color: #1a1a1a;
      color: white;
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    header h1 {
      margin: 0;
      font-size: 1.4rem;
    }

    header h1 a {
      text-decoration: none;
      color: white;
    }

    nav {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
    }

    nav a {
      color: white;
      text-decoration: none;
      font-size: 1rem;
      padding: 6px 10px;
      border-radius: 5px;
      transition: background 0.2s;
    }

    nav a:hover {
      background-color: #333;
    }

    main {
      padding: 20px;
      max-width: 1200px;
      margin: auto;
    }

    @media (max-width: 768px) {
      header {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }

      nav {
        justify-content: center;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1><a href="index.php">SURVIVOR</a> <a href="admin/index.php">.</a></h1>
    <nav>
      <a href="index.php">Accueil</a>
      <a href="propos.php">A Propos</a>
     
      <?php if (isset($_SESSION['user'])): ?>
        <a href="profil.php">Profil</a>
        <a href="deconnexion.php">Déconnexion</a>
      <?php else: ?>
        <a href="connexion.php">Connexion</a>
        <a href="inscription.php">Inscription</a>
      <?php endif; ?>
    </nav>
  </header>
  <main>
