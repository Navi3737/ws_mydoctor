<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
  include 'Connection.php';
  if (isset($_REQUEST['action']))
  {
    $action = $_REQUEST['action'];
    switch ($action) {
      case "select_profile":
      //inicio del select
        if (isset($_REQUEST['id']))
        {
          $user_type = $_REQUEST['user_type'];

          if($user_type == 0)
          {
            $stmt = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $id = $_REQUEST['id'];

            $stmt->execute();
            $result = $stmt->get_result();
            for ($row_num = $result->num_rows - 1; $row_num >= 0; $row_num--)
            {
            $result->data_seek($row_num);
            $row = $result->fetch_assoc();
            echo json_encode($row,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
          }
          else
          {
            $stmt = $mysqli->prepare("SELECT * FROM user_patient_view WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $id = $_REQUEST['id'];

            $stmt->execute();
            $result = $stmt->get_result();
            for ($row_num = $result->num_rows - 1; $row_num >= 0; $row_num--)
            {
            $result->data_seek($row_num);
            $row = $result->fetch_assoc();
            echo json_encode($row,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
          }
        }
      break;
      //fin del select
      case "update_profile":
      $aux = 0;
      $data = json_decode(file_get_contents('php://input'), true);
      $user_id = $data["user_id"];
      $last_name = $data["last_name"];
      $name = $data["name"];
      $phone = $data["phone"];
      $user_type = $data["user_type"];

      $stmt = $mysqli->prepare("UPDATE users SET name = ?, last_name = ?, telephone = ? WHERE user_id = ? ");
      $stmt->bind_param('sssi', $name, $last_name, $phone, $user_id);
      $stmt->execute();
      $arr = array('rows_affected' => $mysqli->affected_rows);
      $aux = $mysqli->affected_rows;
      if ($aux > 0)
      {
        echo json_encode($arr);
      }
      //ejecucion de la insercion de fecha de nacimiento y genero en caso de ser paciente
      if($user_type == 1)
      {
        $birth_date = $data["birth_date"];
        $gender = $data["gender"];
        $stmt = $mysqli->prepare("UPDATE patients SET birth_date= ?, gender = ? WHERE user_id = ?");
        $stmt->bind_param('ssi', $birth_date, $gender, $user_id);
        $stmt->execute();
        if ($aux == 0) {
          $arr = array('rows_affected' => $mysqli->affected_rows);
          echo json_encode($arr);
        }

      }
      break;
      //fin del update profile
      case "update_email":
      $data = json_decode(file_get_contents('php://input'), true);

      $email = $data["email"];
      $user_id = $data["user_id"];

      $stmt = $mysqli->prepare("UPDATE users SET email = ? WHERE user_id = ? ");
      $stmt->bind_param('ss', $email, $user_id);
      $stmt->execute();
      $arr = array('rows_affected' => $mysqli->affected_rows);
      echo json_encode($arr);
      break;
      //fin del update email
      case "update_password":
      $data = json_decode(file_get_contents('php://input'), true);

      $password = $data["pwd"];
      $user_id = $data["user_id"];

      $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE user_id = ? ");
      $stmt->bind_param('ss', $password, $user_id);
      $stmt->execute();
      $arr = array('rows_affected' => $mysqli->affected_rows);
      echo json_encode($arr);
      break;
    }
  }
?>
