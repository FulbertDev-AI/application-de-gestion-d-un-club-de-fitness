<?php

// Requête SQL pour récupérer les membres actifs
$sql = "SELECT 
            u.id, 
            u.nom, 
            u.prenom, 
            u.email, 
            u.date_naissance,
            u.telephone,
            a.type_abonnement,
            a.prix,
            ua.date_debut,
            ua.date_fin
        FROM 
            utilisateurs u
        JOIN 
            utilisateur_abonnements ua ON u.id = ua.utilisateur_id
        JOIN 
            abonnements a ON ua.abonnement_id = a.id
        WHERE 
            ua.date_fin >= CURDATE() 
            AND ua.date_debut <= CURDATE()
            AND ua.statut_paiement = 'payé'
        ORDER BY 
            u.nom, u.prenom";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des membres actifs - Fitzone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card-member {
            transition: transform 0.3s;
        }
        .card-member:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .badge-abonnement {
            font-size: 0.8rem;
            padding: 5px 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Liste des membres actifs</h1>
        
        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total membres actifs</h5>
                        <p class="card-text display-6">
                            <?php 
                                $count_sql = "SELECT COUNT(*) as total FROM utilisateur_abonnements 
                                            WHERE date_fin >= CURDATE() AND date_debut <= CURDATE() 
                                            AND statut_paiement = 'payé'";
                                $count_result = $conn->query($count_sql);
                                $count_row = $count_result->fetch_assoc();
                                echo $count_row['total'];
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h5 class="card-title">Abonnements adultes</h5>
                        <p class="card-text display-6">
                            <?php 
                                $adult_sql = "SELECT COUNT(*) as total FROM utilisateur_abonnements ua
                                            JOIN abonnements a ON ua.abonnement_id = a.id
                                            WHERE ua.date_fin >= CURDATE() 
                                            AND ua.date_debut <= CURDATE()
                                            AND ua.statut_paiement = 'payé'
                                            AND a.type_abonnement = 'adulte'";
                                $adult_result = $conn->query($adult_sql);
                                $adult_row = $adult_result->fetch_assoc();
                                echo $adult_row['total'];
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h5 class="card-title">Abonnements enfants</h5>
                        <p class="card-text display-6">
                            <?php 
                                $child_sql = "SELECT COUNT(*) as total FROM utilisateur_abonnements ua
                                            JOIN abonnements a ON ua.abonnement_id = a.id
                                            WHERE ua.date_fin >= CURDATE() 
                                            AND ua.date_debut <= CURDATE()
                                            AND ua.statut_paiement = 'payé'
                                            AND a.type_abonnement = 'enfant'";
                                $child_result = $conn->query($child_sql);
                                $child_row = $child_result->fetch_assoc();
                                echo $child_row['total'];
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Barre de recherche et filtres -->
        <div class="row mb-4">
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un membre...">
            </div>
            <div class="col-md-6">
                <select id="filterAbonnement" class="form-select">
                    <option value="all">Tous les abonnements</option>
                    <option value="enfant">Enfant</option>
                    <option value="adulte">Adulte</option>
                    <option value="famille">Famille</option>
                    <option value="premium">Premium</option>
                </select>
            </div>
        </div>
        
        <!-- Liste des membres -->
        <div class="row" id="membersContainer">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Calcul de l'âge
                    $dateNaissance = new DateTime($row['date_naissance']);
                    $aujourdhui = new DateTime();
                    $age = $aujourdhui->diff($dateNaissance)->y;
                    
                    // Déterminer la couleur du badge selon le type d'abonnement
                    $badge_class = '';
                    switch($row['type_abonnement']) {
                        case 'enfant': $badge_class = 'bg-info'; break;
                        case 'adulte': $badge_class = 'bg-primary'; break;
                        case 'famille': $badge_class = 'bg-success'; break;
                        case 'premium': $badge_class = 'bg-warning text-dark'; break;
                        default: $badge_class = 'bg-secondary';
                    }
                    
                    // Jours restants avant l'expiration
                    $dateFin = new DateTime($row['date_fin']);
                    $joursRestants = $aujourdhui->diff($dateFin)->days;
                    $expiration_class = $joursRestants <= 7 ? 'text-danger' : 'text-muted';
                    
                    echo '
                    <div class="col-md-4 mb-4 member-card" data-abonnement="'.$row['type_abonnement'].'">
                        <div class="card card-member h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">'.$row['prenom'].' '.$row['nom'].'</h5>
                                <span class="badge '.$badge_class.' badge-abonnement">'.ucfirst($row['type_abonnement']).'</span>
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                    <strong>Âge:</strong> '.$age.' ans<br>
                                    <strong>Email:</strong> '.$row['email'].'<br>
                                    <strong>Téléphone:</strong> '.($row['telephone'] ?? 'Non renseigné').'<br>
                                    <strong>Abonnement:</strong> '.number_format($row['prix'], 0, ',', ' ').' FCFA<br>
                                    <strong>Période:</strong> '.date('d/m/Y', strtotime($row['date_debut'])).' - '.date('d/m/Y', strtotime($row['date_fin'])).'
                                </p>
                                <p class="card-text '.$expiration_class.'">
                                    <small>'.($joursRestants > 0 ? $joursRestants.' jours restants' : 'Expiré aujourd\'hui').'</small>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="voir_profil.php?id='.$row['id'].'" class="btn btn-sm btn-outline-primary">Voir profil</a>
                                <a href="renouveler_abonnement.php?user_id='.$row['id'].'" class="btn btn-sm btn-outline-success">Renouveler</a>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<div class="col-12">
                        <div class="alert alert-info">Aucun membre actif trouvé.</div>
                    </div>';
            }
            ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtrage et recherche en temps réel
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const memberCards = document.querySelectorAll('.member-card');
            
            memberCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                if (cardText.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Filtre par type d'abonnement
        document.getElementById('filterAbonnement').addEventListener('change', function() {
            const filterValue = this.value;
            const memberCards = document.querySelectorAll('.member-card');
            
            memberCards.forEach(card => {
                if (filterValue === 'all' || card.getAttribute('data-abonnement') === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>

<?php
// Fermer la connexion
$conn->close();
?>