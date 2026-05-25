<?php
class Notification {
    public $conn;
    public $id; public $utilisateur_id; public $message; public $lu;

    public function __construct($db) { $this->conn = $db; }

    public function create() {
        $q = 'INSERT INTO notification (utilisateur_id, message) VALUES (:uid, :msg)';
        $s = $this->conn->prepare($q);
        $this->message = htmlspecialchars(strip_tags($this->message ?? ''));
        $s->bindParam(':uid', $this->utilisateur_id);
        $s->bindParam(':msg', $this->message);
        return $s->execute();
    }

    public function getByUser() {
        $q = 'SELECT * FROM notification WHERE utilisateur_id = :uid ORDER BY cree_le DESC LIMIT 50';
        $s = $this->conn->prepare($q);
        $s->bindParam(':uid', $this->utilisateur_id);
        $s->execute(); return $s;
    }

    public function countUnread() {
        $q = 'SELECT COUNT(*) as total FROM notification WHERE utilisateur_id = :uid AND lu = 0';
        $s = $this->conn->prepare($q);
        $s->bindParam(':uid', $this->utilisateur_id);
        $s->execute();
        return $s->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function markAllRead() {
        $q = 'UPDATE notification SET lu = 1 WHERE utilisateur_id = :uid';
        $s = $this->conn->prepare($q);
        $s->bindParam(':uid', $this->utilisateur_id);
        return $s->execute();
    }
}
