<?php
// backend/models/Historique.php
class Historique {
    public $conn;
    public $id;
    public $utilisateur_id;
    public $action;
    public $details;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function log($utilisateur_id, $action, $details = '') {
        $q = 'INSERT INTO historique (utilisateur_id, action, details) VALUES (:uid, :action, :details)';
        $s = $this->conn->prepare($q);
        $s->bindParam(':uid', $utilisateur_id);
        $s->bindParam(':action', $action);
        $details = htmlspecialchars(strip_tags($details ?? ''));
        $s->bindParam(':details', $details);
        return $s->execute();
    }

    public function getAll($limit = 100) {
        $q = $this->conn->prepare('SELECT h.*, u.nom, u.prenom FROM historique h JOIN utilisateur u ON h.utilisateur_id = u.id ORDER BY h.cree_le DESC LIMIT :lim');
        $q->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $q->execute();
        return $q;
    }
}
?>