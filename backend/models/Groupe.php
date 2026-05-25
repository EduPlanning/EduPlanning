<?php
class Groupe
{
    public $conn;
    public $id;
    public $nom;
    public $niveau;
    public $filiere_id;
    public $capacite;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = 'SELECT g.id, g.nom, g.niveau, g.filiere_id, g.capacite,
                         f.nom AS filiere_nom, f.code AS filiere_code
                  FROM `groupe` g
                  LEFT JOIN filiere f ON g.filiere_id = f.id
                  ORDER BY g.nom';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create()
    {
        $query = 'INSERT INTO `groupe` (nom, niveau, filiere_id, capacite)
                  VALUES (:nom, :niveau, :filiere_id, :capacite)';
        $stmt = $this->conn->prepare($query);
        $this->nom = htmlspecialchars(strip_tags($this->nom ?? ''));
        $this->niveau = htmlspecialchars(strip_tags($this->niveau ?? ''));
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':niveau', $this->niveau);
        $stmt->bindParam(':filiere_id', $this->filiere_id);
        $stmt->bindParam(':capacite', $this->capacite);
        return $stmt->execute();
    }

    public function update()
    {
        $query = 'UPDATE `groupe`
                  SET nom = :nom, niveau = :niveau, filiere_id = :filiere_id, capacite = :capacite
                  WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $this->nom = htmlspecialchars(strip_tags($this->nom ?? ''));
        $this->niveau = htmlspecialchars(strip_tags($this->niveau ?? ''));
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':niveau', $this->niveau);
        $stmt->bindParam(':filiere_id', $this->filiere_id);
        $stmt->bindParam(':capacite', $this->capacite);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function delete()
    {
        $query = 'DELETE FROM `groupe` WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function count()
    {
        $query = 'SELECT COUNT(*) as total FROM `groupe`';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
