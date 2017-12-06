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
                $stmt = $mysqli->prepare("SELECT E.city as destiny_city, F.city as origin_city, A.arrival_date, A.notes
                                          FROM destiny_transfer A
                                          JOIN proceedings B ON A.process_id = B.process_id
                                          JOIN patients C ON C.patient_id = B.patient_id
                                          JOIN users D ON D.user_id = C.user_id
                                          JOIN locations E ON (A.location_id = E.location_id)
                                          JOIN locations F ON (D.location_id = F.location_id)
                                          WHERE destiny_transfer_id = ?");
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
        $destiny_transfer_id = $data["destiny_transfer_id"];
        $notes = $data["notes"];
        $arrival_date = $data["arrival_date"];
        $stmt = $mysqli->prepare("UPDATE destiny_transfer set arrival_date = ?, notes = ?
        WHERE destiny_transfer_id = ?");
        $stmt->bind_param('ssi', $arrival_date,$notes,$destiny_transfer_id);
        $stmt->execute();
        $arr = array('rows_affected' => $mysqli->affected_rows);
        echo json_encode($arr);
        //$last_id = $mysqli->insert_id;
        break;
        }
    }
 ?>
