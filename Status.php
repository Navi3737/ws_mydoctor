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
              if (isset($_REQUEST['user']))
              {
                switch ($_REQUEST['user']) {
                  case "D":
                  $stmt = $mysqli->prepare("SELECT A.visit_date, A.patient_notes, A.doctor_notes, D.telephone
                                            FROM schedules A
                                            JOIN proceedings B ON A.process_id = B.process_id
                                            JOIN patients C ON C.patient_id = B.patient_id
                                            JOIN users D ON D.user_id = C.user_id
                                            WHERE schedule_id = ?");
                  break;
                  case "P":
                  $stmt = $mysqli->prepare("SELECT A.visit_date, A.patient_notes, A.doctor_notes, D.telephone
                                            FROM schedules A
                                            JOIN proceedings B ON A.process_id = B.process_id
                                            JOIN doctors C ON C.doctor_id = B.doctor_id
                                            JOIN users D ON D.user_id = C.user_id
                                            WHERE schedule_id = ?");
                    break;
                }

                $stmt->bind_param('i', $id);
                $id = $_REQUEST['id'];
            }
          }
          $stmt->execute();
          $result = $stmt->get_result();
          for ($row_num = $result->num_rows - 1; $row_num >= 0; $row_num--) {
            $result->data_seek($row_num);
            $row = $result->fetch_assoc();
            echo json_encode($row,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
          }
          break;
          case "update";
          $data = json_decode(file_get_contents('php://input'), true);
          $process_id = $data["process_id"];
          $status= $data["status"];
          $step = $data["step"];
          $table = "";
          switch ($step) {
            case 0:
              $table = "schedules";
              break;
            case 1:
              $table = "destiny_transfer";
            break;
            case 2:
              $table = "hotel_transfer";
            break;
            case 3:
              $table = "performed_services";
            break;
            case 4:
              $table = "origin_transfer";
            break;
            case 5:
              $table = "after_services";
            break;
          }
          $stmt = $mysqli->prepare("UPDATE ".$table." set status = ? WHERE process_id = ?");
          $stmt->bind_param('si', $status,$process_id);
          $stmt->execute();
          $arr = array('rows_affected' => $mysqli->affected_rows);
          echo json_encode($arr);
          //$last_id = $mysqli->insert_id;
          if ($mysqli->affected_rows == 1 & $step == 5)
          {
              $stmt = $mysqli->prepare("UPDATE proceedings set status = '1' WHERE process_id = ?");
              $stmt->bind_param('i',$process_id);
              $stmt->execute();
          }
          break;
        }
    }
 ?>
