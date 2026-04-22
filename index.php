<?php
session_start();

// Charger les modules
$modules = file_exists("data/modules.json") ? json_decode(file_get_contents("data/modules.json"), true) : [];

// Récupérer toutes les catégories distinctes
$categories = [];
foreach ($modules as $m) {
    if (isset($m['categorie']) && !in_array($m['categorie'], $categories)) {
        $categories[] = $m['categorie'];
    }
}
sort($categories);

// Compter les ressources validées par module
function compterRessources($module_id) {
    $fichier = "data/modules/module_{$module_id}.json";
    if (!file_exists($fichier)) return 0;
    $data = json_decode(file_get_contents($fichier), true);
    return count(array_filter($data['ressources'] ?? [], fn($r) => ($r['statut'] ?? '') === 'valide'));
}
function compterPosts($module_id) {
    $fichier = "data/modules/module_{$module_id}.json";
    if (!file_exists($fichier)) return 0;
    $data = json_decode(file_get_contents($fichier), true);
    return count($data['posts'] ?? []);
}

$user_connecte = isset($_SESSION['user']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SURVIVOR — Plateforme pédagogique</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<style>
/* ── Variables ── */
:root {
  --blanc:      #ffffff;
  --bleu:       #1a4fd6;
  --bleu-clair: #e8effe;
  --bleu-mid:   #4169e8;
  --orange:     #f76c1b;
  --orange-clair:#fff3ec;
  --gris:       #f4f5f7;
  --gris-bord:  #dde1ea;
  --texte:      #111827;
  --texte-sec:  #6b7280;
  --radius:     12px;
  --shadow:     0 2px 12px rgba(26,79,214,.10);
  --shadow-lg:  0 8px 32px rgba(26,79,214,.14);
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
.btn-nav-orange {
  background: var(--orange);
  color: var(--blanc) !important;
  padding: .42rem 1rem;
  border-radius: 6px;
  font-weight: 600 !important;
}
.user-chip {
  display: flex;
  align-items: center;
  gap: .5rem;
  background: var(--bleu-clair);
  border-radius: 20px;
  padding: .3rem .8rem .3rem .4rem;
}
.user-avatar {
  width: 28px; height: 28px;
  background: var(--bleu);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: .78rem;
}
.user-nom { font-size: .85rem; font-weight: 600; color: var(--bleu); }

/* ── Hero ── */
.hero {
  background: linear-gradient(135deg, var(--bleu) 0%, #2a5ce6 50%, #1a3fa0 100%);
  padding: 4rem 2rem 3.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.hero::before {
  content: '';
  position: absolute;
  top: -80px; left: -80px;
  width: 400px; height: 400px;
  background: rgba(255,255,255,.05);
  border-radius: 50%;
  pointer-events: none;
}
.hero::after {
  content: '';
  position: absolute;
  bottom: -100px; right: -60px;
  width: 350px; height: 350px;
  background: rgba(247,108,27,.12);
  border-radius: 50%;
  pointer-events: none;
}
.hero-tag {
  display: inline-block;
  background: rgba(247,108,27,.25);
  color: #ffb88a;
  font-size: .8rem;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  padding: .3rem .9rem;
  border-radius: 20px;
  margin-bottom: 1rem;
  border: 1px solid rgba(247,108,27,.4);
}
.hero h1 {
  font-family: 'Syne', sans-serif;
  font-size: clamp(2rem, 5vw, 3.2rem);
  font-weight: 800;
  color: #fff;
  letter-spacing: -1px;
  line-height: 1.1;
  margin-bottom: .8rem;
}
.hero h1 em { color: #ffb88a; font-style: normal; }
.hero-sub {
  font-size: 1.05rem;
  color: rgba(255,255,255,.75);
  max-width: 480px;
  margin: 0 auto 2rem;
  line-height: 1.6;
  font-weight: 300;
}
.hero-actions {
  display: flex;
  gap: .8rem;
  justify-content: center;
  flex-wrap: wrap;
}
.btn-hero-primary {
  background: var(--orange);
  color: #fff;
  padding: .75rem 1.8rem;
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-weight: 700;
  font-size: .95rem;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: background .2s, transform .15s;
  box-shadow: 0 4px 14px rgba(247,108,27,.4);
}
.btn-hero-primary:hover { background: #d95e10; transform: translateY(-2px); }
.btn-hero-ghost {
  background: rgba(255,255,255,.12);
  color: #fff;
  padding: .75rem 1.8rem;
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-weight: 600;
  font-size: .95rem;
  text-decoration: none;
  border: 1px solid rgba(255,255,255,.25);
  cursor: pointer;
  transition: background .2s;
  backdrop-filter: blur(4px);
}
.btn-hero-ghost:hover { background: rgba(255,255,255,.2); }

/* Stats hero */
.hero-stats {
  display: flex;
  justify-content: center;
  gap: 2.5rem;
  margin-top: 2.5rem;
  padding-top: 2rem;
  border-top: 1px solid rgba(255,255,255,.15);
  flex-wrap: wrap;
}
.stat-item { text-align: center; }
.stat-num {
  font-family: 'Syne', sans-serif;
  font-size: 1.8rem;
  font-weight: 800;
  color: #fff;
}
.stat-label { font-size: .8rem; color: rgba(255,255,255,.6); margin-top: .15rem; }

/* ── Filtres ── */
.filtres-bar {
  max-width: 1100px;
  margin: 2rem auto 0;
  padding: 0 1.5rem;
  display: flex;
  align-items: center;
  gap: .8rem;
  flex-wrap: wrap;
}
.filtres-label {
  font-size: .85rem;
  font-weight: 600;
  color: var(--texte-sec);
  white-space: nowrap;
}
.search-wrap {
  position: relative;
  flex: 1;
  max-width: 320px;
}
.search-wrap input {
  width: 100%;
  padding: .6rem 1rem .6rem 2.4rem;
  border: 1.5px solid var(--gris-bord);
  border-radius: 8px;
  background: var(--blanc);
  font-family: 'DM Sans', sans-serif;
  font-size: .9rem;
  transition: border-color .2s;
}
.search-wrap input:focus { outline: none; border-color: var(--bleu); }
.search-icon {
  position: absolute;
  left: .75rem; top: 50%;
  transform: translateY(-50%);
  color: var(--texte-sec);
  font-size: .9rem;
  pointer-events: none;
}
.semestre-tabs {
  display: flex;
  gap: .4rem;
  flex-wrap: wrap;
}
.sem-btn {
  background: var(--blanc);
  border: 1.5px solid var(--gris-bord);
  border-radius: 20px;
  padding: .4rem 1rem;
  font-family: 'DM Sans', sans-serif;
  font-size: .84rem;
  font-weight: 600;
  color: var(--texte-sec);
  cursor: pointer;
  transition: all .2s;
  white-space: nowrap;
}
.sem-btn:hover { border-color: var(--bleu); color: var(--bleu); }
.sem-btn.active {
  background: var(--bleu);
  border-color: var(--bleu);
  color: #fff;
}
.count-result {
  font-size: .83rem;
  color: var(--texte-sec);
  margin-left: auto;
  white-space: nowrap;
}

/* ── Grille modules ── */
.modules-section {
  max-width: 1100px;
  margin: 1.5rem auto 4rem;
  padding: 0 1.5rem;
}
.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1.2rem;
}

/* Carte module */
.module-card {
  background: var(--blanc);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: box-shadow .25s, transform .25s;
  border: 1.5px solid transparent;
  text-decoration: none;
  color: var(--texte);
  position: relative;
}
.module-card:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-4px);
  border-color: var(--bleu-clair);
}
.card-cover {
  position: relative;
  height: 160px;
  overflow: hidden;
  background: var(--bleu-clair);
}
.card-cover img {
  width: 100%; height: 100%;
  object-fit: cover;
  transition: transform .4s ease;
}
.module-card:hover .card-cover img { transform: scale(1.07); }

/* Pas d'image : placeholder */
.card-cover-placeholder {
  width: 100%; height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  background: linear-gradient(135deg, var(--bleu-clair), #d0dbff);
}
.card-sem-badge {
  position: absolute;
  top: .7rem; left: .7rem;
  background: rgba(26,79,214,.85);
  color: #fff;
  font-size: .72rem;
  font-weight: 700;
  padding: .2rem .65rem;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: .4px;
  backdrop-filter: blur(4px);
}
.card-body {
  padding: 1rem 1.1rem 1.2rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.card-titre {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  color: var(--texte);
  margin-bottom: .5rem;
  line-height: 1.3;
}
.card-meta-row {
  display: flex;
  gap: .8rem;
  margin-bottom: .9rem;
}
.card-meta-item {
  display: flex;
  align-items: center;
  gap: .3rem;
  font-size: .78rem;
  color: var(--texte-sec);
}
.card-footer {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.btn-voir {
  background: var(--bleu);
  color: #fff;
  padding: .42rem 1rem;
  border-radius: 7px;
  font-size: .84rem;
  font-weight: 700;
  text-decoration: none;
  transition: background .2s;
  display: inline-flex;
  align-items: center;
  gap: .3rem;
}
.btn-voir:hover { background: var(--bleu-mid); }
.donjon-chip {
  display: flex;
  align-items: center;
  gap: .3rem;
  font-size: .75rem;
  color: var(--texte-sec);
}

/* Carte vide */
.no-result {
  grid-column: 1 / -1;
  text-align: center;
  padding: 3rem 1rem;
  color: var(--texte-sec);
}
.no-result .ico { font-size: 3rem; display: block; margin-bottom: .6rem; }

/* ── Section by semestre ── */
.section-semestre-titre {
  font-family: 'Syne', sans-serif;
  font-weight: 700;
  font-size: 1rem;
  color: var(--texte-sec);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 1.5rem 0 .8rem;
  display: flex;
  align-items: center;
  gap: .6rem;
}
.section-semestre-titre::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--gris-bord);
}

/* ── Footer ── */
footer {
  text-align: center;
  padding: 1.5rem;
  color: var(--texte-sec);
  font-size: .82rem;
  border-top: 1px solid var(--gris-bord);
  background: var(--blanc);
}

/* ── Animations ── */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}
.module-card {
  animation: fadeUp .4s ease both;
}

/* ── Responsive ── */
@media (max-width: 600px) {
  .hero { padding: 2.5rem 1.2rem 2rem; }
  .hero-stats { gap: 1.5rem; }
  .navbar { padding: 0 1rem; }
  .navbar-links .btn-nav-orange { display: none; }
  .filtres-bar { gap: .6rem; }
  .modules-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
}
</style>
</head>
<body>

<!-- ── Navbar ── -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">SURVI<span>VOR</span></a>
  <div class="navbar-links">
    <a href="propos.php">À propos</a>
    <?php if ($user_connecte): ?>
      <div class="user-chip">
        <div class="user-avatar"><?= mb_strtoupper(mb_substr($_SESSION['user']['nom'], 0, 1)) ?></div>
        <span class="user-nom"><?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
      </div>
      <a href="deconnexion.php" style="color:var(--texte-sec);font-size:.85rem;">Déconnexion</a>
    <?php else: ?>
      <a href="inscription.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-nav-orange">S'inscrire</a>
      <a href="connexion.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="btn-nav">Connexion</a>
      
    <?php endif; ?>
  </div>
</nav>

<!-- ── Hero ── -->
<section class="hero">
  <div class="hero-tag">🎓 Plateforme pédagogique</div>
  <h1>Bienvenue sur <em>SURVIVOR</em></h1>
  <p class="hero-sub">Tous tes cours, TD, TP et ressources au même endroit. Survie garantie.</p>
  <div class="hero-actions">
    <a href="#modules" class="btn-hero-primary">📚 Explorer les modules</a>
    <a href="propos.php" class="btn-hero-ghost">Qui sommes-nous ?</a>
  </div>
  <div class="hero-stats">
    <div class="stat-item">
      <div class="stat-num"><?= count($modules) ?></div>
      <div class="stat-label">Modules</div>
    </div>
    <div class="stat-item">
      <div class="stat-num"><?= count($categories) ?></div>
      <div class="stat-label">Semestres</div>
    </div>
    <div class="stat-item">
      <div class="stat-num">6</div>
      <div class="stat-label">Types de ressources</div>
    </div>
  </div>
</section>

<!-- ── Filtres ── -->
<div class="filtres-bar" id="modules">
  <span class="filtres-label">Filtrer :</span>
  <div class="search-wrap">
    <span class="search-icon">🔍</span>
    <input type="search" id="searchInput" placeholder="Rechercher un module..." autocomplete="off">
  </div>
  <div class="semestre-tabs">
    <button class="sem-btn active" data-sem="">Tous</button>
    <?php foreach ($categories as $cat): ?>
      <button class="sem-btn" data-sem="<?= htmlspecialchars(strtolower($cat)) ?>">
        <?= ucfirst(htmlspecialchars($cat)) ?>
      </button>
    <?php endforeach; ?>
  </div>
  <span class="count-result" id="countResult"><?= count($modules) ?> module<?= count($modules) > 1 ? 's' : '' ?></span>
</div>

<!-- ── Grille modules ── -->
<div class="modules-section">
  <div class="modules-grid" id="modulesGrid">
    <?php
    // Trier par semestre pour afficher un séparateur (côté PHP, les data-attr permettent le tri JS)
    usort($modules, fn($a, $b) => strcmp($a['categorie'] ?? '', $b['categorie'] ?? ''));
    foreach ($modules as $i => $m):
      $cover = is_array($m['cover']) ? $m['cover'][0] : ($m['cover'] ?? '');
      $nb_ressources = compterRessources($m['id']);
      $nb_posts      = compterPosts($m['id']);
      // Animation delay
      $delay = ($i % 12) * 0.05;
    ?>
      <a class="module-card"
         href="module.php?id=<?= $m['id'] ?>"
         data-nom="<?= strtolower(htmlspecialchars($m['titre'])) ?>"
         data-sem="<?= strtolower(htmlspecialchars($m['categorie'] ?? '')) ?>"
         style="animation-delay:<?= $delay ?>s">
        <div class="card-cover">
          <?php if ($cover): ?>
            <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($m['titre']) ?>" loading="lazy">
          <?php else: ?>
            <div class="card-cover-placeholder">📘</div>
          <?php endif; ?>
          <span class="card-sem-badge"><?= htmlspecialchars($m['categorie'] ?? '') ?></span>
        </div>
        <div class="card-body">
          <div class="card-titre"><?= htmlspecialchars($m['titre']) ?></div>
          <div class="card-meta-row">
            <span class="card-meta-item" title="Ressources validées">📁 <?= $nb_ressources ?> ressource<?= $nb_ressources !== 1 ? 's' : '' ?></span>
            <span class="card-meta-item" title="Discussions">🏰 <?= $nb_posts ?> post<?= $nb_posts !== 1 ? 's' : '' ?></span>
          </div>
          <div class="card-footer">
            <span class="btn-voir">Ouvrir →</span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- ── Footer ── -->
<footer>
  © <?= date('Y') ?> SURVIVOR — Plateforme pédagogique · vibe codé a 99%
</footer>

<script>
const searchInput     = document.getElementById('searchInput');
const semBtns         = document.querySelectorAll('.sem-btn');
const grid            = document.getElementById('modulesGrid');
const cards           = grid.querySelectorAll('.module-card');
const countResult     = document.getElementById('countResult');

let semActif = '';

function filtrer() {
  const terme = searchInput.value.trim().toLowerCase();
  let visible = 0;

  cards.forEach(card => {
    const nom = card.getAttribute('data-nom');
    const sem = card.getAttribute('data-sem');
    const matchSearch = nom.includes(terme);
    const matchSem    = semActif === '' || sem === semActif;
    const show        = matchSearch && matchSem;
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });

  countResult.textContent = visible + ' module' + (visible !== 1 ? 's' : '');

  // Message si vide
  const existing = grid.querySelector('.no-result');
  if (visible === 0 && !existing) {
    grid.insertAdjacentHTML('beforeend',
      `<div class="no-result"><span class="ico">🔎</span>Aucun module trouvé pour « ${terme} »</div>`);
  } else if (visible > 0 && existing) {
    existing.remove();
  }
}

searchInput.addEventListener('input', filtrer);

semBtns.forEach(btn => {
  btn.addEventListener('click', () => {
    semBtns.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    semActif = btn.getAttribute('data-sem');
    filtrer();
  });
});
</script>
</body>
</html>