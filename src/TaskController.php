<?php

/**
 * Front controller to interface between taskGatway and user
 */
class TaskController 
{
    public function __construct(private TaskGateway $gateway, private int $user_id) {

    }
    public function processRequest(string $method, ?string $id): void {
        if ($id === null) {
            if ($method == "GET"){
                echo json_encode($this->gateway->getSchedule());
            } /* elseif ($method == "POST") {
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data);

                // method post method is not used in this case
            }  */else {
                
            }
        } else {
            $data = (array) json_decode(file_get_contents("php://input"), true);

            $errors = $this->getValidationErrors($data);

            if (! empty($errors)) 
                {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }

            // $id is student ID, $user_id is teacher
            $student = $this->gateway->getStudent( $id);

            if ($student === false ) {
                $this->respondNotFound($id);
                return;
            }

            switch ($method) {
                case "POST":

                    $phpDate = (new DateTime())->format("Y-m-d");

                    // Now $phpDate contains today's date in PHP format

                    // Format the PHP date to MySQL date format
                    $mysqlDate = (new DateTime($phpDate))->format("Y-m-d");

                    if ($data["studentAttended"]) {
                        $this->gateway->setStudentAttended($id, $mysqlDate);
                    } else {
                        $this->gateway->setStudentAway($id, $mysqlDate);
                    }
                    
                    break;
                case "PATCH":
                    $data =(array) json_decode(file_get_contents("php://input"),true);

                    $errors = $this->getValidationErrors($data, false);

                    if (! empty($errors)) 
                    {
                        $this->respondUnprocessableEntity($errors);
                        return;
                    }

                    $phpDate = (new DateTime())->format("Y-m-d");

                    // Now $phpDate contains today's date in PHP format

                    // Format the PHP date to MySQL date format
                    $mysqlDate = (new DateTime($phpDate))->format("Y-m-d");
                    
                    $rows = $this->gateway->updateAttendedForStudent($id, $mysqlDate);
                    echo json_encode(["message" => "Task updated", "rows" => $rows]);

                    break;
                default:
                    $this->respondMethodNotAllowed("POST, PATCH");
            }
        }
    }

    private function respondMethodNotAllowed(string $allowed_methods): void 
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    private function respondNotFound(string $id): void {
        http_response_code(404);
        echo json_encode(["message" => "Student with ID $id not found"]);
    }

    private function respondCreated(string $id) : void 
    {
        http_response_code(201);
        echo json_encode(["message" => "Task created", "id" => $id]);
    }

    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors  = [];
        if ($is_new && !array_key_exists("studentAttended", $data)) {
            $errors[] = "studentAttended is required";
        }
        /* if( !empty($data["priority"])) {
            if(filter_var($data["priority"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "priority must be an integer";
            }
        } */
        return $errors;
    }
}