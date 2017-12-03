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
      case "update":



    /*  $json = '{"foo-bar": 12345}';

      $obj = json_decode($json);
      print $obj->{'foo-bar'}; // 12345*/

        break;
      case "select":

        if (isset($_REQUEST['doctor']))
        {
          if(isset($_REQUEST['custom']))
          {
          $stmt = $mysqli->prepare("SELECT custom_service_id as service_id, description FROM custom_services WHERE doctor_id = ?");

          }
          else {
            $stmt = $mysqli->prepare("SELECT A.service_id, B.description, B.img_url, B.price FROM services_doctors A JOIN services B
             ON A.service_id = B.service_id WHERE A.doctor_id = ?");
          }
          $stmt->bind_param('i', $doctor);
          $doctor = $_REQUEST['doctor'];
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
      $email = $data["email"];
      $pass = $data["password"];
      $last_name = $data["last_name"];
      $name = $data["name"];
      $user_type = $data["user_type"];

      $stmt = $mysqli->prepare("INSERT INTO users (email,password,name,last_name,user_type) VALUES(?,?,?,?,?)");
      $stmt->bind_param('sssss', $email,$pass,$name,$last_name,$user_type);
      $stmt->execute();
      $arr = array('rows_affected' => $mysqli->affected_rows);
      echo json_encode($arr);
      $last_id = $mysqli->insert_id;
      if ($mysqli->affected_rows == 1)
      {
        if (isset($data["proID"])) {
          $proId = $data["proID"];
          $stmt = $mysqli->prepare("INSERT INTO doctors (user_id,professional_id) VALUES(?,?)");
          $stmt->bind_param('is',$last_id, $proId);
          $stmt->execute();
        }
        else {
          $stmt = $mysqli->prepare("INSERT INTO patients (user_id) VALUES(?)");
          $stmt->bind_param('i',$last_id);
          $stmt->execute();
        }
      }
    }
  }
 ?>
