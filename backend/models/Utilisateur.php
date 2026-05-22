<?php
class Utilisateur
{
    public $conn;

    public $id;
    public $nom;
    public $prenom;
    public $email;
    public $mot_de_passe;
    public $role;
    public $actif;
    public $groupe_id;
    public $cree_le;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Register new user
    public function register()
    {
        $query = 'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role)
                  VALUES (:nom, :prenom, :email, :mot_de_passe, :role)';
        $stmt = $this->conn->prepare($query);

        $this->nom    = htmlspecialchars(strip_tags($this->nom));
        $this->prenom = htmlspecialchars(strip_tags($this->prenom));
        $this->email  = htmlspecialchars(strip_tags($this->email));
        $this->role   = htmlspecialchars(strip_tags($this->role));
        $hash = password_hash($this->mot_de_passe, PASSWORD_BCRYPT, ['cost' => 12]);

        $stmt->bindParam(':nom',          $this->nom);
        $stmt->bindParam(':prenom',       $this->prenom);
        $stmt->bindParam(':email',        $this->email);
        $stmt->bindParam(':mot_de_passe', $hash);
        $stmt->bindParam(':role',         $this->role);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Login by email + password
    public function login()
    {
        $query = 'SELECT * FROM utilisateur WHERE email = :email AND actif = 1 LIMIT 1';
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        return $stmt;
    }

    // Get all users (admin)
    public function getAll($role = null)
    {
        if ($role) {
            $query = 'SELECT id, nom, prenom, email, role, actif, groupe_id, cree_le FROM utilisateur WHERE role = :role ORDER BY nom';
            $stmt  = $this->conn->prepare($query);
            $stmt->bindParam(':role', $role);
        } else {
            $query = 'SELECT id, nom, prenom, email, role, actif, groupe_id, cree_le FROM utilisateur ORDER BY nom';
            $stmt  = $this->conn->prepare($query);
        }
        $stmt->execute();
        return $stmt;
    }

    // Get single user
    public function getById()
    {
        $query = 'SELECT id, nom, prenom, email, role, actif, groupe_id, cree_le FROM utilisateur WHERE id = :id LIMIT 1';
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Update user
    public function update()
    {
        $query = 'UPDATE utilisateur SET nom=:nom, prenom=:prenom, email=:email, role=:role, actif=:actif, groupe_id=:groupe_id WHERE id=:id';
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':nom',    $this->nom);
        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':email',  $this->email);
        $stmt->bindParam(':role',   $this->role);
        $stmt->bindParam(':groupe_id', $this->groupe_id);
        $stmt->bindParam(':actif',  $this->actif);
        $stmt->bindParam(':id',     $this->id);
        return $stmt->execute();
    }

    // Delete user
    public function delete()
    {
        $query = 'DELETE FROM utilisateur WHERE id = :id';
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Stats for dashboard
    public function countByRole($role)
    {
        $query = 'SELECT COUNT(*) as total FROM utilisateur WHERE role = :role AND actif = 1';
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
