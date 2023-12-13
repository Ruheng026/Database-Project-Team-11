<?php
require 'eloquent.php';

use Illuminate\Database\Capsule\Manager as DB;
?>

<?php
$selectedCounty = $_POST['schcounty'] ?? '南投縣';

// Fetch schools for the selected county
$schoolsForCounty = DB::table('school')
    ->where('schcounty', $selectedCounty)
    ->get(['school_id', 'schname']);

// Generate HTML options for the schname dropdown
$options = '';
foreach ($schoolsForCounty as $school) {
    $options .= "<option value='{$school->school_id}'>{$school->schname}</option>";
}

// Return the options as a response to the AJAX request
echo $options;
?>
