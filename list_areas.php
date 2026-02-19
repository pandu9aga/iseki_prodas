<?php
// removing require __DIR__ . '/vendor/autocomplete.php';

// Actually, let's use a Laravel-friendly way.
use Illuminate\Support\Facades\DB;
use App\Models\Efficiency_Area;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$areas = DB::connection('efficiency')->table('areas')->get();
foreach ($areas as $area) {
    echo "ID: {$area->Id_Area}, Name: {$area->Name_Area}\n";
}
