<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$api = app(App\Services\ApiService::class);
$res = $api->getMahasiswas();
echo json_encode($res);
