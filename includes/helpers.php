<?php

if (!function_exists('e')) {
    function e($valeur): string
    {
        return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('format_date_fr')) {
    function format_date_fr(?string $date, bool $avecHeure = false): string
    {
        if ($date === null || trim($date) === '') {
            return '';
        }

        try {
            $dateObj = new DateTime($date);
        } catch (Throwable $e) {
            return (string) $date;
        }

        return $dateObj->format($avecHeure ? 'd/m/Y H:i' : 'd/m/Y');
    }
}

if (!function_exists('format_mois_fr')) {
    function format_mois_fr(?string $mois): string
    {
        if ($mois === null || !preg_match('/^\d{4}-\d{2}$/', $mois)) {
            return (string) $mois;
        }

        $dateObj = DateTime::createFromFormat('Y-m', $mois);
        if (!$dateObj) {
            return (string) $mois;
        }

        $moisFrancais = [
            1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
            5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
            9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
        ];

        return ucfirst($moisFrancais[(int) $dateObj->format('n')] . ' ' . $dateObj->format('Y'));
    }
}

if (!function_exists('badge_statut_libelle')) {
    function badge_statut_libelle(string $statut): string
    {
        return match ($statut) {
            'saisie' => 'En saisie',
            'transmise' => 'En validation',
            'validee' => 'Validée',
            'refusee' => 'Refusée',
            'remboursee' => 'Remboursée',
            default => ucfirst($statut),
        };
    }
}

if (!function_exists('badge_statut_classe')) {
    function badge_statut_classe(string $statut): string
    {
        return match ($statut) {
            'transmise' => 'badge-transmise',
            'validee', 'remboursee' => 'badge-validee',
            'refusee' => 'badge-refusee',
            default => 'badge-saisie',
        };
    }
}


if (!function_exists('pagination_infos')) {
    function pagination_infos(int $total, int $page, int $parPage): array
    {
        $parPage = max(1, $parPage);
        $totalPages = max(1, (int) ceil($total / $parPage));
        $page = min(max(1, $page), $totalPages);
        return [
            'total' => $total,
            'page' => $page,
            'par_page' => $parPage,
            'total_pages' => $totalPages,
            'offset' => ($page - 1) * $parPage,
        ];
    }
}

if (!function_exists('url_avec_params')) {
    function url_avec_params(array $params): string
    {
        $base = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '';
        $query = array_merge($_GET, $params);
        foreach ($query as $k => $v) {
            if ($v === null || $v === '') unset($query[$k]);
        }
        return $base . ($query ? '?' . http_build_query($query) : '');
    }
}

if (!function_exists('badge_niveau_classe')) {
    function badge_niveau_classe(string $niveau): string
    {
        return match ($niveau) {
            'warning' => 'text-bg-warning',
            'error' => 'text-bg-danger',
            default => 'text-bg-info',
        };
    }
}
