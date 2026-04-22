<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: connexion.php');
    exit();
}

$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;
$modules   = file_exists("data/modules.json") ? json_decode(file_get_contents("data/modules.json"), true) : [];
$module    = null;
foreach ($modules as $m) { if ($m['id'] === $module_id) { $module = $m; break; } }
if (!$module) { header('Location: index.php'); exit(); }

$categories = [
    'cours'    => '📘 Cours',
    'td'       => '📝 TD',
    'devoirs'  => '📋 Devoirs',
    'tp'       => '🔬 TP',
    'conseils' => '💡 Conseils',
    'annexes'  => '📎 Ressources annexes',
];

$erreur = ''; $succes = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre     = trim($_POST['titre'] ?? '');
    $lien      = trim($_POST['lien'] ?? '');
    $categorie = $_POST['categorie'] ?? '';
    $type      = $_POST['type'] ?? '';
    $desc      = trim($_POST['description'] ?? '');

    if (!$titre || !$lien || !$categorie) {
        $erreur = "Titre, lien et catégorie sont obligatoires.";
    } elseif (!filter_var($lien, FILTER_VALIDATE_URL)) {
        $erreur = "Le lien fourni n'est pas une URL valide.";
    } elseif (!array_key_exists($categorie, $categories)) {
        $erreur = "Catégorie invalide.";
    } else {
        $fichier = "data/modules/module_{$module_id}.json";
        $data = file_exists($fichier) ? json_decode(file_get_contents($fichier), true) : ['ressources' => [], 'posts' => []];

        $data['ressources'][] = [
            'id'          => time() . rand(100,999),
            'titre'       => htmlspecialchars($titre),
            'lien'        => $lien,
            'categorie'   => $categorie,
            'type'        => htmlspecialchars($type),
            'description' => htmlspecialchars($desc),
            'auteur'      => $_SESSION['user']['nom'],
            'auteur_id'   => $_SESSION['user']['id'],
            'date'        => date('Y-m-d H:i:s'),
            'statut'      => 'en_attente',
        ];
        file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $succes = "Votre proposition a été soumise ! Elle sera visible après validation par l'administrateur.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Proposer une ressource — SURVIVOR</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --blanc:#fff; --bleu:#1a4fd6; --bleu-clair:#e8effe;
  --orange:#f76c1b; --gris:#f4f5f7; --gris-bord:#dde1ea;
  --texte:#111827; --texte-sec:#6b7280; --radius:12px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;background:var(--gris);color:var(--texte);min-height:100vh;}
.navbar{background:var(--blanc);border-bottom:2px solid var(--bleu-clair);padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100;}
.navbar-brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.4rem;color:var(--bleu);text-decoration:none;}
.navbar-brand span{color:var(--orange);}
.breadcrumb{max-width:700px;margin:1.2rem auto .5rem;padding:0 1.5rem;font-size:.85rem;color:var(--texte-sec);}
.breadcrumb a{color:var(--bleu);text-decoration:none;}
.form-card{max-width:700px;margin:0 auto 3rem;padding:0 1.5rem;}
.card{background:var(--blanc);border-radius:var(--radius);box-shadow:0 2px 12px rgba(26,79,214,.10);overflow:hidden;}
.card-header{padding:1.5rem 2rem;border-bottom:1px solid var(--gris-bord);background:linear-gradient(135deg,var(--bleu),#4169e8);}
.card-header h1{font-family:'Syne',sans-serif;font-weight:800;font-size:1.4rem;color:#fff;}
.card-header p{font-size:.88rem;color:rgba(255,255,255,.75);margin-top:.3rem;}
.card-body{padding:1.8rem 2rem;}
.form-group{margin-bottom:1.2rem;}
.form-group label{display:block;font-weight:600;font-size:.88rem;color:var(--texte);margin-bottom:.4rem;}
.form-group input,.form-group select,.form-group textarea{
  width:100%;border:1.5px solid var(--gris-bord);border-radius:8px;
  padding:.65rem 1rem;font-family:'DM Sans',sans-serif;font-size:.92rem;
  color:var(--texte);background:var(--blanc);transition:border-color .2s;
}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--bleu);}
.form-group textarea{resize:vertical;min-height:80px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:520px){.form-row{grid-template-columns:1fr;}}
.btn-submit{background:var(--orange);color:#fff;border:none;padding:.7rem 2rem;border-radius:8px;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;width:100%;transition:background .2s;}
.btn-submit:hover{background:#d95e10;}
.alert{padding:.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:.9rem;font-weight:500;}
.alert-succes{background:#d1fae5;color:#059669;border-left:3px solid #059669;}
.alert-erreur{background:#fee2e2;color:#dc2626;border-left:3px solid #dc2626;}
.hint{font-size:.78rem;color:var(--texte-sec);margin-top:.3rem;}
.module-badge{display:inline-block;background:var(--bleu-clair);color:var(--bleu);font-size:.8rem;font-weight:700;padding:.3rem .8rem;border-radius:20px;margin-bottom:1.2rem;}
.retour{display:inline-flex;align-items:center;gap:.4rem;color:var(--bleu);text-decoration:none;font-size:.88rem;font-weight:600;margin-bottom:1rem;}
.retour:hover{text-decoration:underline;}
</style>
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="navbar-brand">SURVI<span>VOR</span></a>
</nav>
<div class="breadcrumb">
  <a href="index.php">Accueil</a> › <a href="module.php?id=<?= $module_id ?>">
  <?= htmlspecialchars($module['titre']) ?></a> › Proposer une ressource
</div>
<div class="form-card">
  <a href="module.php?id=<?= $module_id ?>" class="retour">← Retour au module</a>
  <div class="card">
    <div class="card-header">
      <h1>✚ Proposer une ressource</h1>
      <p>Votre proposition sera examinée par l'administrateur avant publication.</p>
    </div>
    <div class="card-body">
      <span class="module-badge">📚 <?= htmlspecialchars($module['titre']) ?></span>
      <?php if ($erreur): ?><div class="alert alert-erreur"><?= $erreur ?></div><?php endif; ?>
      <?php if ($succes): ?><div class="alert alert-succes">✓ <?= $succes ?></div><?php endif; ?>
      <?php if (!$succes): ?>
      <form method="post" novalidate>
        <div class="form-group">
          <label for="titre">Titre de la ressource *</label>
          <input type="text" id="titre" name="titre" placeholder="Ex: Cours Algèbre 1 — Chapitre 3" required
                 value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="categorie">Catégorie *</label>
            <select id="categorie" name="categorie" required>
              <option value="">— Choisir —</option>
              <?php foreach ($categories as $k => $label): ?>
                <option value="<?= $k ?>" <?= (($_POST['categorie'] ?? '') === $k) ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="type">Type de fichier</label>
            <select id="type" name="type">
              <option value="">— Optionnel —</option>
              <option value="PDF">PDF</option>
              <option value="ZIP">ZIP</option>
              <option value="video">Vidéo YouTube</option>
              <option value="ipynb">.ipynb (Notebook)</option>
              <option value="py">.py (Python)</option>
              <option value="autre">Autre</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="lien">Lien (URL) *</label>
          <input type="url" id="lien" name="lien" placeholder="https://..." required
                 value="<?= htmlspecialchars($_POST['lien'] ?? '') ?>">
          <p class="hint">🔗 Lien Google Drive, YouTube, GitHub, Dropbox, etc.</p>
        </div>
        <div class="form-group">
          <label for="description">Description (optionnel)</label>
          <textarea id="description" name="description" placeholder="Décrivez brièvement ce que contient cette ressource..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn-submit">🚀 Soumettre la proposition</button>
      </form>
      <?php else: ?>
        <a href="module.php?id=<?= $module_id ?>" class="btn-submit" style="display:block;text-align:center;text-decoration:none;margin-top:1rem;">← Retourner au module</a>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
