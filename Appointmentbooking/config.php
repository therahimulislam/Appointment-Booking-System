<?php
// ============================================================
//  config.php  —  Environment Loader  (SAFE TO COMMIT)
// ============================================================
//  Reads credentials from .env file (gitignored).
//  No Composer / no external libraries needed.
//
//  File priority (first found wins):
//    1. .env          ← your real secrets (never committed)
//    2. .env.example  ← fallback placeholder values
// ============================================================

/**
 * Parse a .env file and load variables into the environment.
 * Supports:
 *   KEY=value
 *   KEY="quoted value"
 *   # comments
 *   blank lines
 */
function loadEnvFile(string $filePath): void {
    if (!file_exists($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip comments and blank lines
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Split on first = only
        $eqPos = strpos($line, '=');
        if ($eqPos === false) {
            continue;
        }

        $key   = trim(substr($line, 0, $eqPos));
        $value = trim(substr($line, $eqPos + 1));

        // Strip surrounding quotes (" or ')
        if (
            strlen($value) >= 2 &&
            (
                ($value[0] === '"'  && $value[-1] === '"') ||
                ($value[0] === "'"  && $value[-1] === "'")
            )
        ) {
            $value = substr($value, 1, -1);
        }

        // Only set if not already defined (environment takes precedence)
        if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

// ── Load .env (real secrets) then .env.example (fallback) ────
$dir = __DIR__;
loadEnvFile($dir . '/.env');
loadEnvFile($dir . '/.env.example');  // only fills missing keys

/**
 * Helper: get an env variable with an optional default.
 */
function env(string $key, string $default = ''): string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ── Cashfree Credentials ─────────────────────────────────────
define('CF_APP_ID',     env('CF_APP_ID',     'YOUR_CASHFREE_APP_ID'));
define('CF_SECRET_KEY', env('CF_SECRET_KEY', 'YOUR_CASHFREE_SECRET_KEY'));

// ── Environment: 'sandbox' | 'production' ────────────────────
define('CF_ENV', env('CF_ENV', 'sandbox'));

// ── App URL ──────────────────────────────────────────────────
define('APP_BASE_URL', rtrim(env('APP_BASE_URL', 'http://localhost/project/Appointmentbooking'), '/'));

// ── Consultation Fee ─────────────────────────────────────────
define('CONSULTATION_FEE', (float) env('CONSULTATION_FEE', '500'));

// ── Derived constants (auto-set from CF_ENV) ─────────────────
define('CF_API_URL', CF_ENV === 'production'
    ? 'https://api.cashfree.com/pg'
    : 'https://sandbox.cashfree.com/pg'
);

define('CF_API_VERSION', '2023-08-01');

define('CF_JS_SDK', 'https://sdk.cashfree.com/js/v3/cashfree.js');
