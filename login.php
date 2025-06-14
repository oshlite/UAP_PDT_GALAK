<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'database_galak';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("<h1 class='text-red-600 p-6 font-bold'>Koneksi database gagal: " . htmlspecialchars($conn->connect_error) . "</h1>");
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = "Username dan password harus diisi.";
    } else {
        $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id();
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                $role_stmt = $conn->prepare('SELECT IsUserAdmin(?) AS is_admin');
                $role_stmt->bind_param('i', $user['id']);
                $role_stmt->execute();
                $role_result = $role_stmt->get_result();
                $is_admin = 0;
                if ($role_result && $row = $role_result->fetch_assoc()) {
                    $is_admin = $row['is_admin'];
                }
                $role_stmt->close();
                $_SESSION['role'] = ($is_admin == 1) ? 'admin' : 'user';

                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error_message = "Username atau password salah.";
            }
        } else {
            $error_message = "Username atau password salah.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - GALAK</title>

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
    :root { --primary: #3B82F6; --secondary: #8B5CF6; --background: #F9FAFB; --text: #111827; --surface-bg: rgba(255 255 255 / 0.15); --shadow: rgba(59, 130, 246, 0.4); }
    .dark { --primary: #60A5FA; --secondary: #A78BFA; --background: #111827; --text: #E5E7EB; --surface-bg: rgba(20 20 30 / 0.65); --shadow: rgba(96, 165, 250, 0.6); }
    body { font-family: 'Poppins', sans-serif; background-color: var(--background); color: var(--text); }
    .orbitron { font-family: 'Orbitron', sans-serif; }
    .glass-card { background: var(--surface-bg); backdrop-filter: saturate(180%) blur(12px); border-radius: 1rem; border: 1px solid rgba(255 255 255 / 0.3); box-shadow: 0 4px 20px var(--shadow); padding: 2rem; }
    .form-input { width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid #D1D5DB; background-color: rgba(255,255,255,0.8); color: #111827; }
    .dark .form-input { border: 1px solid #4B5563; background-color: rgba(31,41,55,0.8); color: #E5E7EB; }
    .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 8px var(--primary); }
    .btn-submit { background-color: var(--primary); color: white; padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; box-shadow: 0 0 15px var(--primary); transition: all 0.3s ease; }
    .btn-submit:hover { background-color: var(--secondary); box-shadow: 0 0 25px var(--secondary); }
  </style>
</head>
<body class="dark:bg-backgroundDark flex flex-col min-h-screen">

  <header class="sticky top-0 bg-backgroundLight dark:bg-backgroundDark bg-opacity-90 dark:bg-opacity-90 backdrop-blur border-b border-gray-300 dark:border-gray-700 z-50">
    <nav class="container mx-auto flex items-center justify-between px-6 py-4">
      <a href="index.php" class="text-3xl orbitron font-extrabold text-primaryLight dark:text-primaryDark select-none">GAL<span class="text-secondaryLight dark:text-secondaryDark">AK</span></a>
      <div class="hidden md:flex gap-6 text-lg font-semibold">
        <a href="index.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Beranda</a>
        <a href="katalog.php" class="hover:text-primaryLight dark:hover:text-primaryDark transition">Katalog</a>
      </div>
    </nav>
  </header>

  <main class="container mx-auto px-6 py-12 flex-grow flex items-center justify-center">
    <div class="w-full max-w-md">
      <form method="post" action="login.php" class="glass-card space-y-6" novalidate>
        <h1 class="orbitron text-3xl text-center text-primaryLight dark:text-primaryDark">Login</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="text-red-500 text-center p-3 bg-red-100 dark:bg-red-900/50 border border-red-400 rounded"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div>
          <label for="username" class="block mb-2 font-semibold text-textLight dark:text-textDark">Username</label>
          <input type="text" id="username" name="username" required class="form-input" />
        </div>
        <div>
          <label for="password" class="block mb-2 font-semibold text-textLight dark:text-textDark">Password</label>
          <input type="password" id="password" name="password" required class="form-input" />
        </div>
        <button type="submit" class="btn-submit w-full">Login</button>
        <p class="text-center text-sm text-textLight dark:text-textDark">
          Belum punya akun? <a href="register.php" class="font-semibold text-primaryLight dark:text-primaryDark hover:underline">Daftar di sini</a>
        </p>
      </form>
    </div>
  </main>

  <footer class="bg-gradient-to-r from-blue-100 to-purple-100 dark:from-blue-900/50 dark:to-purple-900/50 p-6 mt-auto text-center text-gray-600 dark:text-gray-400 text-sm">
    &copy; <?= date('Y') ?> GALAK. Hak cipta dilindungi undang-undang.
  </footer>

</body>
</html>