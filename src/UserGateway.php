<?php

/**
 * build after ensuring that endpoints work
 */

class UserGateway 
{
    private PDO $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();

    }

    public function getByAPIKey(string $key) {
        $sql = "SELECT *
                FROM teacher 
                WHERE api_key = :api_key";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":api_key", $key, PDO::PARAM_STR);

        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByUsername() {

    }

    public function getByID() {

    }
}