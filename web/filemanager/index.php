<?php
session_start();

$dbFile = 'users.db';
$db = new PDO("sqlite:$dbFile");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    password TEXT
)");

$CONFIG = [
    'lang' => 'en',
    'show_hidden' => false,
    'root_path' => $_SERVER['DOCUMENT_ROOT'] . '/uploads/',
    'allowed_upload_extensions' => 'jpg,jpeg,png,gif,txt,zip',
    'max_upload_size' => 5000000,
];

if (!is_dir($CONFIG['root_path'])) {
    mkdir($CONFIG['root_path'], 0777, true);
}

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    try {
        $stmt->execute([':username' => $username, ':password' => $password]);
        $message = "User  registered successfully.";
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
    } else {
        $message = "Invalid username or password.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && isset($_SESSION['user_id'])) {
    $file = $_FILES['file'];
    $targetFile = $CONFIG['root_path'] . basename($file["name"]);

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        $message = "File uploaded successfully.";
    } else {
        $message = "Error uploading file.";
    }
}

if (isset($_GET['delete']) && isset($_SESSION['user_id'])) {
    $filePath = $CONFIG['root_path'] . basename($_GET['delete']);
    if (file_exists($filePath)) {
        unlink($filePath);
        $message = "File deleted successfully.";
    } else {
        $message = "File not found.";
    }
}

$files = scandir($CONFIG['root_path']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link rel="stylesheet" href="index.css">
</head>

<body>

    <h1>File Manager</h1>

    <?php if (isset($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <h2>Login</h2>
        <form action="" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>

        <h2>Register</h2>
        <form action="" method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password " required>
            <button type="submit" name="register">Register</button>
        </form>
    <?php else: ?>
        <form action="" method="post" enctype="multipart/form-data" class="upload-form">
            <input type="file" name="file" required>
            <button type="submit">Upload</button>
        </form>

        <h2>Files</h2>
        <ul class="file-list">
            <?php
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    echo "<li>$file <a href='?delete=$file'>Delete</a></li>";
                }
            }
            ?>
        </ul>
        <form action="" method="post">
            <button type="submit" name="logout">Logout</button>
        </form>
    <?php endif; ?>

    <?php
    if (isset($_POST['logout'])) {
        session_destroy();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    ?>

</body>

</html>