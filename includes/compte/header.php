<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="icon" type="image/png" href="/medias/favicon.png">
<link rel="icon" type="image/png" href="/medias/favicon.png">
<title>SURVIVOR</title>
<style>
    body { font-family: Arial, sans-serif; background:#f9f9f9; margin:0; padding:0; }
    header {
        background: #007BFF; color: white; padding: 15px 20px;
        display: flex; justify-content: space-between; align-items: center;
    }
    header a { color: white; text-decoration: none; font-weight: bold; }
    nav a {
        margin-left: 15px; color: white; text-decoration: none;
        font-weight: normal;
        transition: opacity 0.2s;
    }
    nav a:hover { opacity: 0.8; }
    main { padding: 30px 15px; max-width: 450px; margin: auto; background: white; margin-top: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
    .error { color: #cc0000; margin-bottom: 15px; text-align: center; }
    form label { display: block; margin: 10px 0 6px; font-weight: bold; color: #444; }
    form input[type="email"], form input[type="text"], form input[type="password"] {
        width: 100%; padding: 10px; font-size: 1rem; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;
        transition: border-color 0.3s;
    }
    form input[type="email"]:focus, form input[type="text"]:focus, form input[type="password"]:focus {
        border-color: #0056b3; outline: none;
    }
    form button {
        margin-top: 20px; width: 100%; padding: 12px; font-size: 1.1rem;
        background-color: #007BFF; border: none; color: white; border-radius: 5px; cursor: pointer;
        transition: background-color 0.3s;
    }
    form button:hover { background-color: #0056b3; }
    .t{
         margin-top: 20px; width: 100%; padding: 12px; font-size: 1.1rem;
        background-color: #007BFF; border: none; color: white; border-radius: 5px; cursor: pointer;
        transition: background-color 0.3s;
    }
    .t:hover { background-color: #0056b3; }
    @media (max-width: 480px) {
        main { margin-top: 15px; padding: 20px; }
        header { flex-wrap: wrap; justify-content: center; gap: 10px; }
    }
    
    
</style>
</head>
<body>
<header>
    <a href="index.php" style="font-size: 1.4rem;">SURVIVOR</a>
    <nav>
        <?php if (isset($_SESSION['user'])): ?>
            <span>Bonjour, <?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
            <a href="profil.php">Profil</a>
            <a href="deconnexion.php">Déconnexion</a>
        <?php else: ?>
            <a href="inscription.php">Inscription</a>
            <a href="connexion.php">Connexion</a>
        <?php endif; ?>
    </nav>
</header>
<main>

