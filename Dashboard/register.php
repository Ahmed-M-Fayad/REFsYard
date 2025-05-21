<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Registration - REFsYard</title>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- Google Fonts for modern typography -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Inter', sans-serif;
      background: url(usr/images/cover2.jpg) no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      position: relative;
      overflow: hidden;
    }
    /* Background overlay with gradient */
    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(0, 20, 60, 0.2), rgba(0, 80, 120, 0.2));
      z-index: 1;
    }
    .register-box {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      width: 400px;
      max-width: 90%;
      position: relative;
      z-index: 2;
      animation: slideIn 0.8s ease-out forwards;
      backdrop-filter: blur(8px);
    }
    .register-box h2 {
      margin-bottom: 30px;
      text-align: center;
      font-size: 28px;
      color: #00143c;
      font-weight: 600;
      letter-spacing: 0.5px;
    }
    .register-box input[type="text"],
    .register-box input[type="email"],
    .register-box input[type="password"],
    .register-box input[type="file"] {
      width: 100%;
      padding: 14px;
      margin: 12px 0;
      border-radius: 10px;
      border: 1px solid #e0e0e0;
      font-size: 15px;
      background: #f8fafc;
      transition: all 0.3s ease;
    }
    .register-box input:focus {
      border-color: #007bff;
      box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
      outline: none;
      background: #fff;
    }
    .register-box input:hover {
      border-color: #0056b3;
    }
    .register-box input[type="file"] {
      padding: 10px;
    }
    .register-box button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #007bff, #0056b3);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      letter-spacing: 0.5px;
    }
    .register-box button:hover {
      background: linear-gradient(135deg, #0056b3, #003d80);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
    }
    .register-box button:active {
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
    }
    .message {
      margin-top: 20px;
      text-align: center;
      font-size: 14px;
      font-weight: 500;
      opacity: 0;
      transition: opacity 0.4s ease;
    }
    .message.show {
      opacity: 1;
    }
    .links {
      margin-top: 20px;
      text-align: center;
      font-size: 14px;
      color: #00143c;
    }
    .links a {
      color: #007bff;
      text-decoration: none;
      margin: 0 12px;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    .links a:hover {
      color: #0056b3;
      text-decoration: underline;
    }
    /* Animations */
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    /* Responsive Design */
    @media (max-width: 480px) {
      .register-box {
        width: 90%;
        padding: 25px;
      }
      .register-box h2 {
        font-size: 24px;
      }
      .register-box input[type="text"],
      .register-box input[type="email"],
      .register-box input[type="password"],
      .register-box input[type="file"] {
        padding: 12px;
        margin: 10px 0;
      }
      .register-box button {
        padding: 12px;
 Universität für Bodenkultur Wien
        font-size: 15px;
      }
      .links {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>

<div class="register-box">
  <h2>Create Account</h2>
  <form id="register-form" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Full Name" required />
    <input type="email" name="email" placeholder="Email Address" required />
    <input type="password" name="password" placeholder="Password" required />
    <input type="file" name="profile_image" accept="image/*" />
    <button type="submit">Register</button>
    <div class="message" id="message"></div>
  </form>
  <div class="links">
    <a href="index.php">Already have an account? Sign In</a>
  </div>
</div>

<script>
  document.getElementById("register-form").addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const messageDiv = document.getElementById("message");

    fetch("register_process.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        messageDiv.style.color = "#007bff";
        messageDiv.textContent = "Registration successful! Redirecting...";
        messageDiv.classList.add("show");
        setTimeout(() => window.location.href = "index.php", 1000);
      } else {
        messageDiv.style.color = "#dc3545";
        messageDiv.textContent = data.error;
        messageDiv.classList.add("show");
      }
    })
    .catch(error => {
      messageDiv.style.color = "#dc3545";
      messageDiv.textContent = "Something went wrong.";
      messageDiv.classList.add("show");
      console.error("Error:", error);
    });
  });
</script>

</body>
</html>