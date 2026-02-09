<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Include database connection
include 'connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gold Rates - CurrencyX</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="homepage.css">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="brand">
            <div class="brand-logo">₵</div>
            <div class="brand-name">CurrencyX</div>
        </div>
        <nav class="header-nav">
            <a href="homepage.php" class="nav-link">Home</a>
            <a href="gold-rates.php" class="nav-link active">Gold Rates</a>
            <a href="markets.php" class="nav-link">Markets</a>
        </nav>
        <div class="user-section">
            <div class="user-info">
                <div class="user-avatar" id="userAvatar">
                    <?php
                    try {
                        if (isset($_SESSION['email'])) {
                            $email = $_SESSION['email'];
                            $stmt = $conn->prepare("SELECT firstName, lastName FROM users WHERE email = ?");
                            $stmt->bind_param("s", $email);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result && mysqli_num_rows($result) === 1) {
                                $row = mysqli_fetch_assoc($result);
                                echo htmlspecialchars(substr($row['firstName'], 0, 1));
                            } else {
                                echo "U";
                            }
                        } else {
                            echo "U";
                        }
                    } catch (Exception $e) {
                        echo "U";
                    }
                    ?>
                </div>
                <div class="user-name" id="userName">
                    <?php
                    try {
                        if (isset($_SESSION['email'])) {
                            $email = $_SESSION['email'];
                            $stmt = $conn->prepare("SELECT firstName, lastName FROM users WHERE email = ?");
                            $stmt->bind_param("s", $email);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if ($result && mysqli_num_rows($result) === 1) {
                                $row = mysqli_fetch_assoc($result);
                                echo htmlspecialchars($row['firstName']) . " " . htmlspecialchars($row['lastName']);
                            } else {
                                echo "User";
                            }
                        } else {
                            echo "User";
                        }
                    } catch (Exception $e) {
                        echo "User";
                    }
                    ?>
                </div>
            </div>
            <button class="logout-btn" onclick="handleLogout()">Logout</button>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Gold Price Table -->
        <div class="gold-container">
            <div class="header">
                <h1>Gold Prices</h1>
                <p>Live gold rates in multiple currencies</p>
                <div class="accent-line"></div>
            </div>

            <div class="gold-selector">
                <label for="goldAmount">Gold Weight</label>
                <div class="gold-input-group">
                    <input
                        type="number"
                        id="goldAmount"
                        name="goldAmount"
                        placeholder="Enter weight"
                        value="1"
                        step="0.01"
                        min="0.01"
                    >
                    <select id="goldUnit" name="goldUnit">
                        <option value="oz">Troy Ounce</option>
                        <option value="gram">Gram</option>
                        <option value="kg">Kilogram</option>
                    </select>
                </div>
                <button type="button" class="refresh-gold-btn" id="refreshGoldBtn" title="Refresh gold prices">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                    </svg>
                    Refresh
                </button>
            </div>

            <div class="gold-table-wrapper">
                <table class="gold-table" id="goldTable">
                    <thead>
                        <tr>
                            <th>Currency</th>
                            <th>Price per Oz</th>
                            <th>Your Amount</th>
                        </tr>
                    </thead>
                    <tbody id="goldTableBody">
                        <tr>
                            <td colspan="3" class="loading-cell">Loading gold prices...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="gold-info">
                <small id="goldUpdateTime">Last updated: Loading...</small>
            </div>
        </div>
    </div>

    <script src="gold-rates.js"></script>
</body>
</html>
