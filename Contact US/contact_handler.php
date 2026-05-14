<?php
/* ========================================
   AL BURHAN — CONTACT FORM HANDLER
   File: Contact US/contact_handler.php
   ======================================== */

header('Content-Type: application/json');

/* Load shared DB config */
require_once '../Admin-Panel/config.php';

/* Only accept POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

/* ---- Sanitize & Validate ---- */
$firstname = trim(htmlspecialchars($_POST['firstname'] ?? '', ENT_QUOTES, 'UTF-8'));
$lastname  = trim(htmlspecialchars($_POST['lastname']  ?? '', ENT_QUOTES, 'UTF-8'));
$phone     = trim(htmlspecialchars($_POST['phone']     ?? '', ENT_QUOTES, 'UTF-8'));
$email     = trim($_POST['email']   ?? '');
$subject   = trim(htmlspecialchars($_POST['subject']  ?? '', ENT_QUOTES, 'UTF-8'));
$message   = trim(htmlspecialchars($_POST['message']  ?? '', ENT_QUOTES, 'UTF-8'));

$errors = [];
if (!$firstname)                    $errors[] = 'First name is required.';
if (!$lastname)                     $errors[] = 'Last name is required.';
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if (!$subject)                      $errors[] = 'Subject is required.';
if (!$message)                      $errors[] = 'Message is required.';
if (strlen($message) < 10)          $errors[] = 'Message must be at least 10 characters.';

if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

/* ---- Save to Database ---- */
try {
    $db   = getDB();
    $ip   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $db->prepare("
        INSERT INTO contact_messages (firstname, lastname, phone, email, subject, message, ip_address)
        VALUES (:firstname, :lastname, :phone, :email, :subject, :message, :ip)
    ");
    $stmt->execute([
        ':firstname' => $firstname,
        ':lastname'  => $lastname,
        ':phone'     => $phone ?: null,
        ':email'     => $email,
        ':subject'   => $subject,
        ':message'   => $message,
        ':ip'        => $ip,
    ]);

    $newId = $db->lastInsertId();

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
    exit;
}

/* ---- Optional: Send email notification to admin ---- */
/* Uncomment and configure if your server supports mail():
$to      = 'info@alburhan.com';
$headers = "From: noreply@alburhan.com\r\nReply-To: $email\r\nContent-Type: text/plain; charset=UTF-8";
$body    = "New contact message #$newId\n\n"
         . "Name:    $firstname $lastname\n"
         . "Email:   $email\n"
         . "Phone:   $phone\n"
         . "Subject: $subject\n\n"
         . "Message:\n$message\n\n"
         . "Received: " . date('Y-m-d H:i:s');
mail($to, "New Contact: $subject", $body, $headers);
*/

echo json_encode([
    'success' => true,
    'message' => 'Your message has been received. We will respond within 24 hours.',
    'id'      => $newId,
]);
exit;
?>