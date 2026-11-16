<?php
/** Local XAMPP bootstrap. Never alters existing accounts or drops database objects. */
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
$root = dirname(__DIR__);
$reconfigure = in_array('--reconfigure', $argv, true);
if (file_exists($root.'/.env') && ! $reconfigure) { fwrite(STDERR, ".env exists. Explicit --reconfigure is required to change database configuration.\n"); exit(1); }
$current = file_exists($root.'/.env') ? Dotenv\Dotenv::parse(file_get_contents($root.'/.env')) : [];
$db = new PDO('mysql:host=127.0.0.1;port=3306;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
foreach (['propertyme_bali_website', 'propertyme_bali_website_test'] as $name) { $db->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); }
$env = file_exists($root.'/.env') ? file_get_contents($root.'/.env') : file_get_contents($root.'/.env.example');
foreach (['DB_CONNECTION'=>'mysql','DB_HOST'=>'127.0.0.1','DB_PORT'=>'3306','DB_DATABASE'=>'propertyme_bali_website','DB_USERNAME'=>'root','DB_PASSWORD'=>''] as $key=>$value) {
    $env = preg_replace('/^'.preg_quote($key,'/').'=.*$/m', $key.'='.$value, $env);
}
if (empty($current['APP_KEY'])) { $env = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), $env); }
file_put_contents($root.'/.env', $env);
file_put_contents($root.'/.env.testing', str_replace(['APP_ENV=local','DB_DATABASE=propertyme_bali_website'], ['APP_ENV=testing','DB_DATABASE=propertyme_bali_website_test'], $env));
echo "Database configured: propertyme_bali_website (development), propertyme_bali_website_test (tests).\n";
echo "Local XAMPP connection: 127.0.0.1:3306; user root; empty database password.\n";
