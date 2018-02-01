<!-- localhost: ooframe.loca -->
<?php
define("DD", realpath("../"));

require DD . "/vendor/autoload.php";

// Update
$u_data = [
    'name' => 'Ko Aung',
    'address' => 'Zawana'
];
DB::table('students')->where("name", "=", "Ko Ko")->update($u_data);

//Insert
$data = [
    'name' => 'Ko Ko',
    'address' => 'Hledan'
];
// DB::table('students')->insert($data);

// Delete
// DB::table('students')->where('id', '=', 9)->delete();

//Truncate
// DB::table('students')->truncate();

// Select All
$students = DB::table('students')->columnsAll()->get();
var_dump($students);

// Group By
$students = DB::table('students')->columnsAll()->groupBy("name")->get();
var_dump($students);

// Select by columns && filter && Order By
$student = DB::table('students')->selectColumns('id', 'name', 'address')->where('name', 'like', '%Aung%')->orderBy("id", "DESC")->get();
var_dump($student);



 ?>
