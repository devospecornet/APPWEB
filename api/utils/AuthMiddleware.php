<?php
require_once __DIR__ . '/../../configuration/base.php';
require_once __DIR__ . '/ReponseJson.php';

class AuthMiddleware
{
    public static function recupererAuthorization(): string
    {
        $headers = function_exists('getallheaders') ? (getallheaders() ?: []) : [];
        if (isset($headers['Authorization'])) return trim((string)$headers['Authorization']);
        if (isset($headers['authorization'])) return trim((string)$headers['authorization']);
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) return trim((string)$_SERVER['HTTP_AUTHORIZATION']);
        if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) return trim((string)$_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        if (function_exists('apache_request_headers')) {
            $apache = apache_request_headers();
            if (isset($apache['Authorization'])) return trim((string)$apache['Authorization']);
            if (isset($apache['authorization'])) return trim((string)$apache['authorization']);
        }
        return '';
    }

    public static function utilisateurConnecteApi(): array
    {
        $authorization = self::recupererAuthorization();

        if (!str_starts_with($authorization, 'Bearer ')) {
            ReponseJson::envoyer(['succes' => false, 'message' => 'Jeton Bearer manquant.'], 401);
        }

        $jeton = trim(substr($authorization, 7));
        if ($jeton === '') {
            ReponseJson::envoyer(['succes' => false, 'message' => 'Jeton Bearer manquant.'], 401);
        }

        $stmt = Base::connexion()->prepare(
            'SELECT aj.*, u.nom, u.prenom, u.email, u.role, u.est_approuve
             FROM api_jetons aj
             INNER JOIN utilisateurs u ON u.id = aj.id_utilisateur
             WHERE aj.jeton = :jeton
             LIMIT 1'
        );
        $stmt->execute(['jeton' => $jeton]);
        $ligne = $stmt->fetch();

        if (!$ligne) {
            ReponseJson::envoyer(['succes' => false, 'message' => 'Jeton invalide.'], 401);
        }

        if (strtotime((string) $ligne['date_expiration']) < time()) {
            ReponseJson::envoyer(['succes' => false, 'message' => 'Jeton expiré.'], 401);
        }

        return $ligne;
    }
}
