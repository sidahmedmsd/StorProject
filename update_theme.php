<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $theme = $data['theme'] ?? 'light-mode';
    
    // Validate theme value
    if ($theme !== 'light-mode' && $theme !== 'dark-mode') {
        $theme = 'light-mode';
    }

    // Always set cookie for guest persistence/persistence after logout
    setcookie('theme', $theme, time() + (30 * 24 * 60 * 60), "/");

    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "UPDATE users SET theme = :theme WHERE id = :id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":theme", $theme);
        oci_bind_by_name($stmt, ":id", $user_id);
        
        if (oci_execute($stmt)) {
            $_SESSION['theme'] = $theme;
            echo json_encode(['success' => true, 'logged_in' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database update failed']);
        }
    } else {
        // Guest mode - cookie is already set above
        echo json_encode(['success' => true, 'logged_in' => false]);
    }
}
?>
