<?php

  $db_server = "localhost";
  $db_user = "root";
  $db_password = "";
  $db_name= "bookstore_db";
  $conn = new mysqli($db_server , $db_user , $db_password , $db_name);

  $id = $_GET["id"];
  $del1 = "delete from wishlist where book_id = '$id'";
  $del2 = "delete from cart where book_id = '$id'";
  $del3 = "delete from reviews where book_id = '$id'";
  $del4 = "delete from books where book_id = '$id'";
  $conn->query($del1);
  $conn->query($del2);
  $conn->query($del3);
  $conn->query($del4);
  header("location:display_books.php");