<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - REFsYard</title>
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
    .reset-box {
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
    .reset-box h2 {
      margin-bottom: 30px;
      text-align: center;
      font-size: 28px;
      color: #00143c;
      font-weight: 600;
      letter-spacing: 0.5px;
    }
    .reset-box input[type="email"] {
      width: 100%;
      padding: 14px;
      margin: 12px 0;
      border-radius: 10px;
      border: 1px solid #e0e0e0;
      font-size: 15px;
      background: #f8fafc;
      transition: all 0.3s ease;
    }
    .reset-box input:focus {
      border-color: #007bff;
      box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
      outline: none;
      background: #fff;
    }
    .reset-box input:hover {
      border-color: #0056b3;
    }
    .reset-box button {
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
    .reset-box button:hover {
      background: linear-gradient(135deg, #0056b3, #003d80);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
    }
    .reset-box button:active {
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
    }
    .reset-box button:disabled {
      background: #6c757d;
      cursor: not-allowed;
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
      .reset-box {
        width: 90%;
        padding: 25px;
      }
      .reset-box h2 {
        font-size: 24px;
      }
      .reset-box input[type="email"] {
        padding: 12px;
        margin: 10px 0;
      }
      .reset-box button {
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
  <div class="reset-box">
    <h2>Reset Password</h2>
    <form id="reset-form">
      <input type="email" name="email" placeholder="Enter your email" required />
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <button type="submit">Send Reset Link <i class="fas fa-spinner fa-spin" id="spinner" style="display: none; margin-left: 8px;"></i></button>
      <div class="message" id="message" role="alert" aria-live="polite"></div>
    </form>
    <div class="links">
      <a href="index.php">Back to Sign In</a>
    </div>
  </div>

  <script>
    document.getElementById("reset-form").addEventListener("submit", async function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const messageDiv = document.getElementById("message");
      const button = document.querySelector("button");
      const spinner = document.getElementById("spinner");
      const email = formData.get("email");
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      const fetchUrl = "./reset_process.php"; // Ensure relative path

      // Client-side email validation
      if (!emailRegex.test(email)) {
        messageDiv.style.color = "#dc3545";
        messageDiv.textContent = "Please enter a valid email address.";
        messageDiv.classList.add("show");
        return;
      }

      // Disable button and show spinner
      button.disabled = true;
      spinner.style.display = "inline-block";

      try {
        console.log("Fetching URL:", fetchUrl); // Log the URL
        const response = await fetch(fetchUrl, {
          method: "POST",
          body: formData
        });

        console.log("Response Status:", response.status, response.statusText);
        const text = await response.text();
        console.log("Raw Response:", text);

        if (!response.ok) {
          if (response.status === 404) {
            throw new Error("reset_process.php not found. Check file location.");
          }
          throw new Error(`HTTP error: ${response.status} ${response.statusText}`);
        }

        let data;
        try {
          data = JSON.parse(text);
        } catch {
          throw new Error("Invalid JSON response");
        }

        messageDiv.style.color = data.success ? "#007bff" : "#dc3545";
        messageDiv.textContent = data.success ? "We sent you a reset link" : data.error;
        messageDiv.classList.add("show");
        setTimeout(() => messageDiv.classList.remove("show"), 5000);
      } catch (error) {
        messageDiv.style.color = "#dc3545";
        messageDiv.textContent = error.message.includes("Failed to fetch")
          ? "Network error. Cannot reach reset_process.php."
          : error.message.includes("not found")
          ? "Error: reset_process.php not found. Please check the file exists."
          : error.message.includes("Invalid JSON")
          ? "Server returned invalid response. Check reset_process.php."
          : `An unexpected error occurred: ${error.message}`;
        messageDiv.classList.add("show");
        console.error("Fetch Error:", error);
      } finally {
        button.disabled = false;
        spinner.style.display = "none";
      }
    });

    // Auto-focus email input
    document.querySelector('input[name="email"]').focus();
  </script>
</body>
</html>