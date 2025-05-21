<?php

  $db_server = "localhost";
  $db_user = "root";
  $db_password = "";
  $db_name= "bookstore_db";
  $conn = new mysqli($db_server , $db_user , $db_password , $db_name);

  $id = $_GET["id"];
  $del1 = "delete from wishlist where user_id = $id";
  $del2 = "delete from reviews where user_id = $id";
  $del2 = "delete from cart where user_id = $id";
  $del3 = "delete from users where user_id = $id";
  $conn->query($del1);
  $conn->query($del2);
  $conn->query($del3);
  header("location:../home.php");