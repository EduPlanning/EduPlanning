<?php
class Salle
{
    public $conn;
    public $id;
    public $nom;
    public $capacite;
    public $equipements;
    public $disponible;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = 'SELECT * FROM salle ORDER BY nom';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create()
    {
        $query = 'INSERT INTO salle (nom, capacite, equipements, disponible)
                  VALUES (:nom, :capacite, :equipements, :disponible)';
        $stmt = $this->conn->prepare($query);
        $this->nom = htmlspecialchars(strip_tags($this->nom ?? ''));
        $this->equipements = htmlspecialchars(strip_tags($this->equipements ?? ''));
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':capacite', $this->capacite);
        $stmt->bindParam(':equipements', $this->equipements);
        $stmt->bindParam(':disponible', $this->disponible);
        return $stmt->execute();
    }

    public function update()
    {
        $query = 'UPDATE salle SET nom = :nom, capacite = :capacite, equipements = :equipements, disponible = :disponible WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $this->nom = htmlspecialchars(strip_tags($this->nom ?? ''));
        $this->equipements = htmlspecialchars(strip_tags($this->equipements ?? ''));
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':capacite', $this->capacite);
        $stmt->bindParam(':equipements', $this->equipements);
        $stmt->bindParam(':disponible', $this->disponible);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function delete()
    {
        $query = 'DELETE FROM salle WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function count()
    {
        $query = 'SELECT COUNT(*) as total FROM salle';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
