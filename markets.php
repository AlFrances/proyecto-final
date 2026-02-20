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
    <title>Markets - CurConv</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="shared.css">
    <link rel="stylesheet" href="markets.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="brand">
            <div class="brand-logo"><img src="download.png" alt="Logo"></div>
            <div class="brand-name">CurConv</div>
        </div>
        <nav class="header-nav">
            <a href="homepage.php" class="nav-link">Home</a>
            <a href="gold-rates.php" class="nav-link">Gold Rates</a>
            <a href="markets.php" class="nav-link active">Markets</a>
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
        <!-- Markets Overview -->
        <div class="markets-container">
            <div class="header">
                <h1>Live Markets</h1>
                <p>Real-time forex exchange rates</p>
                <div class="accent-line"></div>
            </div>

            <!-- Market Stats -->
            <div class="market-stats">
                <div class="stat-card">
                    <div class="stat-label">Active Pairs</div>
                    <div class="stat-value" id="activePairs">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Gainers</div>
                    <div class="stat-value stat-green" id="gainers">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Losers</div>
                    <div class="stat-value stat-red" id="losers">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Last Update</div>
                    <div class="stat-value stat-time" id="lastUpdate">--:--</div>
                </div>
            </div>

            <!-- Markets Table -->
            <div class="markets-table-wrapper">
                <div class="table-controls">
                    <button class="refresh-btn" id="refreshBtn" title="Refresh rates">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                        </svg>
                        Refresh
                    </button>
                </div>

                <table class="markets-table" id="marketsTable">
                    <thead>
                        <tr>
                            <th>Pair</th>
                            <th>Rate</th>
                            <th>Change</th>
                            <th>Change %</th>
                            <th>Trend</th>
                        </tr>
                    </thead>
                    <tbody id="marketsTableBody">
                        <tr>
                            <td colspan="5" class="loading-cell">Loading market data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="markets-info">
                <small>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    Real-time data powered by Alpha Vantage API. Rates cached for 5 minutes. Not financial advice.
                </small>
            </div>

            <!-- Advanced Market Analytics -->
            <div class="analytics-grid">

                <!-- Currency Strength Meter -->
                <div class="analytics-card">
                    <div class="card-header">
                        <h3>Currency Strength</h3>
                        <small>Based on multi-pair performance</small>
                    </div>
                    <div class="strength-meter-container" id="strengthMeter">
                        <div class="loading-text">Calculating strength...</div>
                    </div>
                </div>

                <!-- Market Volatility Heatmap -->
                <div class="analytics-card">
                    <div class="card-header">
                        <h3>Volatility Heatmap</h3>
                        <small>Live percentage changes</small>
                    </div>
                    <div class="heatmap-container" id="volatilityHeatmap">
                        <div class="loading-text">Loading heatmap...</div>
                    </div>
                </div>

                <!-- Economic Calendar Widget -->
                <div class="analytics-card calendar-card">
                    <div class="card-header">
                        <h3>Economic Calendar</h3>
                        <small>Upcoming events</small>
                    </div>
                    <div class="calendar-container" id="economicCalendar">
                        <div class="loading-text">Loading events...</div>
                    </div>
                </div>

            </div>

            <div class="analytics-info">
                <small>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    Real-time data from Alpha Vantage API. Rates cached for 5 minutes to optimize API usage. Refresh to update.
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="markets.js"></script>
</body>
</html>
