<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$api = app()->make(App\Services\ApiService::class);

echo "--- getAngkatanList ---\n";
$res1 = $api->getAngkatanList(['kurikulum_prodi_id' => 1]);
print_r($res1);

echo "\n--- getNilaiCplPerAngkatan ---\n";
$res2 = $api->getNilaiCplPerAngkatan(['kurikulum_prodi_id' => 1, 'angkatan' => '2025']);
print_r($res2);

