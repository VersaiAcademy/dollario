<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// DB Connection
$host = '46.202.161.91';
$dbname = 'u973762102_admin';
$username = 'u973762102_dollario';
$password = '876543Kamlesh';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? null;  // use session user_id
    $subject = $_POST['subject'] ?? null;
    $message = $_POST['message'] ?? null;

    if (!$user_id || !$subject || !$message) {
        echo "<script>alert('Please fill all the fields.'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO user_help_requests (user_id, subject, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id, $subject, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Help request sent successfully!'); window.location.href='submit_help.php';</script>";
    } else {
        echo "<script>alert('Something went wrong!'); window.history.back();</script>";
    }

    $stmt->close();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>User Dashboard Help</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    /* Help Icon Button */
    #openHelpModal {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background: #D4AF37;
      border: none;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      color: black;
      font-size: 24px;
      cursor: pointer;
      z-index: 10000;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
    }

    /* Modal background */
    #helpModal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.6);
      z-index: 9999;
    }

    /* Help form container */
    .help-form-container {
      max-width: 500px;
      margin: 100px auto;
      background: #111;
      padding: 30px;
      border-radius: 8px;
      color: #fff;
      box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
      position: relative;
    }

    .help-form-container h2 {
      color: #D4AF37;
      text-align: center;
      margin-top: 0;
    }

    .help-form-container input,
    .help-form-container textarea {
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      background: #222;
      border: 1px solid #444;
      color: #fff;
      border-radius: 4px;
    }

    .help-form-container button {
      background: #D4AF37;
      border: none;
      color: #000;
      font-weight: bold;
      padding: 12px;
      width: 100%;
      margin-top: 15px;
      cursor: pointer;
      border-radius: 4px;
    }

    #closeHelpModal {
      position: absolute;
      top: 10px;
      right: 20px;
      cursor: pointer;
      font-size: 20px;
      color: #fff;
    }
  </style>
</head>
<body>

  <!-- Help Icon Button -->
  <button id="openHelpModal" title="Need Help?">❓</button>

  <!-- Help Modal -->
  <div id="helpModal">
    <div class="help-form-container">
      <span id="closeHelpModal">✖</span>
      <h2>Need Help?</h2>
    <form method="POST" action="submit_help.php">
    <label>Subject:</label>
    <input type="text" name="subject" required><br><br>

    <label>Message:</label>
    <textarea name="message" required></textarea><br><br>

    <button type="submit">Submit Help Request</button>
</form>


    </div>
  </div>

  <!-- JavaScript to control modal -->
  <script>
    const openBtn = document.getElementById("openHelpModal");
    const helpModal = document.getElementById("helpModal");
    const closeBtn = document.getElementById("closeHelpModal");

    openBtn.addEventListener("click", () => {
      helpModal.style.display = "block";
    });

    closeBtn.addEventListener("click", () => {
      helpModal.style.display = "none";
    });

    window.addEventListener("click", function(event) {
      if (event.target === helpModal) {
        helpModal.style.display = "none";
      }
    });
  </script>
</body>
</html>
