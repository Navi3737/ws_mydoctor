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
      case "select_status":
        if (isset($_REQUEST['id']))
        {
            $stmt = $mysqli->prepare("SELECT B.schedule_id, B.status as schedule_status, C.destiny_transfer_id, C.status as destiny_transfer_status,
                                      D.hotel_transfer_id, D.status as hotel_transfer_status,
                                      E.performed_service_id,E.status as operation_status, F.origin_transfer_id, F.status as origin_transfer_status,
                                      G.after_service_id, G.status as after_service_status
                                      FROM proceedings A
                                      JOIN schedules B ON (A.process_id = B.process_id)
                                      JOIN destiny_transfer C ON (C.process_id = A.process_id)
                                      JOIN hotel_transfer D ON (D.process_id = A.process_id)
                                      JOIN performed_services E ON (E.process_id = A.process_id)
                                      JOIN origin_transfer F ON (F.process_id = A.process_id)
                                      JOIN after_services G ON (A.process_id = G.process_id)
                                      WHERE A.process_id = ?");
            $stmt->bind_param('i', $id);
            $id = $_REQUEST['id'];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        for ($row_num = $result->num_rows - 1; $row_num >= 0; $row_num--) {
          $result->data_seek($row_num);
          $row = $result->fetch_assoc();
          echo json_encode($row,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
      break;
      case "decline":
      $stmt = $mysqli->prepare("UPDATE requests set status = 2 WHERE request_id = ?");
      $stmt->bind_param('i',$request_id);
      $request_id = $_REQUEST['id'];
      $stmt->execute();
      $arr = array('rows_affected' => $mysqli->affected_rows);
      echo json_encode($arr);


    /*  $json = '{"foo-bar": 12345}';

      $obj = json_decode($json);
      print $obj->{'foo-bar'}; // 12345*/

        break;
      case "select":

        if (isset($_REQUEST['query']))
        {
          $stmt = $mysqli->prepare("SELECT distinct concat(B.name, ' ', B.last_name) as name, B.img_url, F.city
                                    , A.doctor_id, A.professional_history
                                    FROM doctors A
                                    JOIN users B ON (A.user_id = B.user_id)
                                    LEFT JOIN locations F ON (F.location_id = B.location_id)
                                    LEFT JOIN services_doctors C ON (C.doctor_id = A.doctor_id)
                                    LEFT JOIN custom_services D ON (D.doctor_id = A.doctor_id)
                                    LEFT JOIN services E ON (E.service_id = C.service_id)

                                    WHERE concat(B.name, ' ', B.last_name) =?  OR E.description =?  OR D.description = ?");
          $stmt->bind_param('sss', $query,$query,$query);
          $query = $_REQUEST['query'];

        }
        elseif (isset($_REQUEST['doctor'])) {
          $stmt = $mysqli->prepare("SELECT A.*, B.description, concat(D.name,' ',D.last_name) as name, B.img_url  FROM requests A JOIN services B
            ON (A.service_id = B.service_id)
            JOIN patients C ON (A.patient_id = C.patient_id)
            JOIN users D ON (D.user_id = C.user_id)
             where doctor_id = ? AND status = '0'");
          $stmt->bind_param('i', $doctor);
          $doctor = $_REQUEST['doctor'];
        }
        elseif (isset($_REQUEST['patient'])) {
          $stmt = $mysqli->prepare("SELECT A.*, B.description, concat(D.name,' ',D.last_name) as name, B.img_url  FROM requests A JOIN services B
            ON (A.service_id = B.service_id)
            JOIN doctors C ON (A.doctor_id = C.doctor_id)
            JOIN users D ON (D.user_id = C.user_id)
             where patient_id = ? AND status = '0'");
          $stmt->bind_param('i', $patient);
          $patient = $_REQUEST['patient'];
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rows = array();
        for ($row_num = $result->num_rows - 1; $row_num >= 0; $row_num--) {
          $result->data_seek($row_num);
          $row = $result->fetch_assoc();
          $rows[] =  $row;
        }
        echo json_encode($rows,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        break;
      case "insert":
      $data = json_decode(file_get_contents('php://input'), true);
      $doctor_id = $data["doctor_id"];
      $patient_id = $data["patient_id"];
      $service_id = $data["service_id"];
      $custom_service_id = $data["custom_service_id"];
      $date = date('Y-m-d H:i:s');
      $status = "0";

      $stmt = $mysqli->prepare("INSERT INTO requests (doctor_id,patient_id,service_id,custom_service_id,request_date,status) VALUES(?,?,?,?,?,?)");
      $stmt->bind_param('iiiiss', $doctor_id,$patient_id,$service_id,$custom_service_id,$date,$status);
      $stmt->execute();
      $arr = array('rows_affected' => $mysqli->affected_rows);
      echo json_encode($arr);
    }
  }
 ?>
