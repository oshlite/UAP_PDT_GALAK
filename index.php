<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth" >
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>GALAK - Katalog Game Modern</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;500;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primaryLight: '#3B82F6', primaryDark: '#60A5FA',
            secondaryLight: '#8B5CF6', secondaryDark: '#A78BFA',
            backgroundLight: '#F9FAFB', backgroundDark: '#111827',
            surfaceLight: 'rgba(255 255 255 / 0.15)', surfaceDark: 'rgba(20 20 30 / 0.65)',
            textLight: '#111827', textDark: '#E5E7EB',
          },
          fontFamily: {
            orbitron: ['Orbitron', 'sans-serif'], poppins: ['Poppins', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <style>
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: var(--scrollbar-track); }
    ::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb); border-radius: 100px; box-shadow: inset 0 0 5px var(--scrollbar-thumb-shadow); }
    ::-webkit-scrollbar-thumb:hover { background: var(--scrollbar-thumb-hover); }
    :root { --scrollbar-track: #E5E7EB; --scrollbar-thumb: #3B82F6; --scrollbar-thumb-shadow: #8B5CF6; --scrollbar-thumb-hover: #8B5CF6; --surface-bg: rgba(255 255 255 / 0.15); --text-shadow-color: #8B5CF6; }
    .dark { --scrollbar-track: #1F2937; --scrollbar-thumb: #60A5FA; --scrollbar-thumb-shadow: #A78BFA; --scrollbar-thumb-hover: #A78BFA; --surface-bg: rgba(20 20 30 / 0.65); --text-shadow-color: #A78BFA; }
    .glass-card { background: var(--surface-bg); backdrop-filter: saturate(180%) blur(12px); border-radius: 1rem; border: 1px solid rgba(255 255 255 / 0.3); box-shadow: 0 0 10px var(--scrollbar-thumb), 0 0 40px var(--scrollbar-thumb-shadow); transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease; }
    .glass-card:hover { transform: translateY(-10px); box-shadow: 0 0 20px var(--scrollbar-thumb), 0 0 60px var(--scrollbar-thumb-shadow); border-color: rgba(255 255 255 / 0.5); }
    a.neon-btn { display: inline-flex; align-items: center; gap: 0.5rem; background-color: transparent; border: 2px solid var(--scrollbar-thumb); color: var(--scrollbar-thumb); padding: 0.65rem 1.5rem; font-weight: 600; border-radius: 1rem; box-shadow: 0 0 10px var(--scrollbar-thumb); text-transform: uppercase; font-size: 1rem; transition: all 0.3s ease; text-decoration: none; user-select: none; cursor: pointer; }
    a.neon-btn:hover, a.neon-btn:focus { background: var(--scrollbar-thumb); color: white; box-shadow: 0 0 15px var(--scrollbar-thumb), 0 0 40px var(--scrollbar-thumb-shadow); transform: scale(1.05); outline: none; }
    .genre-badge { font-weight: 700; font-size: 0.75rem; text-transform: uppercase; color: #F9FAFB; background: linear-gradient(45deg, var(--scrollbar-thumb), var(--scrollbar-thumb-shadow)); padding: 0.25rem 0.75rem; border-radius: 9999px; box-shadow: 0 0 5px var(--scrollbar-thumb), 0 0 10px var(--scrollbar-thumb-shadow); user-select: none; }
    h1, h2, h3, .orbitron { font-family: 'Orbitron', sans-serif; color: var(--scrollbar-thumb); text-shadow: 0 0 5px var(--text-shadow-color), 0 0 10px var(--scrollbar-thumb); }
    body { transition: background-color 0.3s ease, color 0.3s ease; }
    .hero-title { text-shadow: 0 0 14px var(--scrollbar-thumb-shadow), 0 0 30px var(--scrollbar-thumb); }
    footer { user-select:none; }
    .fade-in { animation: fadeIn 0.8s ease forwards; opacity: 0; }
    @keyframes fadeIn { to { opacity: 1; } }
    .toggle-bg { background: #ddd; border-radius: 9999px; width: 3rem; height: 1.5rem; position: relative; cursor: pointer; transition: background-color 0.3s ease; }
    .toggle-circle { background: white; border-radius: 50%; width: 1.3rem; height: 1.3rem; position: absolute; top: 0.1rem; left: 0.1rem; transition: transform 0.3s ease; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
    .dark .toggle-bg { background: #3B82F6; }
    .dark .toggle-circle { transform: translateX(1.5rem); }
  </style>
</head>
<body class="bg-backgroundLight text-textLight dark:bg-backgroundDark dark:text-textDark transition-colors duration-300 min-h-screen flex flex-col">

  <header class="sticky top-0 bg-backgroundLight dark:bg-backgroundDark bg-opacity-90 dark:bg-opacity-90 backdrop-blur border-b border-gray-300 dark:border-gray-700 z-50">
    <nav class="container mx-auto flex items-center justify-between px-6 py-4">
      <a href="index.php" class="text-3xl orbitron font-extrabold text-primaryLight dark:text-primaryDark select-none">GALAK</a>
      
      <div class="hidden md:flex flex-grow justify-center items-center gap-6 text-lg font-semibold text-textLight dark:text-textDark">
        <a href="index.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Beranda</a>
        <a href="katalog.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Katalog Game</a>
        
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <span class="text-gray-400 dark:text-gray-600">|</span>
            <span class="text-base font-medium">Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="text-base font-semibold hover:text-primaryLight dark:hover:text-primaryDark transition" title="Dashboard Admin">Dashboard</a>
            <?php endif; ?>
        <?php endif; ?>
      </div>

      <div class="flex items-center gap-4">
        <div class="hidden md:flex items-center">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
              <a href="logout.php" onclick="return confirm('Anda yakin ingin keluar?');" class="px-3 py-1 border-2 border-red-500 rounded-lg text-sm text-red-500 hover:bg-red-500 hover:text-white transition">Logout</a>
            <?php else: ?>
                <a href="login.php" class="px-4 py-2 border-2 border-primaryLight dark:border-primaryDark rounded-xl text-base font-semibold text-primaryLight dark:text-primaryDark hover:bg-primaryLight hover:text-white transition">Login</a>
            <?php endif; ?>
        </div>

        <div id="toggle" class="toggle-bg" role="switch" aria-checked="false" tabindex="0">
          <div class="toggle-circle"></div>
        </div>

        <button id="menu-btn" aria-label="Toggle menu" aria-expanded="false" class="md:hidden focus:outline-none text-primaryLight dark:text-primaryDark">
          <span class="material-icons text-4xl select-none">menu</span>
        </button>
      </div>
    </nav>
    
    <nav id="mobile-menu" class="hidden md:hidden bg-backgroundLight dark:bg-backgroundDark border-t border-gray-300 dark:border-gray-700 shadow-lg">
      <a href="index.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b border-gray-300 dark:border-gray-700 hover:bg-primaryLight hover:text-white transition">Beranda</a>
      <a href="katalog.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b border-gray-300 dark:border-gray-700 hover:bg-primaryLight hover:text-white transition">Katalog Game</a>
      <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
          <?php if ($_SESSION['role'] === 'admin'): ?>
              <a href="admin_dashboard.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark border-b border-gray-300 dark:border-gray-700 hover:bg-primaryLight hover:text-white transition">Dashboard</a>
          <?php endif; ?>
          <a href="logout.php" class="block px-6 py-3 text-red-500 dark:text-red-600 hover:bg-red-500 hover:text-white transition">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
      <?php else: ?>
          <a href="login.php" class="block px-6 py-3 text-primaryLight dark:text-primaryDark hover:bg-primaryLight hover:text-white transition">Login</a>
      <?php endif; ?>
    </nav>
  </header>

  <main class="container mx-auto px-6 py-12 flex-grow">
    <section class="text-center mb-20 fade-in">
      <h1 class="hero-title text-5xl md:text-6xl font-extrabold mb-5 select-none">Temukan Dunia Game <span class="text-secondaryLight dark:text-secondaryDark">Asyik</span> di GALAK</h1>
      <p class="text-textLight dark:text-textDark text-lg max-w-xl mx-auto mb-8 leading-relaxed select-none">Katalog game modern, futuristik, dan penuh warna. Akses mudah, fitur lengkap, dan tampilan visual yang memukau.</p>
      <a href="katalog.php" class="neon-btn" aria-label="Lihat katalog game">
        <span class="material-icons"></span> Lihat Katalog Game
      </a>
    </section>

    <section>
      <h2 class="orbitron text-4xl mb-14 text-center text-primaryLight dark:text-primaryDark select-none">Game Unggulan</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-12">
        <article class="glass-card fade-in">
          <div class="relative overflow-hidden rounded-xl">
            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/ba2f4111-8026-4312-8af4-7d4505952395.png" alt="Ilustrasi petualangan dengan pemandangan pegunungan dan karakter heroik" loading="lazy" class="w-full object-cover h-48 sm:h-56 rounded-t-xl" />
            <span class="genre-badge absolute top-3 left-3">Petualangan</span>
          </div>
          <div class="p-6">
            <h3 class="text-2xl font-semibold mb-3 text-textLight dark:text-textDark">Petualangan Seru</h3>
            <p class="text-textLight dark:text-textDark mb-5 leading-relaxed select-text">Jelajahi dunia penuh misteri dan tantangan dengan gameplay seru dan memikat.</p>
            <a href="game_detail.php?id=1" class="neon-btn" aria-label="Lihat detail Petualangan Seru">
              <span class="material-icons" aria-hidden="true">Lihat</span> Detail
            </a>
          </div>
        </article>
        <article class="glass-card fade-in" style="animation-delay: 0.2s;">
          <div class="relative overflow-hidden rounded-xl">
            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/f1c95f11-a4d4-4b8c-b393-8522da0064f1.png" alt="Ilustrasi mobil balap futuristik berkecepatan tinggi" loading="lazy" class="w-full object-cover h-48 sm:h-56 rounded-t-xl" />
            <span class="genre-badge absolute top-3 left-3 bg-gradient-to-r from-blue-600 to-purple-600">Balap</span>
          </div>
          <div class="p-6">
            <h3 class="text-2xl font-semibold mb-3 text-textLight dark:text-textDark">Balapan Kecepatan</h3>
            <p class="text-textLight dark:text-textDark mb-5 leading-relaxed select-text">Nikmati sensasi balap super cepat dengan kendaraan futuristik dan trek memukau.</p>
            <a href="game_detail.php?id=2" class="neon-btn" aria-label="Lihat detail Balapan Kecepatan">
              <span class="material-icons" aria-hidden="true">Lihat</span> Detail
            </a>
          </div>
        </article>
        <article class="glass-card fade-in" style="animation-delay: 0.4s;">
          <div class="relative overflow-hidden rounded-xl">
            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/6a538afd-6799-4e4a-8967-c31ec35aaa07.png" alt="Ilustrasi strategi perang dengan peta dan pasukan" loading="lazy" class="w-full object-cover h-48 sm:h-56 rounded-t-xl" />
            <span class="genre-badge absolute top-3 left-3 bg-gradient-to-r from-purple-600 to-pink-600">Strategi</span>
          </div>
          <div class="p-6">
            <h3 class="text-2xl font-semibold mb-3 text-textLight dark:text-textDark">Strategi Perang</h3>
            <p class="text-textLight dark:text-textDark mb-5 leading-relaxed select-text">Rancang taktik jitu untuk kuasai medan perang dengan kecerdasan dan strategi.</p>
            <a href="game_detail.php?id=3" class="neon-btn" aria-label="Lihat detail Strategi Perang">
              <span class="material-icons" aria-hidden="true">Lihat</span> Detail
            </a>
          </div>
        </article>
      </div>
    </section>
  </main>

  <footer class="bg-gradient-to-r from-blue-100 to-purple-100 p-6 mt-16 select-none text-center text-gray-600 dark:text-gray-400 text-sm">
    &copy; 2025 GALAK. Hak cipta dilindungi undang-undang.
  </footer>

  <script>
    const btn = document.getElementById('menu-btn');
    const menu = document.getElementById('mobile-menu');
    btn.addEventListener('click', () => {
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', !expanded);
      menu.classList.toggle('hidden');
    });
    const toggle = document.getElementById('toggle');
    const htmlElement = document.documentElement;
    function applyTheme(theme) {
      if(theme === 'dark') {
        htmlElement.classList.add('dark');
        toggle.setAttribute('aria-checked', 'true');
      } else {
        htmlElement.classList.remove('dark');
        toggle.setAttribute('aria-checked', 'false');
      }
    }
    function getPreferredTheme() {
      if(localStorage.getItem('theme')) { return localStorage.getItem('theme'); }
      if(window.matchMedia('(prefers-color-scheme: dark)').matches) { return 'dark'; }
      return 'light';
    }
    applyTheme(getPreferredTheme());
    toggle.addEventListener('click', () => {
      if(htmlElement.classList.contains('dark')) {
        applyTheme('light');
        localStorage.setItem('theme', 'light');
      } else {
        applyTheme('dark');
        localStorage.setItem('theme', 'dark');
      }
    });
    toggle.addEventListener('keydown', e => {
      if(e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggle.click();
      }
    });
  </script>

</body>
</html>