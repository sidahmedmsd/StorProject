<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $theme = $data['theme'] ?? 'light-mode';
    
    
    if ($theme !== 'light-mode' && $theme !== 'dark-mode') {
        $theme = 'light-mode';
    }


    setcookie('theme', $theme, time() + (30 * 24 * 60 * 60), "/");

        
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        $sql = "UPDATE users SET theme = :theme WHERE id = :id";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":theme", $theme);
        oci_bind_by_name($stmt, ":id", $user_id);
        
        if (oci_execute($stmt)) {
            oci_commit($conn); // IMPORTANT: Commit changes
            $_SESSION['theme'] = $theme; // Update current session
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => true]);
    }
}
?>
