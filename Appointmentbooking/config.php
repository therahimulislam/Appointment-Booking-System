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
//
//  ✅ Safe to include multiple times — guarded against
//     duplicate function declarations and re-loading.
// ============================================================

// Guard: prevent any code below from running more than once
if (defined('_CONFIG_LOADED')) {
    return;
}
define('_CONFIG_LOADED', true);

// ── .env File Parser ─────────────────────────────────────────
if (!function_exists('loadEnvFile')) {
    /**
     * Parse a .env file and load variables into the environment.
     * Supports KEY=value, KEY="quoted value", # comments
     */
    function loadEnvFile(string $filePath): void {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and blank lines
            if ($line === '' || $line[0] === '#') {
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

            // Only set if not already in environment (env vars take precedence)
            if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

if (!function_exists('env')) {
    /**
     * Get an env variable with an optional default.
     */
    function env(string $key, string $default = ''): string {
        $val = $_ENV[$key] ?? getenv($key);
        return ($val !== false && $val !== '') ? $val : $default;
    }
}

// ── Load .env (real secrets) then .env.example (fallback) ────
$_cfgDir = __DIR__;
loadEnvFile($_cfgDir . '/.env');
loadEnvFile($_cfgDir . '/.env.example'); // only fills missing keys
unset($_cfgDir);

// ── Global Settings ──────────────────────────────────────────
date_default_timezone_set(env('TIMEZONE', 'Asia/Kolkata'));

// ── Cashfree Credentials ─────────────────────────────────────
if (!defined('CF_APP_ID'))     define('CF_APP_ID',     env('CF_APP_ID',     'YOUR_CASHFREE_APP_ID'));
if (!defined('CF_SECRET_KEY')) define('CF_SECRET_KEY', env('CF_SECRET_KEY', 'YOUR_CASHFREE_SECRET_KEY'));

// ── Environment: 'sandbox' | 'production' ────────────────────
if (!defined('CF_ENV'))        define('CF_ENV',        env('CF_ENV',        'sandbox'));

// ── App URL ──────────────────────────────────────────────────
if (!defined('APP_BASE_URL'))  define('APP_BASE_URL',  rtrim(env('APP_BASE_URL', 'http://localhost/project/Appointmentbooking'), '/'));

// ── Consultation Fee ─────────────────────────────────────────
if (!defined('CONSULTATION_FEE')) define('CONSULTATION_FEE', (float) env('CONSULTATION_FEE', '500'));

// ── Derived constants (auto-set from CF_ENV) ─────────────────
if (!defined('CF_API_URL')) {
    define('CF_API_URL', CF_ENV === 'production'
        ? 'https://api.cashfree.com/pg'
        : 'https://sandbox.cashfree.com/pg'
    );
}

if (!defined('CF_API_VERSION')) define('CF_API_VERSION', '2023-08-01');
if (!defined('CF_JS_SDK'))      define('CF_JS_SDK',      'https://sdk.cashfree.com/js/v3/cashfree.js');
