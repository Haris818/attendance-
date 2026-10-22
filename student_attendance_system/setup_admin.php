
<?php
require 'config.php';

$username = 'admin';
$password = 'ChangeMe123!';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "SELECT id FROM admins WHERE username = ?"
);
$stmt->execute([$username]);

$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($admins) > 0) {
    $stmt = $pdo->prepare(
        "UPDATE admins SET password_hash = ? WHERE username = ?"
    );
    $stmt->execute([$hash, $username]);

    echo "Admin password reset successfully!";
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO admins (username, password_hash) VALUES (?, ?)"
    );
    $stmt->execute([$username, $hash]);

    echo "Admin account created successfully!";
}

echo "<br>Username: admin";
echo "<br>Password: ChangeMe123!";
echo "<br>Database: " . htmlspecialchars(
    $pdo->query("SELECT DATABASE()")->fetchColumn()
);
?>
