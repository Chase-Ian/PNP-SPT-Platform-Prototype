<?php

namespace App\Support;

/**
 * The PNP office an account belongs to (users.region), from config/pnp.php:
 * the 18 Police Regional Offices by their official short names (NCRPO,
 * PRO 1 to PRO 13, PRO BAR, PRO COR, PRO NIR) plus National Headquarters.
 */
final class PnpRegions
{
    /** The labels used before the official names (November 2026), for rolling back. */
    public const PREVIOUS_LABELS = [
        'NCRPO - National Capital Region Police Office' => 'PRO NCR - National Capital Region (NCR)',
        'PRO 1 - Ilocos Region' => 'PRO 1 - Region 1 - Ilocos Region',
        'PRO 2 - Cagayan Valley' => 'PRO 2 - Region 2 - Cagayan Valley',
        'PRO 3 - Central Luzon' => 'PRO 3 - Region 3 - Central Luzon',
        'PRO 4A - CALABARZON' => 'PRO 4A - Region 4A - CALABARZON',
        'PRO 4B - MIMAROPA' => 'PRO 4B - Region 4B - MIMAROPA',
        'PRO 5 - Bicol Region' => 'PRO 5 - Region 5 - Bicol Region',
        'PRO 6 - Western Visayas' => 'PRO 6 - Region 6 - Western Visayas',
        'PRO 7 - Central Visayas' => 'PRO 7 - Region 7 - Central Visayas',
        'PRO 8 - Eastern Visayas' => 'PRO 8 - Region 8 - Eastern Visayas',
        'PRO 9 - Zamboanga Peninsula' => 'PRO 9 - Region 9 - Zamboanga Peninsula',
        'PRO 10 - Northern Mindanao' => 'PRO 10 - Region 10 - Northern Mindanao',
        'PRO 11 - Davao Region' => 'PRO 11 - Region 11 - Davao Region',
        'PRO 12 - SOCCSKSARGEN' => 'PRO 12 - Region 12 - SOCCSKSARGEN',
        'PRO 13 - Caraga' => 'PRO 13 - Region 13 - Caraga Region',
        'PRO BAR - Bangsamoro Autonomous Region in Muslim Mindanao' => 'PRO BARMM - Bangsamoro Autonomous Region (BARMM)',
        'PRO COR - Cordillera Administrative Region' => 'PRO CAR - Cordillera Administrative Region (CAR)',
    ];

    /**
     * Words that identify an office in old or hand-typed values, checked in
     * this order, as whole words. The key is the start of the official label.
     */
    private const KEYWORDS = [
        'NCRPO' => ['NCRPO', 'NCR', 'NATIONAL CAPITAL', 'METRO MANILA'],
        'PRO NIR' => ['NIR', 'NEGROS ISLAND', 'REGION 18', 'PRO 18'],
        'PRO BAR' => ['BARMM', 'BANGSAMORO', 'ARMM', 'PRO BAR'],
        'PRO COR' => ['CORDILLERA', 'CAR', 'COR', 'PROCOR'],
        'NHQ Camp Crame' => ['NHQ', 'NATIONAL HEADQUARTERS', 'CAMP CRAME'],
        'PRO 4A' => ['CALABARZON', 'IV-A'],
        'PRO 4B' => ['MIMAROPA', 'IV-B'],
        'PRO 1' => ['ILOCOS'],
        'PRO 2' => ['CAGAYAN VALLEY'],
        'PRO 3' => ['CENTRAL LUZON'],
        'PRO 5' => ['BICOL'],
        'PRO 6' => ['WESTERN VISAYAS'],
        'PRO 7' => ['CENTRAL VISAYAS'],
        'PRO 8' => ['EASTERN VISAYAS'],
        'PRO 9' => ['ZAMBOANGA PENINSULA'],
        'PRO 10' => ['NORTHERN MINDANAO'],
        'PRO 11' => ['DAVAO'],
        'PRO 12' => ['SOCCSKSARGEN'],
        'PRO 13' => ['CARAGA'],
    ];

    /** @return list<string> */
    public static function all(): array
    {
        return config('pnp.regions');
    }

    /**
     * The official label for a saved value (an old label or a hand-typed
     * variant such as "NCR"), or null when it can't be told apart.
     */
    public static function normalize(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (in_array($value, self::all(), true)) {
            return $value;
        }

        $upper = strtoupper($value);
        foreach (self::KEYWORDS as $prefix => $words) {
            foreach ($words as $word) {
                if (preg_match('/(?<![A-Z0-9])'.preg_quote($word, '/').'(?![A-Z0-9])/', $upper) === 1) {
                    return self::byPrefix($prefix);
                }
            }
        }

        // "PRO 10 - ...", "PRO4A", "Region 3"
        if (preg_match('/^PRO\s*-?\s*(\d{1,2}[AB]?)(?![0-9])/', $upper, $m) === 1
            || preg_match('/\bREGION\s*(\d{1,2}[AB]?)(?![0-9])/', $upper, $m) === 1) {
            return self::byPrefix('PRO '.$m[1]);
        }

        return null;
    }

    /**
     * The office's short code for certificate control numbers: NCRPO, PRO1 to
     * PRO13 (PRO4A, PRO4B), PROBAR, PROCOR, PRONIR, NHQ. Each office has its
     * own. Old labels are recognised like normalize(); an unknown or missing
     * office gives REG.
     */
    public static function code(?string $region): string
    {
        $label = self::normalize($region);
        if ($label === null) {
            return 'REG';
        }

        $short = strtoupper(explode(' - ', $label, 2)[0]);

        return str_starts_with($short, 'NHQ') ? 'NHQ' : preg_replace('/[^A-Z0-9]/', '', $short);
    }

    private static function byPrefix(string $prefix): ?string
    {
        foreach (self::all() as $label) {
            if (str_starts_with($label, $prefix.' - ')) {
                return $label;
            }
        }

        return null;
    }
}
