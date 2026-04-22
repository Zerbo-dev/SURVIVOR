<?php
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Charger les modules
$modules = file_exists("data/modules.json") ? json_decode(file_get_contents("data/modules.json"), true) : [];
$module = null;
foreach ($modules as $m) {
    if ($m['id'] === $id) { $module = $m; break; }
}
if (!$module) { header('Location: index.php'); exit(); }

// Charger les ressources du module
$fichier_ressources = "data/modules/module_{$id}.json";
$data_module = file_exists($fichier_ressources)
    ? json_decode(file_get_contents($fichier_ressources), true)
    : ['ressources' => [], 'posts' => []];

$ressources = $data_module['ressources'] ?? [];
$posts      = $data_module['posts'] ?? [];

// Catégories de ressources
$categories_ressources = [
    'cours'     => ['label' => 'Cours',             'icon' => '📘', 'formats' => ['PDF']],
    'td'        => ['label' => 'TD',                'icon' => '📝', 'formats' => ['PDF']],
    'devoirs'   => ['label' => 'Devoirs',           'icon' => '📋', 'formats' => ['PDF']],
    'tp'        => ['label' => 'TP',                'icon' => '🔬', 'formats' => ['PDF','ZIP','.ipynb','.py','Vidéo','Autre']],
    'conseils'  => ['label' => 'Conseils',          'icon' => '💡', 'formats' => ['Texte','Image']],
    'annexes'   => ['label' => 'Ressources annexes','icon' => '📎', 'formats' => ['PDF','ZIP','Vidéo','Autre']],
];

// Regrouper les ressources validées par catégorie
$ressources_par_cat = [];
foreach ($categories_ressources as $key => $_) {
    $ressources_par_cat[$key] = [];
}
foreach ($ressources as $r) {
    if (($r['statut'] ?? '') === 'valide' && isset($ressources_par_cat[$r['categorie']])) {
        $ressources_par_cat[$r['categorie']][] = $r;
    }
}

// Traitement post forum
$forum_erreur = '';
$forum_succes = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isset($_SESSION['user'])) {
        $forum_erreur = "Vous devez être connecté pour participer.";
    } else {
        $action = $_POST['action'];

        if ($action === 'nouveau_post') {
            $contenu = trim($_POST['contenu'] ?? '');
            $titre   = trim($_POST['titre'] ?? '');
            if (!$contenu || !$titre) {
                $forum_erreur = "Titre et contenu requis.";
            } else {
                $nouveau = [
                    'id'        => time() . rand(100,999),
                    'titre'     => htmlspecialchars($titre),
                    'contenu'   => htmlspecialchars($contenu),
                    'auteur'    => $_SESSION['user']['nom'],
                    'auteur_id' => $_SESSION['user']['id'],
                    'date'      => date('Y-m-d H:i:s'),
                    'reponses'  => [],
                ];
                $data_module['posts'][] = $nouveau;
                file_put_contents($fichier_ressources, json_encode($data_module, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $posts = $data_module['posts'];
                $forum_succes = "Post publié !";
            }
        } elseif ($action === 'repondre') {
            $post_id = $_POST['post_id'] ?? '';
            $contenu = trim($_POST['contenu'] ?? '');
            if (!$contenu) {
                $forum_erreur = "La réponse ne peut pas être vide.";
            } else {
                foreach ($data_module['posts'] as &$p) {
                    if ((string)$p['id'] === (string)$post_id) {
                        $p['reponses'][] = [
                            'id'        => time() . rand(100,999),
                            'contenu'   => htmlspecialchars($contenu),
                            'auteur'    => $_SESSION['user']['nom'],
                            'auteur_id' => $_SESSION['user']['id'],
                            'date'      => date('Y-m-d H:i:s'),
                        ];
                        break;
                    }
                }
                file_put_contents($fichier_ressources, json_encode($data_module, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $posts = $data_module['posts'];
                $forum_succes = "Réponse publiée !";
            }
        }
    }
}

$user_connecte = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($module['titre']) ?> — SURVIVOR</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
/* ── Variables ── */
:root {
  --blanc:    #ffffff;
  --bleu:     #1a4fd6;
  --bleu-clair: #e8effe;
  --bleu-mid: #4169e8;
  --orange:   #f76c1b;
  --orange-clair: #fff3ec;
  --gris:     #f4f5f7;
  --gris-bord: #dde1ea;
  --texte:    #111827;
  --texte-sec:#6b7280;
  --radius:   12px;
  --shadow:   0 2px 12px rgba(26,79,214,.10);
  --shadow-lg:0 8px 32px rgba(26,79,214,.14);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'DM Sans', sans-serif;
  background: var(--gris);
  color: var(--texte);
  min-height: 100vh;
}

/* ── Navbar ── */
.navbar {
  background: var(--blanc);
  border-bottom: 2px solid var(--bleu-clair);
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 60px;
  position: sticky; top: 0; z-index: 100;
  box-shadow: 0 2px 8px rgba(26,79,214,.08);
}
.navbar-brand {
  font-family: 'Syne', sans-serif;
  font-weight: 800;
  font-size: 1.4rem;
  color: var(--bleu);
  text-decoration: none;
  letter-spacing: -0.5px;
}
.navbar-brand span { color: var(--orange); }
.navbar-links { display: flex; gap: 1.2rem; align-items: center; }
.navbar-links a {
  text-decoration: none;
  color: var(--texte-sec);
  font-size: .92rem;
  font-weight: 500;
  transition: color .2s;
}
.navbar-links a:hover { color: var(--bleu); }
.btn-nav {
  background: var(--bleu);
  color: var(--blanc) !important;
  padding: .42rem 1rem;
  border-radius: 6px;
  font-weight: 600 !important;
}

/* ── Breadcrumb ── */
.breadcrumb {
  max-width: 1100px;
  margin: 1.2rem auto .5rem;
  padding: 0 1.5rem;
  font-size: .85rem;
  color: var(--texte-sec);
}
.breadcrumb a { color: var(--bleu); text-decoration: none; }
.breadcrumb a:hover { text-decoration: underline; }

/* ── Hero module ── */
.module-hero {
  max-width: 1100px;
  margin: 0 auto 2rem;
  padding: 0 1.5rem;
}
.module-hero-inner {
  background: linear-gradient(135deg, var(--bleu) 0%, var(--bleu-mid) 100%);
  border-radius: var(--radius);
  padding: 2.5rem 2.5rem 2rem;
  display: flex;
  align-items: center;
  gap: 2rem;
  box-shadow: var(--shadow-lg);
  position: relative;
  overflow: hidden;
}
.module-hero-inner::before {
  content: '';
  position: absolute;
  top: -40px; right: -40px;
  width: 220px; height: 220px;
  background: rgba(255,255,255,.06);
  border-radius: 50%;
}
.module-hero-inner::after {
  content: '';
  position: absolute;
  bottom: -60px; right: 80px;
  width: 160px; height: 160px;
  background: rgba(247,108,27,.15);
  border-radius: 50%;
}
.hero-icon {
  width: 80px; height: 80px;
  background: rgba(255,255,255,.15);
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  font-size: 2.5rem;
  flex-shrink: 0;
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255,255,255,.2);
}
.hero-info h1 {
  font-family: 'Syne', sans-serif;
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.5px;
}
.hero-badge {
  display: inline-block;
  background: var(--orange);
  color: #fff;
  font-size: .78rem;
  font-weight: 700;
  padding: .25rem .75rem;
  border-radius: 20px;
  margin-top: .5rem;
  text-transform: uppercase;
  letter-spacing: .5px;
}
.hero-actions {
  margin-top: 1.2rem;
  display: flex;
  gap: .8rem;
  flex-wrap: wrap;
}
.btn-propose {
  background: var(--orange);
  color: #fff;
  border: none;
  padding: .55rem 1.3rem;
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-weight: 600;
  font-size: .9rem;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  transition: background .2s, transform .15s;
}
.btn-propose:hover { background: #d95e10; transform: translateY(-1px); }

/* ── Layout principal ── */
.page-layout {
  max-width: 1100px;
  margin: 0 auto 3rem;
  padding: 0 1.5rem;
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 1.5rem;
}
@media (max-width: 860px) {
  .page-layout { grid-template-columns: 1fr; }
}

/* ── Section ressources ── */
.section-card {
  background: var(--blanc);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.section-header {
  padding: 1.2rem 1.5rem;
  border-bottom: 1px solid var(--gris-bord);
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.section-title {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 1.1rem;
  color: var(--texte);
}

/* Toggle vue */
.view-toggle {
  display: flex;
  gap: .4rem;
}
.view-btn {
  background: var(--gris);
  border: 1px solid var(--gris-bord);
  border-radius: 6px;
  padding: .3rem .55rem;
  cursor: pointer;
  font-size: .85rem;
  color: var(--texte-sec);
  transition: all .2s;
}
.view-btn.active {
  background: var(--bleu);
  color: #fff;
  border-color: var(--bleu);
}

/* ── ONGLETS ── */
.tabs-container { padding: 1rem 1.5rem 0; }
.tabs-nav {
  display: flex;
  gap: .3rem;
  overflow-x: auto;
  padding-bottom: .5rem;
  scrollbar-width: none;
}
.tabs-nav::-webkit-scrollbar { display: none; }
.tab-btn {
  flex-shrink: 0;
  background: none;
  border: 2px solid transparent;
  border-bottom: 2px solid var(--gris-bord);
  padding: .55rem 1rem;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  font-size: .88rem;
  font-weight: 500;
  color: var(--texte-sec);
  border-radius: 6px 6px 0 0;
  transition: all .2s;
  display: flex;
  align-items: center;
  gap: .4rem;
}
.tab-btn:hover { color: var(--bleu); background: var(--bleu-clair); }
.tab-btn.active {
  color: var(--bleu);
  border-color: var(--gris-bord);
  border-bottom-color: var(--blanc);
  background: var(--blanc);
  font-weight: 600;
}
.tab-count {
  background: var(--bleu-clair);
  color: var(--bleu);
  font-size: .72rem;
  font-weight: 700;
  padding: .1rem .4rem;
  border-radius: 10px;
}
.tab-btn.active .tab-count { background: var(--bleu); color: #fff; }

.tabs-content { padding: 1.2rem 1.5rem 1.5rem; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── ACCORDÉONS ── */
.accordion-container { padding: .8rem 1.5rem 1.5rem; display: none; }
.accordion-item {
  border: 1px solid var(--gris-bord);
  border-radius: var(--radius);
  margin-bottom: .6rem;
  overflow: hidden;
}
.accordion-trigger {
  width: 100%;
  background: var(--gris);
  border: none;
  padding: .9rem 1.2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  cursor: pointer;
  font-family: 'DM Sans', sans-serif;
  font-size: .95rem;
  font-weight: 600;
  color: var(--texte);
  transition: background .2s;
}
.accordion-trigger:hover { background: var(--bleu-clair); }
.accordion-trigger.open { background: var(--bleu); color: #fff; }
.accordion-trigger.open .acc-count { background: rgba(255,255,255,.25); color: #fff; }
.acc-left { display: flex; align-items: center; gap: .6rem; }
.acc-arrow {
  transition: transform .3s;
  font-size: .8rem;
  color: var(--texte-sec);
}
.accordion-trigger.open .acc-arrow { transform: rotate(180deg); color: #fff; }
.acc-count {
  background: var(--bleu-clair);
  color: var(--bleu);
  font-size: .72rem;
  font-weight: 700;
  padding: .1rem .4rem;
  border-radius: 10px;
}
.accordion-body {
  display: none;
  padding: 1rem 1.2rem;
  border-top: 1px solid var(--gris-bord);
  animation: slideDown .25s ease;
}
.accordion-body.open { display: block; }
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Liste ressources ── */
.ressource-list { display: flex; flex-direction: column; gap: .6rem; }
.ressource-empty {
  color: var(--texte-sec);
  font-size: .9rem;
  font-style: italic;
  padding: .8rem 0;
  display: flex;
  align-items: center;
  gap: .5rem;
}
.ressource-item {
  display: flex;
  align-items: center;
  gap: .8rem;
  background: var(--gris);
  border: 1px solid var(--gris-bord);
  border-radius: 8px;
  padding: .75rem 1rem;
  text-decoration: none;
  color: var(--texte);
  transition: all .2s;
}
.ressource-item:hover {
  background: var(--bleu-clair);
  border-color: var(--bleu);
  transform: translateX(3px);
}
.ressource-icon {
  width: 38px; height: 38px;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}
.icon-pdf   { background: #fee2e2; }
.icon-zip   { background: #fef3c7; }
.icon-video { background: #ede9fe; }
.icon-other { background: var(--bleu-clair); }
.icon-texte { background: #d1fae5; }
.ressource-info { flex: 1; min-width: 0; }
.ressource-nom {
  font-weight: 600;
  font-size: .9rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.ressource-meta {
  font-size: .78rem;
  color: var(--texte-sec);
  margin-top: .15rem;
}
.ressource-badge {
  font-size: .72rem;
  font-weight: 700;
  padding: .2rem .5rem;
  border-radius: 5px;
  text-transform: uppercase;
  letter-spacing: .3px;
  flex-shrink: 0;
}
.badge-pdf   { background: #fee2e2; color: #dc2626; }
.badge-zip   { background: #fef3c7; color: #d97706; }
.badge-video { background: #ede9fe; color: #7c3aed; }
.badge-other { background: var(--bleu-clair); color: var(--bleu); }
.badge-texte { background: #d1fae5; color: #059669; }
.btn-ouvrir {
  background: var(--bleu);
  color: #fff;
  border: none;
  padding: .35rem .8rem;
  border-radius: 6px;
  font-size: .78rem;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  flex-shrink: 0;
  transition: background .2s;
}
.btn-ouvrir:hover { background: var(--bleu-mid); }

/* ── Sidebar Donjon ── */
.donjon-section {
  background: var(--blanc);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.donjon-header {
  background: linear-gradient(135deg, #1a1a2e, #16213e);
  padding: 1.2rem 1.5rem;
  display: flex;
  align-items: center;
  gap: .8rem;
}
.donjon-title {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  color: #fff;
}
.donjon-subtitle {
  font-size: .78rem;
  color: rgba(255,255,255,.6);
  margin-top: .15rem;
}
.donjon-icon { font-size: 1.6rem; }

/* Nouveau post */
.nouveau-post-area {
  padding: 1rem 1.2rem;
  border-bottom: 1px solid var(--gris-bord);
}
.nouveau-post-area textarea,
.nouveau-post-area input[type="text"] {
  width: 100%;
  border: 1px solid var(--gris-bord);
  border-radius: 8px;
  padding: .6rem .9rem;
  font-family: 'DM Sans', sans-serif;
  font-size: .88rem;
  resize: none;
  margin-bottom: .5rem;
  transition: border-color .2s;
}
.nouveau-post-area input[type="text"] { margin-bottom: .4rem; }
.nouveau-post-area textarea:focus,
.nouveau-post-area input[type="text"]:focus {
  outline: none;
  border-color: var(--bleu);
}
.btn-poster {
  background: var(--orange);
  color: #fff;
  border: none;
  padding: .5rem 1.1rem;
  border-radius: 7px;
  font-family: 'DM Sans', sans-serif;
  font-weight: 600;
  font-size: .85rem;
  cursor: pointer;
  transition: background .2s;
}
.btn-poster:hover { background: #d95e10; }
.connecte-msg {
  font-size: .83rem;
  color: var(--texte-sec);
  text-align: center;
  padding: .6rem;
}
.connecte-msg a { color: var(--bleu); font-weight: 600; }

/* Messages forum */
.alert-msg {
  margin: .6rem 1.2rem;
  padding: .5rem .9rem;
  border-radius: 7px;
  font-size: .85rem;
  font-weight: 500;
}
.alert-succes { background: #d1fae5; color: #059669; border-left: 3px solid #059669; }
.alert-erreur { background: #fee2e2; color: #dc2626; border-left: 3px solid #dc2626; }

/* Posts liste */
.posts-list {
  max-height: 520px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--gris-bord) transparent;
}
.post-item {
  border-bottom: 1px solid var(--gris-bord);
  padding: 1rem 1.2rem;
}
.post-item:last-child { border-bottom: none; }
.post-top {
  display: flex;
  align-items: flex-start;
  gap: .6rem;
  margin-bottom: .5rem;
}
.post-avatar {
  width: 34px; height: 34px;
  background: var(--bleu);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: .85rem;
  flex-shrink: 0;
}
.post-meta-info { flex: 1; min-width: 0; }
.post-auteur { font-weight: 700; font-size: .88rem; }
.post-date   { font-size: .75rem; color: var(--texte-sec); }
.post-titre  { font-weight: 600; font-size: .92rem; margin-bottom: .35rem; }
.post-contenu { font-size: .87rem; color: var(--texte-sec); line-height: 1.5; }

/* Réponses */
.reponses-list {
  margin-top: .8rem;
  padding-left: .8rem;
  border-left: 2px solid var(--bleu-clair);
  display: flex;
  flex-direction: column;
  gap: .5rem;
}
.reponse-item {
  background: var(--gris);
  border-radius: 8px;
  padding: .6rem .8rem;
}
.rep-header {
  display: flex;
  align-items: center;
  gap: .4rem;
  margin-bottom: .3rem;
}
.rep-avatar {
  width: 24px; height: 24px;
  background: var(--orange);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: .7rem;
  flex-shrink: 0;
}
.rep-auteur { font-size: .8rem; font-weight: 600; }
.rep-date   { font-size: .72rem; color: var(--texte-sec); }
.rep-contenu { font-size: .84rem; color: var(--texte-sec); }

/* Répondre form */
.repondre-toggle {
  background: none;
  border: none;
  color: var(--bleu);
  font-size: .8rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: .5rem;
  padding: 0;
  display: inline-flex;
  align-items: center;
  gap: .3rem;
}
.repondre-form {
  display: none;
  margin-top: .6rem;
  gap: .4rem;
}
.repondre-form textarea {
  width: 100%;
  border: 1px solid var(--gris-bord);
  border-radius: 7px;
  padding: .5rem .7rem;
  font-family: 'DM Sans', sans-serif;
  font-size: .84rem;
  resize: none;
  margin-bottom: .4rem;
}
.repondre-form textarea:focus {
  outline: none;
  border-color: var(--bleu);
}

/* Post vide */
.posts-empty {
  padding: 2rem 1.2rem;
  text-align: center;
  color: var(--texte-sec);
  font-size: .9rem;
}
.posts-empty .emoji { font-size: 2.5rem; display: block; margin-bottom: .5rem; }

/* ── Footer ── */
footer {
  text-align: center;
  padding: 1.5rem;
  color: var(--texte-sec);
  font-size: .82rem;
  border-top: 1px solid var(--gris-bord);
  background: var(--blanc);
}

/* ── Responsive ── */
@media (max-width: 600px) {
  .module-hero-inner { flex-direction: column; padding: 1.5rem; }
  .hero-icon { width: 56px; height: 56px; font-size: 1.8rem; }
  .hero-info h1 { font-size: 1.5rem; }
  .tabs-nav { gap: .15rem; }
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">SURVI<span>VOR</span></a>
  <div class="navbar-links">
    <a href="index.php">Accueil</a>
    <?php if ($user_connecte): ?>
      <a href="profil.php"><?= htmlspecialchars($_SESSION['user']['nom']) ?></a>
      <a href="deconnexion.php">Déconnexion</a>
    <?php else: ?>
      <a href="connexion.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-nav">Connexion</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Breadcrumb -->
<div class="breadcrumb">
  <a href="index.php">Accueil</a> › <strong><?= htmlspecialchars($module['titre']) ?></strong>
</div>

<!-- Hero -->
<div class="module-hero">
  <div class="module-hero-inner">
    <div class="hero-icon">📚</div>
    <div class="hero-info">
      <h1><?= htmlspecialchars($module['titre']) ?></h1>
      <span class="hero-badge"><?= htmlspecialchars($module['categorie'] ?? '') ?></span>
      <div class="hero-actions">
        <?php if ($user_connecte): ?>
          <a href="proposer_ressource.php?module_id=<?= $id ?>" class="btn-propose">✚ Proposer une ressource</a>
        <?php else: ?>
          <a href="connexion.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-propose">🔒 Connectez-vous pour proposer</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Layout -->
<div class="page-layout">

  <!-- Ressources -->
  <div class="section-card">
    <div class="section-header">
      <span class="section-title">📁 Ressources du module</span>
      <div class="view-toggle">
        <button class="view-btn active" id="btnOnglets" onclick="setVue('onglets')" title="Vue onglets">⊞ Onglets</button>
        <button class="view-btn" id="btnAccordeons" onclick="setVue('accordeons')" title="Vue accordéons">☰ Accordéons</button>
      </div>
    </div>

    <!-- VUE ONGLETS -->
    <div class="tabs-container" id="vueOnglets">
      <div class="tabs-nav" role="tablist">
        <?php $first = true; foreach ($categories_ressources as $key => $cat): ?>
          <button class="tab-btn <?= $first ? 'active' : '' ?>"
                  role="tab"
                  onclick="activerTab('<?= $key ?>')"
                  id="tab-<?= $key ?>">
            <?= $cat['icon'] ?> <?= $cat['label'] ?>
            <span class="tab-count"><?= count($ressources_par_cat[$key]) ?></span>
          </button>
        <?php $first = false; endforeach; ?>
      </div>

      <div class="tabs-content">
        <?php $first = true; foreach ($categories_ressources as $key => $cat): ?>
          <div class="tab-panel <?= $first ? 'active' : '' ?>" id="panel-<?= $key ?>">
            <div class="ressource-list">
              <?php if (empty($ressources_par_cat[$key])): ?>
                <div class="ressource-empty">📭 Aucune ressource pour cette catégorie.</div>
              <?php else: foreach ($ressources_par_cat[$key] as $r): ?>
                <?php
                  $ext = strtolower(pathinfo(parse_url($r['lien'], PHP_URL_PATH), PATHINFO_EXTENSION));
                  $type = in_array($ext, ['pdf']) ? 'pdf'
                        : (in_array($ext, ['zip','rar','7z']) ? 'zip'
                        : (in_array($r['type'] ?? '', ['video', 'Vidéo']) || str_contains($r['lien'], 'youtube') || str_contains($r['lien'], 'youtu.be') ? 'video' : 'other'));
                  $icons = ['pdf'=>'📄 icon-pdf','zip'=>'📦 icon-zip','video'=>'▶️ icon-video','other'=>'🔗 icon-other'];
                  $badges = ['pdf'=>'badge-pdf PDF','zip'=>'badge-zip ZIP','video'=>'badge-video Vidéo','other'=>'badge-other Lien'];
                  [$icon_class, $badge_class] = [explode(' ',$icons[$type])[0], $icons[$type]];
                  $badge_parts = explode(' ', $badges[$type]);
                ?>
                <a href="<?= htmlspecialchars($r['lien']) ?>" target="_blank" class="ressource-item">
                  <div class="ressource-icon <?= explode(' ',$icons[$type])[1] ?>"><?= explode(' ',$icons[$type])[0] ?></div>
                  <div class="ressource-info">
                    <div class="ressource-nom"><?= htmlspecialchars($r['titre']) ?></div>
                    <div class="ressource-meta">Ajouté par <?= htmlspecialchars($r['auteur'] ?? 'Admin') ?> · <?= date('d/m/Y', strtotime($r['date'] ?? 'now')) ?></div>
                  </div>
                  <span class="ressource-badge <?= $badge_parts[0] ?>"><?= $badge_parts[1] ?></span>
                </a>
              <?php endforeach; endif; ?>
            </div>
          </div>
        <?php $first = false; endforeach; ?>
      </div>
    </div>

    <!-- VUE ACCORDÉONS -->
    <div class="accordion-container" id="vueAccordeons">
      <?php foreach ($categories_ressources as $key => $cat): ?>
        <div class="accordion-item">
          <button class="accordion-trigger" onclick="toggleAcc('<?= $key ?>')" id="acc-trigger-<?= $key ?>">
            <span class="acc-left">
              <?= $cat['icon'] ?> <?= $cat['label'] ?>
              <span class="acc-count"><?= count($ressources_par_cat[$key]) ?></span>
            </span>
            <span class="acc-arrow">▼</span>
          </button>
          <div class="accordion-body" id="acc-body-<?= $key ?>">
            <div class="ressource-list">
              <?php if (empty($ressources_par_cat[$key])): ?>
                <div class="ressource-empty">📭 Aucune ressource pour cette catégorie.</div>
              <?php else: foreach ($ressources_par_cat[$key] as $r): ?>
                <?php
                  $ext = strtolower(pathinfo(parse_url($r['lien'], PHP_URL_PATH), PATHINFO_EXTENSION));
                  $type = in_array($ext, ['pdf']) ? 'pdf'
                        : (in_array($ext, ['zip','rar','7z']) ? 'zip'
                        : (str_contains($r['lien'], 'youtube') || str_contains($r['lien'], 'youtu.be') ? 'video' : 'other'));
                  $icons = ['pdf'=>'📄 icon-pdf','zip'=>'📦 icon-zip','video'=>'▶️ icon-video','other'=>'🔗 icon-other'];
                  $badges = ['pdf'=>'badge-pdf PDF','zip'=>'badge-zip ZIP','video'=>'badge-video Vidéo','other'=>'badge-other Lien'];
                ?>
                <a href="<?= htmlspecialchars($r['lien']) ?>" target="_blank" class="ressource-item">
                  <div class="ressource-icon <?= explode(' ',$icons[$type])[1] ?>"><?= explode(' ',$icons[$type])[0] ?></div>
                  <div class="ressource-info">
                    <div class="ressource-nom"><?= htmlspecialchars($r['titre']) ?></div>
                    <div class="ressource-meta">Ajouté par <?= htmlspecialchars($r['auteur'] ?? 'Admin') ?> · <?= date('d/m/Y', strtotime($r['date'] ?? 'now')) ?></div>
                  </div>
                  <span class="ressource-badge <?= explode(' ', $badges[$type])[0] ?>"><?= explode(' ', $badges[$type])[1] ?></span>
                </a>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Donjon du Module -->
  <div class="donjon-section">
    <div class="donjon-header">
      <div class="donjon-icon">🏰</div>
      <div>
        <div class="donjon-title">Le Donjon du Module</div>
        <div class="donjon-subtitle"><?= count($posts) ?> discussion<?= count($posts) !== 1 ? 's' : '' ?></div>
      </div>
    </div>

    <?php if ($forum_erreur): ?>
      <div class="alert-msg alert-erreur"><?= $forum_erreur ?></div>
    <?php elseif ($forum_succes): ?>
      <div class="alert-msg alert-succes">✓ <?= $forum_succes ?></div>
    <?php endif; ?>

    <!-- Zone nouveau post -->
    <div class="nouveau-post-area">
      <?php if ($user_connecte): ?>
        <form method="post">
          <input type="hidden" name="action" value="nouveau_post">
          <input type="text" name="titre" placeholder="Titre du post..." maxlength="120" required>
          <textarea name="contenu" rows="3" placeholder="Posez votre question ou partagez..." required></textarea>
          <button type="submit" class="btn-poster">🚀 Publier</button>
        </form>
      <?php else: ?>
        <div class="connecte-msg">
          <a href="connexion.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Connectez-vous</a> pour participer au Donjon.
        </div>
      <?php endif; ?>
    </div>

    <!-- Liste des posts -->
    <div class="posts-list">
      <?php if (empty($posts)): ?>
        <div class="posts-empty">
          <span class="emoji">🏰</span>
          Aucune discussion pour l'instant.<br>Soyez le premier à briser le silence !
        </div>
      <?php else:
        $posts_trie = array_reverse($posts);
        foreach ($posts_trie as $post): ?>
          <div class="post-item">
            <div class="post-top">
              <div class="post-avatar"><?= mb_strtoupper(mb_substr($post['auteur'], 0, 1)) ?></div>
              <div class="post-meta-info">
                <div class="post-auteur"><?= htmlspecialchars($post['auteur']) ?></div>
                <div class="post-date"><?= date('d/m/Y H:i', strtotime($post['date'])) ?></div>
              </div>
            </div>
            <div class="post-titre"><?= htmlspecialchars($post['titre']) ?></div>
            <div class="post-contenu"><?= nl2br(htmlspecialchars($post['contenu'])) ?></div>

            <!-- Réponses existantes -->
            <?php if (!empty($post['reponses'])): ?>
              <div class="reponses-list">
                <?php foreach ($post['reponses'] as $rep): ?>
                  <div class="reponse-item">
                    <div class="rep-header">
                      <div class="rep-avatar"><?= mb_strtoupper(mb_substr($rep['auteur'], 0, 1)) ?></div>
                      <span class="rep-auteur"><?= htmlspecialchars($rep['auteur']) ?></span>
                      <span class="rep-date">· <?= date('d/m/Y H:i', strtotime($rep['date'])) ?></span>
                    </div>
                    <div class="rep-contenu"><?= nl2br(htmlspecialchars($rep['contenu'])) ?></div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Bouton répondre -->
            <?php if ($user_connecte): ?>
              <button class="repondre-toggle" onclick="toggleRepondre('rep-<?= $post['id'] ?>')">
                💬 Répondre (<?= count($post['reponses']) ?>)
              </button>
              <div class="repondre-form" id="rep-<?= $post['id'] ?>">
                <form method="post">
                  <input type="hidden" name="action" value="repondre">
                  <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                  <textarea name="contenu" rows="2" placeholder="Votre réponse..." required></textarea>
                  <button type="submit" class="btn-poster" style="font-size:.82rem;padding:.4rem .9rem;">Envoyer</button>
                </form>
              </div>
            <?php else: ?>
              <button class="repondre-toggle" style="color:var(--texte-sec);cursor:default;">
                💬 <?= count($post['reponses']) ?> réponse<?= count($post['reponses']) !== 1 ? 's' : '' ?>
              </button>
            <?php endif; ?>
          </div>
      <?php endforeach; endif; ?>
    </div>
  </div>

</div><!-- /page-layout -->

<footer>
  © <?= date('Y') ?> SURVIVOR — Plateforme pédagogique Vibe codé a 99%
</footer>

<script>
// ── Vue toggle (onglets / accordéons) ──
function setVue(vue) {
  const btnO = document.getElementById('btnOnglets');
  const btnA = document.getElementById('btnAccordeons');
  const vO   = document.getElementById('vueOnglets');
  const vA   = document.getElementById('vueAccordeons');

  if (vue === 'onglets') {
    vO.style.display = ''; vA.style.display = 'none';
    btnO.classList.add('active'); btnA.classList.remove('active');
    localStorage.setItem('moduleVue', 'onglets');
  } else {
    vA.style.display = 'block'; vO.style.display = 'none';
    btnA.classList.add('active'); btnO.classList.remove('active');
    localStorage.setItem('moduleVue', 'accordeons');
  }
}

// ── Onglets ──
function activerTab(key) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-' + key).classList.add('active');
  document.getElementById('panel-' + key).classList.add('active');
}

// ── Accordéons ──
function toggleAcc(key) {
  const trigger = document.getElementById('acc-trigger-' + key);
  const body    = document.getElementById('acc-body-' + key);
  const isOpen  = body.classList.contains('open');
  // fermer tous
  document.querySelectorAll('.accordion-body').forEach(b => b.classList.remove('open'));
  document.querySelectorAll('.accordion-trigger').forEach(t => t.classList.remove('open'));
  if (!isOpen) {
    body.classList.add('open');
    trigger.classList.add('open');
  }
}

// ── Répondre toggle ──
function toggleRepondre(id) {
  const el = document.getElementById(id);
  el.style.display = el.style.display === 'block' ? 'none' : 'block';
}

// ── Mémoriser la vue ──
window.addEventListener('DOMContentLoaded', () => {
  const saved = localStorage.getItem('moduleVue') || 'onglets';
  setVue(saved);
});
</script>
</body>
</html>
