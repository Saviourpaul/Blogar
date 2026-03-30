<?php
// includes/auth.php
// Session management + CSRF token generation/validation.

declare(strict_types=1);

class Auth
{
    /**
     * Boot a secure session.
     * Call this at the top of EVERY page/endpoint — once.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => true,          // HTTPS only (disable locally if needed)
                'httponly' => true,           // JS cannot read this cookie
                'samesite' => 'Strict',       // blocks CSRF from cross-site requests
            ]);
            session_start();
        }
    }

    /**
     * Returns the currently logged-in user's ID, or null.
     */
    public static function userId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    /**
     * Returns true if a user is logged in.
     */
    public static function check(): bool
    {
        return self::userId() !== null;
    }

    /**
     * Hard-require a logged-in user.
     * For API endpoints: sends 401 JSON and exits.
     * For page endpoints: redirects to login.
     */
    public static function requireLogin(bool $isApi = false): void
    {
        if (!self::check()) {
            if ($isApi) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'You must be logged in.']);
                exit;
            }
            header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    /**
     * Log a user in. Call this only after verifying credentials.
     */
    public static function login(int $userId): void
    {
        session_regenerate_id(true);   // prevent session fixation
        $_SESSION['user_id']   = $userId;
        $_SESSION['csrf_token'] = self::generateCsrfToken();
    }

    /**
     * Destroy the session completely.
     */
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '',
                time() - 42000,
                $params['path'],   $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    // ── CSRF ─────────────────────────────────────────────────────────────────

    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            return self::generateCsrfToken();
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate the CSRF token from the request.
     * Accepts token from POST body OR X-CSRF-Token header (for AJAX).
     */
    public static function verifyCsrf(): bool
    {
        $submitted = $_POST['csrf_token']
            ?? $_SERVER['HTTP_X_CSRF_TOKEN']
            ?? '';

        $stored = $_SESSION['csrf_token'] ?? '';

        // hash_equals prevents timing attacks
        return $stored !== '' && hash_equals($stored, $submitted);
    }

    /**
     * Combine CSRF check with API error response.
     */
    public static function requireCsrf(): void
    {
        if (!self::verifyCsrf()) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid CSRF token.']);
            exit;
        }
    }
}