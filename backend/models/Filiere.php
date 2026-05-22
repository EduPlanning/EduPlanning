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
}
