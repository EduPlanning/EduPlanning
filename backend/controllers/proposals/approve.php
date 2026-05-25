<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole('administrateur');

include_once '../../config/Database.php';
include_once '../../models/Notification.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents('php://input'));
if (!isset($data->proposal_id, $data->status)) {
    echo json_encode(['message' => 'Données manquantes']);
    exit;
}

$pid = intval($data->proposal_id);
$status = $data->status;
if (!in_array($status, ['approved', 'rejected'])) {
    echo json_encode(['message' => 'Status invalide']);
    exit;
}

$q = $db->prepare('UPDATE proposition SET status = :status WHERE id = :id');
$q->bindParam(':status', $status);
$q->bindParam(':id', $pid);
if ($q->execute()) {
    // If approved, attempt to apply the proposal to the resource
    $r = $db->prepare('SELECT * FROM proposition WHERE id = :id LIMIT 1');
    $r->bindParam(':id', $pid);
    $r->execute();
    $proposal = $r->fetch(PDO::FETCH_ASSOC);

    $applyMsg = '';
    if ($proposal && $status === 'approved') {
        $resource = $proposal['resource'];
        $action = $proposal['action'];
        $cible_id = $proposal['cible_id'];
        $payload = json_decode($proposal['payload'], true) ?: [];

        try {
            if ($resource === 'groupe') {
                if ($action === 'create') {
                    $s = $db->prepare('INSERT INTO `groupe` (nom, niveau, filiere_id, capacite) VALUES (:nom,:niveau,:filiere_id,:capacite)');
                    $s->bindParam(':nom', $payload['nom']);
                    $s->bindParam(':niveau', $payload['niveau']);
                    $s->bindParam(':filiere_id', $payload['filiere_id']);
                    $s->bindParam(':capacite', $payload['capacite']);
                    $s->execute();
                    $applyMsg = 'Groupe créé';
                } elseif ($action === 'update') {
                    $s = $db->prepare('UPDATE `groupe` SET nom=:nom, niveau=:niveau, filiere_id=:filiere_id, capacite=:capacite WHERE id=:id');
                    $s->bindParam(':nom', $payload['nom']);
                    $s->bindParam(':niveau', $payload['niveau']);
                    $s->bindParam(':filiere_id', $payload['filiere_id']);
                    $s->bindParam(':capacite', $payload['capacite']);
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Groupe mis à jour';
                } elseif ($action === 'delete') {
                    $s = $db->prepare('DELETE FROM `groupe` WHERE id = :id');
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Groupe supprimé';
                }
            } elseif ($resource === 'salle') {
                if ($action === 'create') {
                    $s = $db->prepare('INSERT INTO salle (nom, capacite, equipements, disponible) VALUES (:nom,:capacite,:equipements,:disponible)');
                    $s->bindParam(':nom', $payload['nom']);
                    $s->bindParam(':capacite', $payload['capacite']);
                    $s->bindParam(':equipements', $payload['equipements']);
                    $s->bindParam(':disponible', $payload['disponible']);
                    $s->execute();
                    $applyMsg = 'Salle créée';
                } elseif ($action === 'update') {
                    $s = $db->prepare('UPDATE salle SET nom=:nom, capacite=:capacite, equipements=:equipements, disponible=:disponible WHERE id=:id');
                    $s->bindParam(':nom', $payload['nom']);
                    $s->bindParam(':capacite', $payload['capacite']);
                    $s->bindParam(':equipements', $payload['equipements']);
                    $s->bindParam(':disponible', $payload['disponible']);
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Salle mise à jour';
                } elseif ($action === 'delete') {
                    $s = $db->prepare('DELETE FROM salle WHERE id = :id');
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Salle supprimée';
                }
            } elseif ($resource === 'matiere') {
                if ($action === 'create') {
                    $s = $db->prepare('INSERT INTO matiere (nom, code, volume_horaire, coefficient) VALUES (:nom,:code,:volume,:coef)');
                    $s->bindParam(':nom', $payload['nom']);
                    $s->bindParam(':code', $payload['code']);
                    $s->bindParam(':volume', $payload['volume_horaire']);
                    $s->bindParam(':coef', $payload['coefficient']);
                    $s->execute();
                    $applyMsg = 'Matière créée';
                } elseif ($action === 'update') {
                    $s = $db->prepare('UPDATE matiere SET nom=:nom, code=:code, volume_horaire=:volume, coefficient=:coef WHERE id=:id');
                    $s->bindParam(':nom', $payload['nom']);
                    $s->bindParam(':code', $payload['code']);
                    $s->bindParam(':volume', $payload['volume_horaire']);
                    $s->bindParam(':coef', $payload['coefficient']);
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Matière mise à jour';
                } elseif ($action === 'delete') {
                    $s = $db->prepare('DELETE FROM matiere WHERE id = :id');
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Matière supprimée';
                }
            } elseif ($resource === 'utilisateur') {
                if ($action === 'create') {
                    $s = $db->prepare('INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, actif, groupe_id) VALUES (:nom,:prenom,:email,:mot_de_passe,:role,:actif,:groupe_id)');
                    $hash = password_hash($payload['mot_de_passe'], PASSWORD_BCRYPT, ['cost' => 12]);
                    $s->bindParam(':nom', $payload['nom']);
                    $s->bindParam(':prenom', $payload['prenom']);
                    $s->bindParam(':email', $payload['email']);
                    $s->bindParam(':mot_de_passe', $hash);
                    $s->bindParam(':role', $payload['role']);
                    $s->bindParam(':actif', $payload['actif']);
                    $s->bindParam(':groupe_id', $payload['groupe_id']);
                    $s->execute();
                    $applyMsg = 'Utilisateur créé';
                } elseif ($action === 'update') {
                    $s = $db->prepare('UPDATE utilisateur SET nom=:nom, prenom=:prenom, email=:email, role=:role, actif=:actif, groupe_id=:groupe_id WHERE id=:id');
                    $s->bindParam(':nom', $payload['nom']);
                    $s->bindParam(':prenom', $payload['prenom']);
                    $s->bindParam(':email', $payload['email']);
                    $s->bindParam(':role', $payload['role']);
                    $s->bindParam(':actif', $payload['actif']);
                    $s->bindParam(':groupe_id', $payload['groupe_id']);
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Utilisateur mis à jour';
                } elseif ($action === 'delete') {
                    $s = $db->prepare('DELETE FROM utilisateur WHERE id = :id');
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Utilisateur supprimé';
                }
            } elseif ($resource === 'creneau') {
                if ($action === 'create') {
                    $s = $db->prepare('INSERT INTO creneau (date_cours, heure_debut, heure_fin, matiere_id, enseignant_id, salle_id, groupe_id, type, recurrent, freq_recurrence, date_fin_recurrence) VALUES (:date_cours,:heure_debut,:heure_fin,:matiere_id,:enseignant_id,:salle_id,:groupe_id,:type,:recurrent,:freq_recurrence,:date_fin_recurrence)');
                    $s->bindParam(':date_cours', $payload['date_cours']);
                    $s->bindParam(':heure_debut', $payload['heure_debut']);
                    $s->bindParam(':heure_fin', $payload['heure_fin']);
                    $s->bindParam(':matiere_id', $payload['matiere_id']);
                    $s->bindParam(':enseignant_id', $payload['enseignant_id']);
                    $s->bindParam(':salle_id', $payload['salle_id']);
                    $s->bindParam(':groupe_id', $payload['groupe_id']);
                    $s->bindParam(':type', $payload['type']);
                    $s->bindParam(':recurrent', $payload['recurrent']);
                    $s->bindParam(':freq_recurrence', $payload['freq_recurrence']);
                    $s->bindParam(':date_fin_recurrence', $payload['date_fin_recurrence']);
                    $s->execute();
                    $applyMsg = 'Créneau créé';
                } elseif ($action === 'update') {
                    $s = $db->prepare('UPDATE creneau SET date_cours=:date_cours, heure_debut=:heure_debut, heure_fin=:heure_fin, matiere_id=:matiere_id, enseignant_id=:enseignant_id, salle_id=:salle_id, groupe_id=:groupe_id, type=:type WHERE id=:id');
                    $s->bindParam(':date_cours', $payload['date_cours']);
                    $s->bindParam(':heure_debut', $payload['heure_debut']);
                    $s->bindParam(':heure_fin', $payload['heure_fin']);
                    $s->bindParam(':matiere_id', $payload['matiere_id']);
                    $s->bindParam(':enseignant_id', $payload['enseignant_id']);
                    $s->bindParam(':salle_id', $payload['salle_id']);
                    $s->bindParam(':groupe_id', $payload['groupe_id']);
                    $s->bindParam(':type', $payload['type']);
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Créneau mis à jour';
                } elseif ($action === 'delete') {
                    $s = $db->prepare('DELETE FROM creneau WHERE id = :id');
                    $s->bindParam(':id', $cible_id);
                    $s->execute();
                    $applyMsg = 'Créneau supprimé';
                }
            }
        } catch (Exception $ex) {
            // keep applyMsg empty on error
            $applyMsg = 'Erreur lors de l\'application de la proposition';
        }
    }

    // notify author
    $r2 = $db->prepare('SELECT auteur_id FROM proposition WHERE id = :id LIMIT 1');
    $r2->bindParam(':id', $pid);
    $r2->execute();
    $row2 = $r2->fetch(PDO::FETCH_ASSOC);
    if ($row2) {
        $notif = new Notification($db);
        $notif->utilisateur_id = $row2['auteur_id'];
        $msg = "Votre proposition #{$pid} a été {$status} par un administrateur.";
        if ($applyMsg) $msg .= " ({$applyMsg})";
        $notif->message = $msg;
        $notif->create();
    }
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'approve_proposal', json_encode(['id' => $pid, 'status' => $status, 'resource' => $proposal['resource']]));
    echo json_encode(['message' => 'Mise à jour effectuée', 'applied' => $applyMsg]);
} else {
    echo json_encode(['message' => 'Erreur lors de la mise à jour']);
}
