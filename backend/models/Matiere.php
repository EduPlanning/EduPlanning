<?php
class Matiere {
    public $conn;
    public $id; public $nom; public $code; public $volume_horaire; public $coefficient;

    public function __construct($db) { $this->conn = $db; }

    public function getAll() {
        $s = $this->conn->prepare('SELECT * FROM matiere ORDER BY nom');
        $s->execute(); return $s;
    }
    public function create() {
        $q = 'INSERT INTO matiere (nom, code, volume_horaire, coefficient) VALUES (:nom, :code, :volume_horaire, :coefficient)';
        $s = $this->conn->prepare($q);
        $this->nom = htmlspecialchars(strip_tags($this->nom ?? ''));
        $this->code = htmlspecialchars(strip_tags($this->code ?? ''));
        $s->bindParam(':nom',           $this->nom);
        $s->bindParam(':code',          $this->code);
        $s->bindParam(':volume_horaire',$this->volume_horaire);
        $s->bindParam(':coefficient',   $this->coefficient);
        return $s->execute();
    }
    public function update() {
        $q = 'UPDATE matiere SET nom=:nom, code=:code, volume_horaire=:volume_horaire, coefficient=:coefficient WHERE id=:id';
        $s = $this->conn->prepare($q);
        $this->nom = htmlspecialchars(strip_tags($this->nom ?? ''));
        $this->code = htmlspecialchars(strip_tags($this->code ?? ''));
        $s->bindParam(':nom',$this->nom); $s->bindParam(':code',$this->code);
        $s->bindParam(':volume_horaire',$this->volume_horaire); $s->bindParam(':coefficient',$this->coefficient);
        $s->bindParam(':id',$this->id);
        return $s->execute();
    }
    public function delete() {
        $s = $this->conn->prepare('DELETE FROM matiere WHERE id = :id');
        $s->bindParam(':id',$this->id); return $s->execute();
    }
}
