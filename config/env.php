<?php
/**
 * Lightweight environment loader.
 *
 * Add local values to the project root .env file:
 * RAZORPAY_KEY_ID=your_key_id
 * RAZORPAY_KEY_SECRET=your_key_secret
 */

if (!function_exists('load_env_file')) {
    function load_env_file($path)
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));

            if ($key === '') {
                continue;
            }

            $value = trim($value, "\"'");

            if (getenv($key) === false) {
                putenv($key . '=' . $value);
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

if (!function_exists('env_value')) {
    function env_value($key, $default = '')
    {
        $value = getenv($key);

        if ($value === false && isset($_ENV[$key])) {
            $value = $_ENV[$key];
        }

        if ($value === false && isset($_SERVER[$key])) {
            $value = $_SERVER[$key];
        }

        return $value === false || $value === '' ? $default : $value;
    }
}

load_env_file(dirname(__DIR__) . '/.env');

define('RAZORPAY_KEY_ID', env_value('RAZORPAY_KEY_ID'));
define('RAZORPAY_KEY_SECRET', env_value('RAZORPAY_KEY_SECRET'));
define('RAZORPAY_CURRENCY', env_value('RAZORPAY_CURRENCY', 'INR'));
define('RAZORPAY_BUSINESS_NAME', env_value('RAZORPAY_BUSINESS_NAME', 'Nutri Afghan'));
