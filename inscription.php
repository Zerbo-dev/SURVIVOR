<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

function redirect_safe($url, $fallback = 'index.php') {
    if (empty($url) || preg_match('#^(https?:)?//#i', $url)) {
        return $fallback;
    }
    return $url;
}

$redirect = '';
if (!empty($_GET['redirect']))  $redirect = $_GET['redirect'];
if (!empty($_POST['redirect'])) $redirect = $_POST['redirect'];
$redirect = redirect_safe($redirect);

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email       = trim($_POST['email'] ?? '');
    $nom         = trim($_POST['nom'] ?? '');
    $mdp         = $_POST['mdp'] ?? '';
    $mdp_confirm = $_POST['mdp_confirm'] ?? '';

    if (!$email || !$nom || !$mdp || !$mdp_confirm) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Adresse email invalide.";
    } elseif (strlen($mdp) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($mdp !== $mdp_confirm) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } else {
        $fichier = 'data/users.json';
        $users   = file_exists($fichier) ? json_decode(file_get_contents($fichier), true) : [];

        foreach ($users as $u) {
            if ($u['email'] === $email) {
                $erreur = "Cet email est déjà utilisé.";
                break;
            }
        }

        if (!$erreur) {
            $id   = $users ? max(array_column($users, 'id')) + 1 : 1;
            $hash = password_hash($mdp, PASSWORD_DEFAULT);

            $users[] = [
                'id'               => $id,
                'email'            => $email,
                'nom'              => $nom,
                'mot_de_passe'     => $hash,
                'date_inscription' => date('Y-m-d H:i:s'),
            ];
            file_put_contents($fichier, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $_SESSION['user'] = [
                'id'    => $id,
                'email' => $email,
                'nom'   => $nom,
            ];

            header('Location: ' . (!empty($redirect) ? $redirect : 'index.php'));
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription — SURVIVOR</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --blanc:       #ffffff;
  --bleu:        #1a4fd6;
  --bleu-clair:  #e8effe;
  --bleu-mid:    #4169e8;
  --orange:      #f76c1b;
  --orange-clair:#fff3ec;
  --gris:        #f4f5f7;
  --gris-bord:   #dde1ea;
  --texte:       #111827;
  --texte-sec:   #6b7280;
  --radius:      14px;
  --shadow:      0 4px 24px rgba(26,79,214,.13);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--gris);
  color: var(--texte);
  min-height: 100vh;
  display: grid;
  grid-template-rows: 60px 1fr;
}

.navbar {
  background: var(--blanc);
  border-bottom: 2px solid var(--bleu-clair);
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 2px 8px rgba(26,79,214,.07);
}
.navbar-brand {
  font-family: 'Syne', sans-serif;
  font-weight: 800;
  font-size: 1.4rem;
  color: var(--bleu);
  text-decoration: none;
}
.navbar-brand span { color: var(--orange); }
.navbar-back {
  font-size: .88rem;
  color: var(--texte-sec);
  text-decoration: none;
  font-weight: 500;
  transition: color .2s;
}
.navbar-back:hover { color: var(--bleu); }

.page {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1.2rem;
}

.card {
  background: var(--blanc);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  width: 100%;
  max-width: 440px;
  overflow: hidden;
}

/* Header orange pour différencier de la connexion */
.card-top {
  background: linear-gradient(135deg, var(--orange) 0%, #e85e10 100%);
  padding: 2rem 2rem 1.6rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.card-top::before {
  content: '';
  position: absolute;
  top: -50px; right: -50px;
  width: 180px; height: 180px;
  background: rgba(255,255,255,.07);
  border-radius: 50%;
}
.card-top::after {
  content: '';
  position: absolute;
  bottom: -40px; left: -30px;
  width: 130px; height: 130px;
  background: rgba(26,79,214,.15);
  border-radius: 50%;
}
.card-icon {
  width: 60px; height: 60px;
  background: rgba(255,255,255,.18);
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.8rem;
  margin: 0 auto 1rem;
  border: 1px solid rgba(255,255,255,.25);
  position: relative; z-index: 1;
}
.card-top h1 {
  font-family: 'Syne', sans-serif;
  font-size: 1.5rem;
  font-weight: 800;
  color: #fff;
  position: relative; z-index: 1;
}
.card-top p {
  font-size: .88rem;
  color: rgba(255,255,255,.75);
  margin-top: .4rem;
  position: relative; z-index: 1;
}

.card-body { padding: 1.8rem 2rem 2rem; }

.alert-erreur {
  background: #fee2e2;
  color: #dc2626;
  border-left: 3px solid #dc2626;
  border-radius: 8px;
  padding: .7rem 1rem;
  font-size: .88rem;
  font-weight: 500;
  margin-bottom: 1.2rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: .8rem;
}
@media (max-width: 420px) { .form-row { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 1rem; }
.form-group label {
  display: block;
  font-size: .85rem;
  font-weight: 600;
  color: var(--texte);
  margin-bottom: .4rem;
}
.form-group input {
  width: 100%;
  padding: .7rem 1rem;
  border: 1.5px solid var(--gris-bord);
  border-radius: 9px;
  font-family: 'DM Sans', sans-serif;
  font-size: .95rem;
  color: var(--texte);
  background: var(--gris);
  transition: border-color .2s, background .2s, box-shadow .2s;
}
.form-group input:focus {
  outline: none;
  border-color: var(--orange);
  background: var(--blanc);
  box-shadow: 0 0 0 3px rgba(247,108,27,.12);
}

/* Indicateur force mot de passe */
.mdp-hint {
  font-size: .75rem;
  color: var(--texte-sec);
  margin-top: .3rem;
}

.btn-submit {
  width: 100%;
  background: var(--orange);
  color: #fff;
  border: none;
  padding: .8rem;
  border-radius: 9px;
  font-family: 'DM Sans', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  margin-top: .4rem;
  transition: background .2s, transform .15s, box-shadow .2s;
  box-shadow: 0 3px 12px rgba(247,108,27,.35);
}
.btn-submit:hover {
  background: #d95e10;
  transform: translateY(-1px);
  box-shadow: 0 5px 16px rgba(247,108,27,.4);
}

.divider {
  display: flex;
  align-items: center;
  gap: .8rem;
  margin: 1.4rem 0;
  color: var(--texte-sec);
  font-size: .82rem;
}
.divider::before, .divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--gris-bord);
}

.btn-secondaire {
  width: 100%;
  background: var(--bleu-clair);
  color: var(--bleu);
  border: 1.5px solid var(--bleu);
  padding: .72rem;
  border-radius: 9px;
  font-family: 'DM Sans', sans-serif;
  font-weight: 700;
  font-size: .92rem;
  cursor: pointer;
  text-decoration: none;
  display: block;
  text-align: center;
  transition: background .2s, color .2s, transform .15s;
}
.btn-secondaire:hover {
  background: var(--bleu);
  color: #fff;
  transform: translateY(-1px);
}
</style>
</head>
<body>

<nav class="navbar">
  <a href="index.php" class="navbar-brand">SURVI<span>VOR</span></a>
  <a href="<?= htmlspecialchars($redirect ?: 'index.php') ?>" class="navbar-back">← Retour</a>
</nav>

<div class="page">
  <div class="card">
    <div class="card-top">
      <div class="card-icon">🎓</div>
      <h1>Inscription</h1>
      <p>Rejoins la communauté SURVIVOR</p>
    </div>
    <div class="card-body">
      <?php if ($erreur): ?>
        <div class="alert-erreur">⚠️ <?= htmlspecialchars($erreur) ?></div>
      <?php endif; ?>

      <form method="post" novalidate>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <div class="form-group">
          <label for="nom">Nom complet</label>
          <input type="text" id="nom" name="nom" required
                 placeholder="Ton nom"
                 value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="email">Adresse email</label>
          <input type="email" id="email" name="email" required
                 placeholder="ton@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="mdp">Mot de passe</label>
            <input type="password" id="mdp" name="mdp" required placeholder="••••••••">
            <div class="mdp-hint">6 caractères minimum</div>
          </div>
          <div class="form-group">
            <label for="mdp_confirm">Confirmer</label>
            <input type="password" id="mdp_confirm" name="mdp_confirm" required placeholder="••••••••">
          </div>
        </div>

        <button type="submit" class="btn-submit">Créer mon compte →</button>
      </form>

      <div class="divider">ou</div>

      <a href="connexion.php<?= $redirect ? '?redirect='.urlencode($redirect) : '' ?>"
         class="btn-secondaire">
        Déjà un compte ? Se connecter
      </a>
    </div>
  </div>
</div>

</body>
</html>
