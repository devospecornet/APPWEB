<?php

if (!function_exists('demarrerSessionSiNecessaire')) {
    function demarrerSessionSiNecessaire(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
}

if (!function_exists('verifierSession')) {
    function verifierSession(): void
    {
        demarrerSessionSiNecessaire();

        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - (int) $_SESSION['LAST_ACTIVITY']) > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['message_connexion'] = "Votre session a expiré après 15 minutes d'inactivité.";
            header('Location: ' . asset_url('index.php'));
            exit;
        }

        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

if (!function_exists('exigerConnexion')) {
    function exigerConnexion(array $rolesAutorises = []): void
    {
        verifierSession();

        if (!isset($_SESSION['utilisateur'])) {
            header('Location: ' . asset_url('index.php'));
            exit;
        }

        if ($rolesAutorises !== []) {
            $role = $_SESSION['utilisateur']['role'] ?? '';
            if (!in_array($role, $rolesAutorises, true)) {
                header('Location: ' . asset_url('tableau_bord.php'));
                exit;
            }
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        demarrerSessionSiNecessaire();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('verifierCsrfOuEchouer')) {
    function verifierCsrfOuEchouer(): void
    {
        demarrerSessionSiNecessaire();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $token = $_POST['csrf_token'] ?? '';
        if ($token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(400);
            echo 'Jeton CSRF invalide.';
            exit;
        }
    }
}

if (!function_exists('limiterTentativesConnexion')) {
    function limiterTentativesConnexion(string $email): bool
    {
        demarrerSessionSiNecessaire();
        $cle = 'login_attempts_' . md5(strtolower(trim($email)) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'local'));
        $entree = $_SESSION[$cle] ?? ['count' => 0, 'time' => time()];
        if ((time() - (int) $entree['time']) > 900) {
            $entree = ['count' => 0, 'time' => time()];
        }
        if ((int) $entree['count'] >= 5) {
            $_SESSION[$cle] = $entree;
            return false;
        }
        $entree['count']++;
        $_SESSION[$cle] = $entree;
        return true;
    }
}

if (!function_exists('reinitialiserTentativesConnexion')) {
    function reinitialiserTentativesConnexion(string $email): void
    {
        demarrerSessionSiNecessaire();
        $cle = 'login_attempts_' . md5(strtolower(trim($email)) . '|' . ($_SERVER['REMOTE_ADDR'] ?? 'local'));
        unset($_SESSION[$cle]);
    }
}
