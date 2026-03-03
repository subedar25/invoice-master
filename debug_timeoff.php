<?php
// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Models\TimeOffRequest;

echo "Checking TimeOffRequest count...\n";
try {
    $count = TimeOffRequest::count();
    echo "Total Records: " . $count . "\n";
    
    if($count > 0) {
        $first = TimeOffRequest::first();
        echo "First Record:\n";
        print_r($first->toArray());
        
        echo "\nCasting Check:\n";
        echo "Start Time Type: " . gettype($first->start_time) . "\n";
        if($first->start_time instanceof \Carbon\Carbon) {
             echo "Start Time Format: " . $first->start_time->format('Y-m-d H:i:s') . "\n";
        } else {
             echo "Start Time is NOT Carbon instance.\n";
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
