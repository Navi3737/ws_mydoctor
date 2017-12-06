<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
  include 'Connection.php';
  if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
      case "delete":
        $id = $_REQUEST['id'];
        $mysqli->query("DELETE FROM users where user_id = '".$id."'");
        echo $mysqli->affected_rows;
        break;
      case "select":
          if (isset($_REQUEST['id']))
          {
              /*  $stmt = $mysqli->prepare("SELECT A.notes, A.arrival_date, C.name,C.hotel_id
                                          FROM hotel_transfer A
                                          JOIN proceedings B ON A.process_id = B.process_id
                                          JOIN hotels C ON (C.hotel_id = A.hotel_id)
                                          JOIN locations D ON (D.location_id = C.location_id)
                                          WHERE hotel_transfer_id = ?");*/

                $stmt = $mysqli->prepare("SELECT A.results, A.comments, A.details, A.performed_date
                                          FROM performed_services A
                                          JOIN proceedings B ON A.process_id = B.process_id
                                          WHERE performed_service_id = ?");
          }

          $stmt->bind_param('i', $id);
          $id = $_REQUEST['id'];
          $stmt->execute();
          $result = $stmt->get_result();
          for ($row_num = $result->num_rows - 1; $row_num >= 0; $row_num--) {
            $result->data_seek($row_num);
            $row = $result->fetch_assoc();
            echo json_encode($row,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
          }
        break;
        case "update":
        $data = json_decode(file_get_contents('php://input'), true);
        $performed_service_id = $data["performed_service_id"];
        $results = $data["results"];
        $comments = $data["comments"];
        $details = $data["details"];
        $performed_date = $data["performed_date"];
        $stmt = $mysqli->prepare("UPDATE performed_services set results = ?, comments = ?, details = ?, performed_date = ?
        WHERE performed_service_id = ?");
        $stmt->bind_param('ssssi', $results,$comments,$details,$performed_date,$performed_service_id);
        $stmt->execute();
        $arr = array('rows_affected' => $mysqli->affected_rows);
        echo json_encode($arr);
        //$last_id = $mysqli->insert_id;
        break;
        }
    }
 ?>
