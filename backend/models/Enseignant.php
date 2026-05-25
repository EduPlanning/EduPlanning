<?php
class Enseignant
{
    public $conn;
    public $id;
    public $utilisateur_id;
    public $specialite;
    public $disponibilites;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = 'SELECT e.id, e.utilisateur_id, e.specialite, e.disponibilites,
                         u.nom, u.prenom, u.email, u.role
                  FROM enseignant e
                  JOIN utilisateur u ON e.utilisateur_id = u.id
                  WHERE u.actif = 1
                  ORDER BY u.nom';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByUtilisateurId()
    {
        $query = 'SELECT * FROM enseignant WHERE utilisateur_id = :utilisateur_id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':utilisateur_id', $this->utilisateur_id);
        $stmt->execute();
        return $stmt;
    }

    public function getById()
    {
        $query = 'SELECT * FROM enseignant WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function ensureForUser($utilisateurId, $specialite = null)
    {
        $check = $this->conn->prepare('SELECT id FROM enseignant WHERE utilisateur_id = :utilisateur_id LIMIT 1');
        $check->bindParam(':utilisateur_id', $utilisateurId);
        $check->execute();
        if ($check->rowCount() > 0) {
            return (int) $check->fetch(PDO::FETCH_ASSOC)['id'];
        }

        $specialite = htmlspecialchars(strip_tags($specialite ?? ''));
        $insert = $this->conn->prepare('INSERT INTO enseignant (utilisateur_id, specialite) VALUES (:utilisateur_id, :specialite)');
        $insert->bindParam(':utilisateur_id', $utilisateurId);
        $insert->bindParam(':specialite', $specialite);
        $insert->execute();

        return (int) $this->conn->lastInsertId();
    }

    public function deleteByUserId($utilisateurId)
    {
        $delete = $this->conn->prepare('DELETE FROM enseignant WHERE utilisateur_id = :utilisateur_id');
        $delete->bindParam(':utilisateur_id', $utilisateurId);
        return $delete->execute();
    }
}
