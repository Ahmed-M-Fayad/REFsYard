<?php

  $db_server = "localhost";
  $db_user = "root";
  $db_password = "";
  $db_name= "bookstore_db";
  $conn = new mysqli($db_server , $db_user , $db_password , $db_name);

  // echo"<pre>";
  // print_r($_POST);
  // echo"<pre>";


  $id = $_POST["id"];
  $name = $_POST["name"];
  $email = $_POST["email"];
  $telephone= $_POST["telephone"];
  $gender= $_POST["gender"];
  $age= $_POST["age"];
  $role= $_POST["role"];
  $country= $_POST["country"];
  // $sel_created="select * from customer where id =$id";
  // $result_created=$conn->query($sel_created);
  // $result=$result_created->fetch_assoc();
  // $created=$result["created_on"];

  $update="UPDATE users SET name='$name',gender='$gender',email='$email',telephone='$telephone',age=$age,
  country='$country',role='$role',last_update=NOW() WHERE user_id = $id";
  $conn->query($update);
  header("location:../home.php");