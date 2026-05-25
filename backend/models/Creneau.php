<?php
class Creneau {
    public $conn;

    public $id;
    public $date_cours;
    public $heure_debut;
    public $heure_fin;
    public $matiere_id;
    public $enseignant_id;
    public $salle_id;
    public $groupe_id;
    public $type;
    public $recurrent;
    public $freq_recurrence;
    public $date_fin_recurrence;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all creneaux with joined data
    public function getAll($filters = []) {
        $query = "SELECT c.id, c.date_cours, c.heure_debut, c.heure_fin, c.type, c.recurrent, c.freq_recurrence, c.date_fin_recurrence,
                          c.matiere_id, c.enseignant_id, c.salle_id, c.groupe_id,
                          m.nom AS matiere_nom, m.code AS matiere_code,
                          CONCAT(u.prenom,' ',u.nom) AS enseignant_nom,
                          s.nom AS salle_nom,
                          g.nom AS groupe_nom
                   FROM creneau c
                  LEFT JOIN matiere    m ON c.matiere_id    = m.id
                  LEFT JOIN enseignant e ON c.enseignant_id = e.id
                  LEFT JOIN utilisateur u ON e.utilisateur_id = u.id
                  LEFT JOIN salle  s ON c.salle_id    = s.id
                  LEFT JOIN groupe g ON c.groupe_id   = g.id
                  WHERE 1=1";
        $params = [];

        if (!empty($filters['groupe_id'])) {
            $query .= ' AND c.groupe_id = :groupe_id';
            $params[':groupe_id'] = $filters['groupe_id'];
        }
        if (!empty($filters['enseignant_id'])) {
            $query .= ' AND c.enseignant_id = :enseignant_id';
            $params[':enseignant_id'] = $filters['enseignant_id'];
        }
        if (!empty($filters['salle_id'])) {
            $query .= ' AND c.salle_id = :salle_id';
            $params[':salle_id'] = $filters['salle_id'];
        }
        if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
            $query .= ' AND c.date_cours BETWEEN :date_debut AND :date_fin';
            $params[':date_debut'] = $filters['date_debut'];
            $params[':date_fin']   = $filters['date_fin'];
        }

        $query .= ' ORDER BY c.date_cours, c.heure_debut';
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt;
    }

    // Detect conflicts before insert/update
    public function detecterConflits() {
        $conflicts = [];

        // Conflict enseignant
        $q1 = "SELECT c.id FROM creneau c
               WHERE c.enseignant_id = :enseignant_id
                 AND c.date_cours = :date_cours
                 AND c.id != :id
                 AND (c.heure_debut < :heure_fin AND c.heure_fin > :heure_debut)";
        $s1 = $this->conn->prepare($q1);
        $s1->bindParam(':enseignant_id', $this->enseignant_id);
        $s1->bindParam(':date_cours',    $this->date_cours);
        $s1->bindParam(':id',            $this->id ?? 0);
        $s1->bindParam(':heure_fin',     $this->heure_fin);
        $s1->bindParam(':heure_debut',   $this->heure_debut);
        $s1->execute();
        if ($s1->rowCount() > 0) $conflicts[] = 'enseignant';

        // Conflict salle
        $q2 = "SELECT c.id FROM creneau c
               WHERE c.salle_id = :salle_id
                 AND c.date_cours = :date_cours
                 AND c.id != :id
                 AND (c.heure_debut < :heure_fin AND c.heure_fin > :heure_debut)";
        $s2 = $this->conn->prepare($q2);
        $s2->bindParam(':salle_id',    $this->salle_id);
        $s2->bindParam(':date_cours',  $this->date_cours);
        $s2->bindParam(':id',          $this->id ?? 0);
        $s2->bindParam(':heure_fin',   $this->heure_fin);
        $s2->bindParam(':heure_debut', $this->heure_debut);
        $s2->execute();
        if ($s2->rowCount() > 0) $conflicts[] = 'salle';

        // Conflict groupe
        $q3 = "SELECT c.id FROM creneau c
               WHERE c.groupe_id = :groupe_id
                 AND c.date_cours = :date_cours
                 AND c.id != :id
                 AND (c.heure_debut < :heure_fin AND c.heure_fin > :heure_debut)";
        $s3 = $this->conn->prepare($q3);
        $s3->bindParam(':groupe_id',   $this->groupe_id);
        $s3->bindParam(':date_cours',  $this->date_cours);
        $s3->bindParam(':id',          $this->id ?? 0);
        $s3->bindParam(':heure_fin',   $this->heure_fin);
        $s3->bindParam(':heure_debut', $this->heure_debut);
        $s3->execute();
        if ($s3->rowCount() > 0) $conflicts[] = 'groupe';

        return $conflicts;
    }

    // Create creneau
    public function create() {
        $query = 'INSERT INTO creneau (date_cours, heure_debut, heure_fin, matiere_id, enseignant_id, salle_id, groupe_id, type, recurrent, freq_recurrence, date_fin_recurrence)
                  VALUES (:date_cours, :heure_debut, :heure_fin, :matiere_id, :enseignant_id, :salle_id, :groupe_id, :type, :recurrent, :freq_recurrence, :date_fin_recurrence)';
        $stmt = $this->conn->prepare($query);
        $this->type = htmlspecialchars(strip_tags($this->type ?? ''));
        $this->freq_recurrence = htmlspecialchars(strip_tags($this->freq_recurrence ?? ''));
        $stmt->bindParam(':date_cours',           $this->date_cours);
        $stmt->bindParam(':heure_debut',          $this->heure_debut);
        $stmt->bindParam(':heure_fin',            $this->heure_fin);
        $stmt->bindParam(':matiere_id',           $this->matiere_id);
        $stmt->bindParam(':enseignant_id',        $this->enseignant_id);
        $stmt->bindParam(':salle_id',             $this->salle_id);
        $stmt->bindParam(':groupe_id',            $this->groupe_id);
        $stmt->bindParam(':type',                 $this->type);
        $stmt->bindParam(':recurrent',            $this->recurrent);
        $stmt->bindParam(':freq_recurrence',      $this->freq_recurrence);
        $stmt->bindParam(':date_fin_recurrence',  $this->date_fin_recurrence);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    // Update creneau
    public function update() {
        $query = 'UPDATE creneau SET date_cours=:date_cours, heure_debut=:heure_debut, heure_fin=:heure_fin,
                  matiere_id=:matiere_id, enseignant_id=:enseignant_id, salle_id=:salle_id,
                  groupe_id=:groupe_id, type=:type, recurrent=:recurrent, freq_recurrence=:freq_recurrence, date_fin_recurrence=:date_fin_recurrence WHERE id=:id';
        $stmt = $this->conn->prepare($query);
        $this->type = htmlspecialchars(strip_tags($this->type ?? ''));
        $this->freq_recurrence = htmlspecialchars(strip_tags($this->freq_recurrence ?? ''));
        $stmt->bindParam(':date_cours',           $this->date_cours);
        $stmt->bindParam(':heure_debut',          $this->heure_debut);
        $stmt->bindParam(':heure_fin',            $this->heure_fin);
        $stmt->bindParam(':matiere_id',           $this->matiere_id);
        $stmt->bindParam(':enseignant_id',        $this->enseignant_id);
        $stmt->bindParam(':salle_id',             $this->salle_id);
        $stmt->bindParam(':groupe_id',            $this->groupe_id);
        $stmt->bindParam(':type',                 $this->type);
        $stmt->bindParam(':recurrent',            $this->recurrent);
        $stmt->bindParam(':freq_recurrence',      $this->freq_recurrence);
        $stmt->bindParam(':date_fin_recurrence',  $this->date_fin_recurrence);
        $stmt->bindParam(':id',                   $this->id);
        return $stmt->execute();
    }

    // Delete
    public function delete() {
        $query = 'DELETE FROM creneau WHERE id = :id';
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    // Stats
    public function countAll() {
        $stmt = $this->conn->prepare('SELECT COUNT(*) as total FROM creneau');
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countConflits() {
        // Count distinct pairs of creneaux that overlap same enseignant or salle
        $query = "SELECT COUNT(*) as total FROM (
            SELECT c1.id FROM creneau c1
            JOIN creneau c2 ON c1.id < c2.id
              AND c1.date_cours = c2.date_cours
              AND (c1.enseignant_id = c2.enseignant_id OR c1.salle_id = c2.salle_id OR c1.groupe_id = c2.groupe_id)
              AND c1.heure_debut < c2.heure_fin AND c1.heure_fin > c2.heure_debut
        ) sub";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Occupation des salles par semaine
    public function occupationSalles() {
        $query = "SELECT s.nom AS salle,
                         COUNT(c.id) AS nb_creneaux,
                         SUM(TIMESTAMPDIFF(MINUTE, c.heure_debut, c.heure_fin)) / 60 AS heures_total
                  FROM salle s
                  LEFT JOIN creneau c ON c.salle_id = s.id
                  GROUP BY s.id, s.nom
                  ORDER BY heures_total DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function repartitionFilieres() {
        $query = "SELECT f.id, f.nom AS filiere, f.code,
                         COUNT(c.id) AS nb_creneaux,
                         ROUND(COALESCE(SUM(TIMESTAMPDIFF(MINUTE, c.heure_debut, c.heure_fin)), 0) / 60, 2) AS heures_total
                  FROM filiere f
                  LEFT JOIN `groupe` g ON g.filiere_id = f.id
                  LEFT JOIN creneau c ON c.groupe_id = g.id
                  GROUP BY f.id, f.nom, f.code
                  ORDER BY heures_total DESC, f.nom ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
