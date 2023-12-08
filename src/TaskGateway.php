<?php

/***
 * Class to interface with database.
 */

class TaskGateway {
    private PDO $conn;

    public function __construct(Database $database) {
        $this->conn = $database->getConnection();
    }

    public function getSchedule(): array
    {
        $sql = "SELECT *
                FROM student
                ORDER BY lessonTime";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
        return $data;
    }

    public function getStudent(string $id) : array | false {
        $sql = "SELECT *
                FROM student
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        //$stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function setStudentAttended(int $student_ID, string $lesson_date) : string | false {
        $sql = "INSERT INTO attendance (studentID, attended, lessonDate)
                VALUES (:id, 1, :lesson_date)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $student_ID, PDO::PARAM_INT);

        $stmt->bindValue(":lesson_date", $lesson_date, PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();

    }

    public function setStudentAway(int $student_ID, string $lesson_date) : array | false {
        $sql = "INSERT INTO attendance (studentID, attended, lessonDate)
                VALUES (:id, 0, :lesson_date)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $student_ID, PDO::PARAM_INT);

        $stmt->bindValue(":lesson_date", $lesson_date, PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();

    }

    public function updateAttendedForStudent(int $student_ID, string $lesson_date) : int | false  {

            // update where student and date are matching
    
            $sql = "UPDATE attendance"
                    . " SET attended = CASE
                        WHEN attended = 0 THEN 1
                        WHEN attended = 1 THEN 0
                        ELSE attended
                    END "
                    . " WHERE studentID = :id"
                    . " AND lessonDate = :lessonDate";
    
            $stmt = $this->conn->prepare($sql);

            $stmt->bindValue(":id", $student_ID, PDO::PARAM_INT);
            $stmt->bindValue(":lessonDate", $lesson_date, PDO::PARAM_STR);

            

            $stmt->execute();
            return $stmt->rowCount();


        
    }
}