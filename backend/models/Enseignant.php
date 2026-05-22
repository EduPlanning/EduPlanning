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
}
