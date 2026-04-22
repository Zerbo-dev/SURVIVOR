<?php
session_start();

// Protection admin — adaptez selon votre logique (email ou rôle)
$admin_emails = ['yanniszerbo@gmail.com']; // à modifier
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['email'], $admin_emails)) {
    header('Location: index.php');
    exit();
}

$modules = file_exists("data/modules.json") ? json_decode(file_get_contents("data/modules.json"), true) : [];

// Action (valider / rejeter)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $module_id  = (int)($_POST['module_id'] ?? 0);
    $ressource_id = $_POST['ressource_id'] ?? '';
    $action       = $_POST['action'] ?? '';

    if (in_array($action, ['valider', 'rejeter']) && $module_id) {
        $fichier = "data/modules/module_{$module_id}.json";
        $data = file_exists($fichier) ? json_decode(file_get_contents($fichier), true) : ['ressources'=>[],'posts'=>[]];

        foreach ($data['ressources'] as &$r) {
            if ((string)$r['id'] === (string)$ressource_id) {
                if ($action === 'valider') {
                    $r['statut'] = 'valide';
                    $r['date_validation'] = date('Y-m-d H:i:s');
                } else {
                    $r['statut'] = 'rejete';
                }
                break;
            }
        }
        file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header("Location: admin.php?module_id={$module_id}&action={$action}");
        exit();
    }
}

// Charger toutes les propositions en attente par module
$filtre_module = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;
$statut_filtre = $_GET['statut'] ?? 'en_attente';

$toutes_ressources = [];
foreach ($modules as $m) {
    $fichier = "data/modules/module_{$m['id']}.json";
    $data = file_exists($fichier) ? json_decode(file_get_contents($fichier), true) : ['ressources'=>[],'posts'=>[]];
    foreach ($data['ressources'] as $r) {
        if ($filtre_module && $m['id'] !== $filtre_module) continue;
        if ($r['statut'] !== $statut_filtre) continue;
        $toutes_ressources[] = array_merge($r, ['module_id' => $m['id'], 'module_titre' => $m['titre']]);
    }
}

$cats = [
    'cours'=>'📘 Cours','td'=>'📝 TD','devoirs'=>'📋 Devoirs',
    'tp'=>'🔬 TP','conseils'=>'💡 Conseils','annexes'=>'📎 Annexes',
];
$flash = $_GET['action'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin — Validation des ressources</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root{--blanc:#fff;--bleu:#1a4fd6;--bleu-clair:#e8effe;--orange:#f76c1b;--gris:#f4f5f7;--gris-bord:#dde1ea;--texte:#111827;--texte-sec:#6b7280;--radius:12px;--vert:#059669;--rouge:#dc2626;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'DM Sans',sans-serif;background:var(--gris);color:var(--texte);min-height:100vh;}
.navbar{background:var(--blanc);border-bottom:2px solid var(--bleu-clair);padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:60px;}
.brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.3rem;color:var(--bleu);text-decoration:none;}
.brand span{color:var(--orange);}
.brand em{font-style:normal;font-size:.75rem;background:#fee2e2;color:var(--rouge);padding:.15rem .5rem;border-radius:6px;margin-left:.5rem;font-weight:700;}
.container{max-width:1000px;margin:2rem auto;padding:0 1.5rem;}
h1{font-family:'Syne',sans-serif;font-weight:800;font-size:1.6rem;margin-bottom:1.5rem;}
.filters{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center;}
.filters select,.filters a.filter-btn{padding:.5rem 1rem;border-radius:8px;border:1.5px solid var(--gris-bord);background:var(--blanc);font-size:.88rem;cursor:pointer;text-decoration:none;color:var(--texte);}
.filter-btn.active{background:var(--bleu);color:#fff;border-color:var(--bleu);}
.count-badge{background:var(--orange);color:#fff;font-size:.78rem;font-weight:700;padding:.15rem .5rem;border-radius:10px;margin-left:.4rem;}
.flash{padding:.7rem 1rem;border-radius:8px;margin-bottom:1rem;font-weight:600;font-size:.9rem;}
.flash.valider{background:#d1fae5;color:var(--vert);}
.flash.rejeter{background:#fee2e2;color:var(--rouge);}
.ressource-card{background:var(--blanc);border-radius:var(--radius);box-shadow:0 2px 10px rgba(26,79,214,.08);margin-bottom:1rem;overflow:hidden;border-left:4px solid var(--bleu-clair);}
.ressource-card.valide{border-left-color:var(--vert);}
.ressource-card.rejete{border-left-color:var(--rouge);}
.card-inner{padding:1.2rem 1.5rem;}
.card-top{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
.card-title{font-family:'Syne',sans-serif;font-weight:700;font-size:1rem;color:var(--texte);}
.card-meta{font-size:.82rem;color:var(--texte-sec);margin-top:.3rem;}
.card-meta span{margin-right:.8rem;}
.badge{display:inline-block;font-size:.72rem;font-weight:700;padding:.2rem .55rem;border-radius:6px;text-transform:uppercase;}
.badge-cat{background:var(--bleu-clair);color:var(--bleu);}
.badge-module{background:#fef3c7;color:#d97706;}
.badge-valide{background:#d1fae5;color:var(--vert);}
.badge-rejete{background:#fee2e2;color:var(--rouge);}
.badge-attente{background:#fff3ec;color:var(--orange);}
.card-lien{margin-top:.6rem;}
.card-lien a{color:var(--bleu);font-size:.88rem;word-break:break-all;}
.card-desc{margin-top:.5rem;font-size:.86rem;color:var(--texte-sec);font-style:italic;}
.card-actions{display:flex;gap:.6rem;margin-top:1rem;}
.btn{border:none;padding:.45rem 1.1rem;border-radius:7px;font-family:'DM Sans',sans-serif;font-weight:700;font-size:.84rem;cursor:pointer;transition:all .2s;}
.btn-valider{background:var(--vert);color:#fff;}
.btn-valider:hover{background:#047857;}
.btn-rejeter{background:#fee2e2;color:var(--rouge);border:1px solid #fca5a5;}
.btn-rejeter:hover{background:#fecaca;}
.empty{text-align:center;padding:3rem 1rem;color:var(--texte-sec);}
.empty .ico{font-size:3rem;display:block;margin-bottom:.7rem;}
.stat-bar{display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap;}
.stat-item{background:var(--blanc);border-radius:var(--radius);padding:.8rem 1.2rem;box-shadow:0 2px 8px rgba(26,79,214,.07);flex:1;min-width:130px;text-align:center;}
.stat-num{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:var(--bleu);}
.stat-label{font-size:.78rem;color:var(--texte-sec);margin-top:.15rem;}
</style>
</head>
<body>
<nav class="navbar">
  <a href="index.php" class="brand">SURVI<span>VOR</span> <em>ADMIN</em></a>
  <a href="<?php echo isset($_SESSION['user']) ? 'deconnexion.php' : 'connexion.php'; ?>" style="font-size:.88rem;color:var(--texte-sec);text-decoration:none;">Déconnexion</a>
</nav>

<div class="container">
  <h1>🛡️ Validation des ressources</h1>

  <?php if ($flash === 'valider'): ?>
    <div class="flash valider">✓ Ressource validée et publiée avec succès !</div>
  <?php elseif ($flash === 'rejeter'): ?>
    <div class="flash rejeter">✗ Ressource rejetée.</div>
  <?php endif; ?>

  <?php
  // Stats globales
  $nb_attente = 0; $nb_valide = 0; $nb_rejete = 0;
  foreach ($modules as $m) {
    $f = "data/modules/module_{$m['id']}.json";
    $d = file_exists($f) ? json_decode(file_get_contents($f), true) : ['ressources'=>[]];
    foreach ($d['ressources'] as $r) {
      if ($r['statut'] === 'en_attente') $nb_attente++;
      elseif ($r['statut'] === 'valide') $nb_valide++;
      else $nb_rejete++;
    }
  }
  ?>
  <div class="stat-bar">
    <div class="stat-item"><div class="stat-num" style="color:var(--orange)"><?= $nb_attente ?></div><div class="stat-label">En attente</div></div>
    <div class="stat-item"><div class="stat-num" style="color:var(--vert)"><?= $nb_valide ?></div><div class="stat-label">Validées</div></div>
    <div class="stat-item"><div class="stat-num" style="color:var(--rouge)"><?= $nb_rejete ?></div><div class="stat-label">Rejetées</div></div>
  </div>

  <div class="filters">
    <select onchange="window.location='admin.php?module_id='+this.value+'&statut=<?= $statut_filtre ?>'">
      <option value="0" <?= !$filtre_module ? 'selected' : '' ?>>Tous les modules</option>
      <?php foreach ($modules as $m): ?>
        <option value="<?= $m['id'] ?>" <?= $filtre_module === $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['titre']) ?></option>
      <?php endforeach; ?>
    </select>
    <a href="?module_id=<?= $filtre_module ?>&statut=en_attente" class="filter-btn <?= $statut_filtre === 'en_attente' ? 'active' : '' ?>">
      En attente <span class="count-badge"><?= $nb_attente ?></span>
    </a>
    <a href="?module_id=<?= $filtre_module ?>&statut=valide" class="filter-btn <?= $statut_filtre === 'valide' ? 'active' : '' ?>">Validées</a>
    <a href="?module_id=<?= $filtre_module ?>&statut=rejete" class="filter-btn <?= $statut_filtre === 'rejete' ? 'active' : '' ?>">Rejetées</a>
  </div>

  <?php if (empty($toutes_ressources)): ?>
    <div class="empty">
      <span class="ico">🎉</span>
      <?= $statut_filtre === 'en_attente' ? 'Aucune proposition en attente.' : 'Aucune ressource dans cette catégorie.' ?>
    </div>
  <?php else: foreach ($toutes_ressources as $r): ?>
    <div class="ressource-card <?= $r['statut'] ?>">
      <div class="card-inner">
        <div class="card-top">
          <div>
            <div class="card-title"><?= htmlspecialchars($r['titre']) ?></div>
            <div class="card-meta">
              <span>👤 <?= htmlspecialchars($r['auteur']) ?></span>
              <span>📅 <?= date('d/m/Y H:i', strtotime($r['date'])) ?></span>
              <span class="badge badge-module"><?= htmlspecialchars($r['module_titre']) ?></span>
              <span class="badge badge-cat"><?= $cats[$r['categorie']] ?? $r['categorie'] ?></span>
              <?php if ($r['statut'] === 'valide'): ?>
                <span class="badge badge-valide">✓ Validé</span>
              <?php elseif ($r['statut'] === 'rejete'): ?>
                <span class="badge badge-rejete">✗ Rejeté</span>
              <?php else: ?>
                <span class="badge badge-attente">⏳ En attente</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="card-lien">🔗 <a href="<?= htmlspecialchars($r['lien']) ?>" target="_blank"><?= htmlspecialchars($r['lien']) ?></a></div>
        <?php if (!empty($r['description'])): ?>
          <div class="card-desc"><?= htmlspecialchars($r['description']) ?></div>
        <?php endif; ?>
        <?php if ($r['statut'] === 'en_attente'): ?>
          <div class="card-actions">
            <form method="post" style="display:inline">
              <input type="hidden" name="module_id" value="<?= $r['module_id'] ?>">
              <input type="hidden" name="ressource_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="action" value="valider">
              <button type="submit" class="btn btn-valider">✓ Valider</button>
            </form>
            <form method="post" style="display:inline">
              <input type="hidden" name="module_id" value="<?= $r['module_id'] ?>">
              <input type="hidden" name="ressource_id" value="<?= $r['id'] ?>">
              <input type="hidden" name="action" value="rejeter">
              <button type="submit" class="btn btn-rejeter">✗ Rejeter</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>
</body>
</html>
