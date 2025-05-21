<?php

  $db_server = "localhost";
  $db_user = "root";
  $db_password = "";
  $db_name= "bookstore_db";
  $conn = new mysqli($db_server , $db_user , $db_password , $db_name);

  // echo "<pre>";
  // print_r($_POST);
  // echo "</pre>";
if (isset($_POST["country"]) && isset($_POST["gender"]))
{
  $name = $_POST["name"];
  $email = $_POST["email"];
  $age = $_POST["age"];
  $country = $_POST["country"];
  $gender = $_POST["gender"];
  $role = $_POST["role"];
  $telephone = $_POST["telephone"];
  $password = $_POST["password"];

  $insert="INSERT INTO users( name,  email, telephone, age, country, gender,role,password,created_on , last_update)
  VALUES ('$name','$email',$telephone,$age,'$country','$gender','$role','$password',NOW(),NOW())";
  $conn->query($insert);
  header("location:../home.php");
  exit();

}else{
  header("Location: adduser.php?error=1");
  exit();
}
?>