<?php
  include 'Connection.php';
  if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    switch ($action) {
      case "delete":
        $id = $_REQUEST['id'];
        $mysqli->query("DELETE FROM users where user_id = '".$id."'");
        echo $mysqli->affected_rows;
        break;
      case "update":



    /*  $json = '{"foo-bar": 12345}';

      $obj = json_decode($json);
      print $obj->{'foo-bar'}; // 12345*/

        break;
      case "select":
        if (isset($_REQUEST['id']))
        {
            $stmt = $mysqli->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->bind_param('i', $id);
            $id = $_REQUEST['id'];
        }
        else if (isset($_REQUEST['email']))
        {
            if (isset($_REQUEST['password']))
            {
                $stmt = $mysqli->prepare("SELECT user_type FROM users where email = ? AND password = ?");
                $stmt->bind_param('ss', $email,$pass);
                $email = $_REQUEST['email'];
                $pass = $_REQUEST['password'];
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                  $result->data_seek(0);
                  $row = $result->fetch_assoc();
                  $user_type = $row["user_type"];
                }

                if ($user_type == "D") {
                  $stmt = $mysqli->prepare("SELECT A.*, B.* FROM users A
                  JOIN doctors B ON (B.user_id = A.user_id) where email = ? AND password = ? ") ;
                }
                else {
                  $stmt = $mysqli->prepare("SELECT A.*, B.* FROM users A
                  JOIN patients B ON (B.user_id = A.user_id) where email = ? AND password = ? ") ;
                }

                $stmt->bind_param('ss', $email,$pass);

            }
            else {
              $stmt = $mysqli->prepare("SELECT * FROM users where email = ?");
              $stmt->bind_param('s', $email);
              $email = $_REQUEST['email'];
            }

        }
        else if (isset($_REQUEST['email_forgot']))
        {
          $stmt = $mysqli->prepare("SELECT CONCAT(name,' ',last_name) as name, password FROM users where email = ?");
          $stmt->bind_param('s', $email);
          $email = $_REQUEST['email_forgot'];
        }
        else
        {
          $stmt = $mysqli->prepare("SELECT * FROM users");
        }

        $stmt->execute();
        $result = $stmt->get_result();
        for ($row_num = $result->num_rows - 1; $row_num >= 0; $row_num--) {
          $result->data_seek($row_num);
          $row = $result->fetch_assoc();
          echo json_encode($row,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
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
