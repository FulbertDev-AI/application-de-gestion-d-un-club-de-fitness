<?php
    $host ="localhost";
    $user = "root";
    $pwd = "";
    $dbname ="fitness_club";

    $conn = mysqli_connect($host, $user, $pwd, $dbname);

    if(!$conn){
        die("Erreur de connection à la base de données");
    }
?>