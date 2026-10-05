<?php
// Load before output on each page containing the shared form.
ini_set('display_errors', '0');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'use_strict_mode' => true,
        'cookie_httponly' => true,
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'cookie_samesite' => 'Lax',
    ]);
}
$contactTopics = [
    'training' => 'Training',
    'skill-assessment' => 'Skill Assessment',
    'partnership' => 'Partnership',
    'application' => 'Application',
    'venue-hire' => 'Venue Hire',
];
$contactValues = array_fill_keys(['name', 'email', 'phone', 'topic', 'message'], '');
$contactErrors = [];
$contactSuccess = false;
if (empty($_SESSION['contact_csrf'])) {
    $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
}

function contactDatabase(): PDO
{
    $path = __DIR__ . '/../config.local.php';
    if (!is_file($path)) throw new RuntimeException('Contact database configuration missing.');
    $config = require $path;
    $db = is_array($config) ? ($config['db'] ?? null) : null;
    if (!is_array($db) || empty($db['host']) || empty($db['database']) || empty($db['username']) || !isset($db['password'])) {
        throw new RuntimeException('Contact database configuration incomplete.');
    }
    // Configuration is trusted, but prevent DSN separators being interpreted as options.
    if (preg_match('/[;\x00-\x1f]/', $db['host'] . $db['database'])) {
        throw new RuntimeException('Invalid database configuration.');
    }
    $port = filter_var($db['port'] ?? 3306, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    if ($port === false) throw new RuntimeException('Invalid database port.');
    return new PDO('mysql:host=' . $db['host'] . ';port=' . $port . ';dbname=' . $db['database'] . ';charset=utf8mb4', $db['username'], $db['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function validateContact(array $input, array $topics): array
{
    $values = [];
    $errors = [];
    foreach (['name' => 100, 'email' => 255, 'phone' => 30, 'topic' => 100, 'message' => 4000] as $field => $max) {
        $raw = $input[$field] ?? '';
        $validText = is_string($raw) && preg_match('//u', $raw) === 1 && !str_contains($raw, "\0");
        $value = $validText ? trim($raw) : '';
        if (!$validText || mb_strlen($value, 'UTF-8') > $max) {
            $errors[$field] = ucfirst($field) . ' must be valid text of no more than ' . $max . ' characters.';
            $value = $validText ? mb_substr($value, 0, $max, 'UTF-8') : '';
        } elseif ($field !== 'phone' && $value === '') {
            $errors[$field] = 'Please enter your ' . $field . '.';
        }
        $values[$field] = $value;
    }
    if (!isset($errors['email']) && !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (!isset($topics[$values['topic']])) {
        $errors['topic'] = 'Please choose one of the available topics.';
        $values['topic'] = '';
    }
    return [$values, $errors];
}

// The session lock serializes submissions; rotating the token after insertion rejects retries.
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'contact.php') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        [$contactValues, $contactErrors] = validateContact($_POST, $contactTopics);
        $token = $_POST['csrf_token'] ?? '';
        if (!is_string($token) || !hash_equals($_SESSION['contact_csrf'], $token)) {
            $contactErrors['form'] = 'Your form has expired. Please review your message and try again.';
            http_response_code(403);
        } elseif ($contactErrors) {
            http_response_code(422);
        } else {
            try {
                $pdo = contactDatabase();
                $statement = $pdo->prepare('INSERT INTO contact_messages (name, email, phone, topic, message) VALUES (:name, :email, :phone, :topic, :message)');
                $statement->execute([
                    'name' => $contactValues['name'],
                    'email' => $contactValues['email'],
                    'phone' => $contactValues['phone'] === '' ? null : $contactValues['phone'],
                    'topic' => $contactValues['topic'],
                    'message' => $contactValues['message'],
                ]);
                $_SESSION['contact_csrf'] = bin2hex(random_bytes(32));
                $_SESSION['contact_success'] = true;
                header('Location: contact.php#contact', true, 303);
                exit;
            } catch (Throwable $error) {
                // No verified private logger exists: do not write credentials or personal data to public files.
                $contactErrors['form'] = "We couldn't send your message. Please try again.";
                http_response_code(503);
            }
        }
    } else {
        $contactSuccess = !empty($_SESSION['contact_success']);
        unset($_SESSION['contact_success']);
    }
}
