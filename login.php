<?php
session_start();

$SUPABASE_URL = getenv('SUPABASE_URL');
$SUPABASE_ANON_KEY = getenv('SUPABASE_ANON_KEY');


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // login data
    $data = [
        "email" => $email,
        "password" => $password
    ];

    // create request
    $ch = curl_init();

    curl_setopt(
        $ch,
        CURLOPT_URL,
        $SUPABASE_URL . "/auth/v1/token?grant_type=password"
    );

    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt(
        $ch,
        CURLOPT_POSTFIELDS,
        json_encode($data)
    );

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $SUPABASE_ANON_KEY",
        "Content-Type: application/json"
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // execute request
    $response = curl_exec($ch);

    curl_close($ch);

    $result = json_decode($response, true);

    // login success
    if (isset($result['access_token'])) {

        session_regenerate_id(true);

        $_SESSION['user'] = $result['user']['email'];
        $_SESSION['token'] = $result['access_token'];

        header("Location: index.php");
        exit();

    } else {

        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="login">

<div class="login-box">

    <h2>Sign In</h2>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Email address"
            required>

        <br><br>

        <input
            type="password"
            name="password"
            placeholder="Password"
            required>

        <br><br>

        <button type="submit">
            Sign In
        </button>

    </form>

    <?php if (isset($error)) : ?>
        <p class="error">
            <?php echo htmlspecialchars($error); ?>
        </p>
    <?php endif; ?>

</div>

</body>
</html>