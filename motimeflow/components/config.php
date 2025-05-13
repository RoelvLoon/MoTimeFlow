<?php

session_start();



// Timezone instellen

date_default_timezone_set('Europe/Amsterdam');



// Dagen

$days = [

    1 => 'maandag',

    2 => 'dinsdag',

    3 => 'woensdag',

    4 => 'donderdag',

    5 => 'vrijdag',

    6 => 'zaterdag',

    7 => 'zondag'

];



// Tijd dingen

$datumVandaag = date("Y-m-d");

$time = date("H:i");

$dayName = date("l");

$day_of_week = date('N', strtotime($dayName));



function e($str) {

    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');

}



// Connectie met DB

// ...



// $conn=mysqli_connect($host, $user, $password, $db)

// or die('Error: connectie met de database is niet gelukt!<br>'.mysqli_connect_error());



// function fnCloseDb($conn){

//     if (!$conn==false)

//     {

//         mysqli_close($conn)

//         or die('Sluiten MySQL-db niet gelukt...');

//     }

// }



// // Connectie met DB

...



try {

    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // set the PDO error mode to exception

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch(PDOException $e) {

    die("<p class='text-danger'> Connectie met database mislukt: <b>" . $e->getMessage() . "</b></p>");

}



?>