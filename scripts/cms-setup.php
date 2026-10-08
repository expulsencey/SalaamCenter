<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('display_errors', '0');
require __DIR__ . '/../includes/cms.php';
$stage = 'configuration';
try {
    $configFile = getenv('SALAAM_CMS_CONFIG') ?: __DIR__ . '/../config.local.php';
    if (!is_readable($configFile)) throw new InvalidArgumentException('CMS configuration is missing or unreadable. Save the private config.local.php file using config.example.php as a template.');
    if (filesize($configFile) === 0) throw new InvalidArgumentException('CMS configuration file is empty (0 bytes). Save the PHP configuration array in config.local.php; editing config.example.php alone does not configure the CMS.');
    if (!extension_loaded('pdo_mysql')) throw new InvalidArgumentException('The CLI PHP executable does not have the PDO MySQL driver available. Check which PHP executable is being used.');
    $stage = 'database connection/configuration';
    $pdo = cmsDatabase();
    $command = $argv[1] ?? '';
    if ($command === 'migrate') {
        $stage = 'schema loading';
        $schemaFile = __DIR__ . '/../database/cms.sql';
        if (!is_readable($schemaFile)) throw new InvalidArgumentException('The database/cms.sql schema file is missing or unreadable.');
        $schema = file_get_contents($schemaFile);
        if ($schema === false || trim($schema) === '') throw new InvalidArgumentException('The database/cms.sql schema file could not be read or is empty.');
        foreach (explode(';', $schema) as $index => $sql) {
            if (trim($sql) === '') continue;
            $stage = 'migration statement ' . ($index + 1);
            $pdo->exec($sql);
        }
        echo "CMS tables are ready. Existing data was preserved.\n";
    } elseif ($command === 'import-courses') {
        $stage = 'course import';
        require_once __DIR__ . '/../includes/course-store.php';
        echo json_encode(courseImport(), JSON_THROW_ON_ERROR) . "\n";
    } elseif (in_array($command, ['create-admin','reset-password'], true)) {
        $stage = 'administrator setup';
        $input = json_decode(stream_get_contents(STDIN), true, 8, JSON_THROW_ON_ERROR);
        $email = strtolower(cmsText($input['email'] ?? '', 254));
        $password = $input['password'] ?? null;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !is_string($password) || strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) throw new InvalidArgumentException('Use a valid email and a password of 12 to 72 bytes.');
        // Match the unknown-account verification cost used by cmsLogin().
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        if ($command === 'create-admin') {
            $name = cmsText($input['display_name'] ?? '', 100);
            if ($name === '') throw new InvalidArgumentException('A display name is required.');
            $q = $pdo->prepare('INSERT INTO cms_users (email,display_name,password_hash) VALUES (?,?,?)'); $q->execute([$email,$name,$hash]);
        } else {
            $q = $pdo->prepare('UPDATE cms_users SET password_hash=? WHERE email=?'); $q->execute([$hash,$email]);
            if (!$q->rowCount()) throw new InvalidArgumentException('No administrator was changed.');
        }
        echo "Administrator credentials saved securely.\n";
    } else throw new InvalidArgumentException('Usage: cms-setup.php migrate | import-courses | create-admin | reset-password. Account commands read JSON from standard input.');
} catch (InvalidArgumentException $e) { fwrite(STDERR, $e->getMessage() . "\n"); exit(1); }
catch (Throwable $e) {
    // CLI-only allowlisted diagnostics: never print exception messages, SQL or credentials.
    $code = $e instanceof PDOException ? (int) ($e->errorInfo[1] ?? 0) : 0;
    $hint = match ($code) {
        1045, 1698 => 'MySQL rejected authentication. Check the private username/password and allowed host.',
        1049 => 'The configured database does not exist on this MySQL server.',
        1044, 1142, 1143, 1227 => 'The database account lacks the required permission for this operation.',
        2002, 2003 => 'MySQL could not be reached. Check that it is running and that host/port are correct.',
        1064 => 'MySQL rejected the schema syntax. Check database/cms.sql against the server version.',
        1062 => 'A unique value already exists. Account creation does not overwrite an existing administrator.',
        1071, 1709 => 'The server rejected an index size. Check the server version and table format.',
        default => 'Check that the private configuration returns the expected db array and that the schema matches the server.'
    };
    fwrite(STDERR, 'CMS setup failed during ' . $stage . ($code ? ' (MySQL code ' . $code . ')' : '') . '. ' . $hint . "\n");
    exit(1);
}
