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
            $stmt = $mysqli->prepare("SELECT B.visit_date,B.schedule_id, B.status as schedule_status, C.destiny_transfer_id, C.status as destiny_transfer_status,
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
      case "update":

      if (isset($_REQUEST['status']))
      {
        if (isset($_REQUEST['process_id']))
        {
          $status = $_REQUEST['status'];
          $process_id = $_REQUEST['process_id'];
          $stmt = $mysqli->prepare("UPDATE proceedings set status = ? WHERE process_id = ?");
          $stmt->bind_param('si', $status,$process_id);
          $stmt->execute();
          $arr = array('rows_affected' => $mysqli->affected_rows);
          echo json_encode($arr);
        }
      }

        break;
      case "select":

        if (isset($_REQUEST['doctor']))
        {
          $stmt = $mysqli->prepare("SELECT F.visit_date,C.description, E.img_url, CONCAT(E.name,' ',E.last_name) as name, A.process_id FROM proceedings A JOIN requests B ON (A.request_id =
          B.request_id) JOIN services C ON (C.service_id = B.service_id) JOIN patients D ON (D.patient_id = A.patient_id) JOIN users E ON (E.user_id = D.user_id)
          JOIN schedules F ON (F.process_id = A.process_id) WHERE A.doctor_id= ? AND A.status = 0
           ORDER BY A.process_id desc");
          $stmt->bind_param('i', $id);
          $id = $_REQUEST['doctor'];

        }
        elseif (isset($_REQUEST['patient'])) {
          $stmt = $mysqli->prepare("SELECT F.visit_date, C.description, E.img_url, CONCAT('Dr. ',E.name,' ',E.last_name) as name, A.process_id FROM proceedings A JOIN requests B ON (A.request_id =
          B.request_id) JOIN services C ON (C.service_id = B.service_id) JOIN doctors D ON (D.doctor_id = A.doctor_id) JOIN users E ON (E.user_id = D.user_id)
          JOIN schedules F ON (F.process_id = A.process_id) WHERE A.patient_id= ? AND A.status = 0
          ORDER BY A.process_id desc");
          $stmt->bind_param('i', $id);
          $id = $_REQUEST['patient'];
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
        case "selectH":

          if (isset($_REQUEST['doctor']))
          {
            $stmt = $mysqli->prepare("SELECT C.description, E.img_url, CONCAT(E.name,' ',E.last_name) as name, A.process_id FROM proceedings A JOIN requests B ON (A.request_id =
            B.request_id) JOIN services C ON (C.service_id = B.service_id) JOIN patients D ON (D.patient_id = A.patient_id) JOIN users E ON (E.user_id = D.user_id) WHERE A.doctor_id= ? AND A.status = 1
             ORDER BY A.process_id desc");
            $stmt->bind_param('i', $id);
            $id = $_REQUEST['doctor'];

          }
          elseif (isset($_REQUEST['patient'])) {
            $stmt = $mysqli->prepare("SELECT C.description, E.img_url, CONCAT('Dr. ',E.name,' ',E.last_name) as name, A.process_id FROM proceedings A JOIN requests B ON (A.request_id =
            B.request_id) JOIN services C ON (C.service_id = B.service_id) JOIN doctors D ON (D.doctor_id = A.doctor_id) JOIN users E ON (E.user_id = D.user_id) WHERE A.patient_id= ? AND A.status = 1
            ORDER BY A.process_id desc");
            $stmt->bind_param('i', $id);
            $id = $_REQUEST['patient'];
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
      $patient = $data["patient_id"];
      $doctor = $data["doctor_id"];
      $request = $data["request_id"];
      $status = $data["status"];
      $stmt = $mysqli->prepare("INSERT INTO proceedings (patient_id,doctor_id,request_id,status) VALUES(?,?,?,?)");
      $stmt->bind_param('iiis', $patient,$doctor,$request,$status);
      $stmt->execute();
      $arr = array('rows_affected' => $mysqli->affected_rows);
      echo json_encode($arr);
      //$last_id = $mysqli->insert_id;
      if ($mysqli->affected_rows == 1)
      {
          $stmt = $mysqli->prepare("UPDATE requests set status = '1' WHERE request_id = ?");
          $stmt->bind_param('i',$request);
          $stmt->execute();
      }
    }
  }
 ?>
