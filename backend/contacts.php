<?php
// contact_handler.php

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Inclure votre fichier de connexion à la base de données
    require_once 'C:\wamp64\www\fitness-club\backend\connexion.php';
    // Remplacez 'chemin/vers/votre_fichier_connexion.php' par le chemin réel
    
    // Récupérer et nettoyer les données du formulaire
    $nom = mysqli_real_escape_string($conn, trim($_POST['nom']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
    
    // Valider les données (vous pouvez ajouter plus de validations)
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le champ nom est requis.";
    }
    
    if (empty($email)) {
        $errors[] = "Le champ email est requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    if (empty($message)) {
        $errors[] = "Le champ message est requis.";
    }
    
    // Si aucune erreur, procéder à l'insertion
    if (empty($errors)) {
        // Préparer la requête SQL
        $sql = "INSERT INTO contacts (nom, email, sujet, message, date_envoi) 
                VALUES (?, ?, ?, ?, NOW())";
        
        // Préparer la déclaration
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            // Lier les paramètres
            mysqli_stmt_bind_param($stmt, "ssss", $nom, $email, $message);
            
            // Exécuter la déclaration
            if (mysqli_stmt_execute($stmt)) {
                // Succès - rediriger ou afficher un message
                header("Location: merci.html"); // Rediriger vers une page de remerciement
                exit();
            } else {
                $errors[] = "Une erreur s'est produite lors de l'enregistrement. Veuillez réessayer.";
            }
            
            // Fermer la déclaration
            mysqli_stmt_close($stmt);
        } else {
            $errors[] = "Une erreur de préparation de requête s'est produite.";
        }
    }
    
    // Si on arrive ici, c'est qu'il y a eu des erreurs
    // Vous pouvez les afficher ou les enregistrer dans un log
    foreach ($errors as $error) {
        echo "<p>Erreur: $error</p>";
    }
    
    // Fermer la connexion
    mysqli_close($conn);
} else {
    // Si quelqu'un essaie d'accéder directement à ce script sans soumettre le formulaire
    header("Location: contact.php"); // Rediriger vers la page de contact
    exit();
}
?>