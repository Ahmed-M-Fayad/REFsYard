<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if(isset($_SESSION["username"])){
  header("location:home.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Login - REFsYard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
    .login-box {
      background: rgba(255, 255, 255, 0.95);
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      width: 380px;
      max-width: 90%;
      position: relative;
      z-index: 2;
      animation: slideIn 0.8s ease-out forwards;
      backdrop-filter: blur(8px);
    }
    .login-box h2 {
      margin-bottom: 30px;
      text-align: center;
      font-size: 28px;
      color: #00143c;
      font-weight: 600;
      letter-spacing: 0.5px;
    }
    .login-box input[type="text"],
    .login-box input[type="password"] {
      width: 100%;
      padding: 14px;
      margin: 12px 0;
      border-radius: 10px;
      border: 1px solid #e0e0e0;
      font-size: 15px;
      background: #f8fafc;
      transition: all 0.3s ease;
    }
    .login-box input:focus {
      border-color: #007bff;
      box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
      outline: none;
      background: #fff;
    }
    .login-box input:hover {
      border-color: #0056b3;
    }
    .login-box button {
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
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .login-box button:hover {
      background: linear-gradient(135deg, #0056b3, #003d80);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
    }
    .login-box button:active {
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
    }
    .login-box button:disabled {
      background: #6c757d;
      cursor: not-allowed;
    }
    .message {
      margin-top: 12px;
      text-align: center;
      font-size: 14px;
      font-weight: 500;
      color: #dc3545;
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
    @media (max-width: 480px) {
      .login-box {
        width: 90%;
        padding: 25px;
      }
      .login-box h2 {
        font-size: 24px;
      }
      .login-box input[type="text"],
      .login-box input[type="password"] {
        padding: 12px;
        margin: 10px 0;
      }
      .login-box button {
        padding: 12px;
        font-size: 15px;
      }
      .links {
        font-size: 13px;
      }
    }
  </style>
</head>
<body>
<div class="login-box">
  <h2>Welcome Back</h2>
  <form id="login-form" action ="login.php" method ="POST">
    <input type="text" name="username" placeholder="Username" required />
    <input type="password" name="password" placeholder="Password" required />
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <button type="submit">Sign In <i class="fas fa-spinner fa-spin" id="spinner" style="display: none; margin-left: 8px;"></i></button>
    <div class="message" id="message" role="alert" aria-live="polite"></div>
  </form>
  <div class="links">
    <a href="register.php">Create Account</a> | <a href="forgot-password.php">Forgot Password?</a>
  </div>
</div>
<script>
  document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const messageDiv = document.getElementById('message');
    const spinner = document.getElementById('spinner');
    const submitButton = form.querySelector('button');

    spinner.style.display = 'inline-block';
    submitButton.disabled = true;
    messageDiv.classList.remove('show');
    messageDiv.textContent = '';

    try {
      const response = await fetch('login.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json(); // ✅ use `result` instead of `data`

      if (result.success && result.redirect) {
        window.location.href = result.redirect;
        return;
      }

      if (!result.success) {
        messageDiv.textContent = result.error;
        messageDiv.classList.add('show');
      }

    } catch (error) {
      console.error('Fetch Error:', error);
      messageDiv.textContent = error.message.includes('Failed to fetch')
        ? 'Network error. Cannot reach login.php.'
        : 'Something went wrong.';
      messageDiv.classList.add('show');
    } finally {
      spinner.style.display = 'none';
      submitButton.disabled = false;
    }
  });
</script>

</body>
</html>