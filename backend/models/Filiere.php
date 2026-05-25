<?php
class Filiere
{
    public $conn;
    public $id;
    public $nom;
    public $code;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll()
    {
        $query = 'SELECT * FROM filiere ORDER BY nom';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById()
    {
        $query = 'SELECT * FROM filiere WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    public function create()
    {
        $query = 'INSERT INTO filiere (nom, code) VALUES (:nom, :code)';
        $stmt = $this->conn->prepare($query);
        $this->nom = htmlspecialchars(strip_tags($this->nom ?? ''));
        $this->code = htmlspecialchars(strip_tags($this->code ?? ''));
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':code', $this->code);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update()
    {
        $query = 'UPDATE filiere SET nom = :nom, code = :code WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $this->nom = htmlspecialchars(strip_tags($this->nom ?? ''));
        $this->code = htmlspecialchars(strip_tags($this->code ?? ''));
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function delete()
    {
        $query = 'DELETE FROM filiere WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function count()
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) AS total FROM filiere');
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
