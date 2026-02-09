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
    <title>Currency Converter</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="homepage.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="brand">
            <div class="brand-logo">₵</div>
            <div class="brand-name">CurrencyX</div>
        </div>
        <nav class="header-nav">
            <a href="homepage.php" class="nav-link active">Home</a>
            <a href="gold-rates.php" class="nav-link">Gold Rates</a>
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
    
    <!-- Converter and Result Grid -->
    <div class="converter-result-grid">
        
    <!-- Main Currency Converter -->
    <div class="container">
        <div class="header">
            <h1>Currency Converter</h1>
            <p>Convert between currencies in real-time</p>
            <div class="accent-line"></div>
        </div>

        <form class="converter-form" id="converterForm">
            <div class="currency-row">
                <div class="input-group">
                    <label for="amount">Amount</label>
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount" 
                        placeholder="Enter amount" 
                        value="100"
                        step="0.01"
                        required
                    >
                </div>

                <div class="input-group">
                    <label for="fromCurrency">From</label>
                    <div class="custom-select-wrapper">
                        <div class="custom-select-trigger" id="fromTrigger">
                            <img src="https://flagcdn.com/w40/us.png" alt="US Flag" class="flag-icon">
                            <span class="currency-code">USD</span>
                            <span class="currency-name">US Dollar</span>
                            <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 12 12">
                                <path fill="currentColor" d="M6 9L1 4h10z"/>
                            </svg>
                        </div>
                        <div class="custom-select-dropdown" id="fromDropdown">
                            <div class="currency-search">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                                <input type="text" placeholder="Search currencies..." id="fromSearch">
                            </div>
                            <div class="currency-list" id="fromList"></div>
                        </div>
                        <input type="hidden" id="fromCurrency" name="fromCurrency" value="USD">
                    </div>
                </div>
            </div>

            <div class="swap-container">
                <button type="button" class="swap-btn" id="swapBtn" title="Swap currencies">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 16V4M7 4L3 8M7 4L11 8M17 8V20M17 20L21 16M17 20L13 16"/>
                    </svg>
                </button>
            </div>

            <div class="input-group">
                <label for="toCurrency">To</label>
                <div class="custom-select-wrapper">
                    <div class="custom-select-trigger" id="toTrigger">
                        <img src="https://flagcdn.com/w40/eu.png" alt="EU Flag" class="flag-icon">
                        <span class="currency-code">EUR</span>
                        <span class="currency-name">Euro</span>
                        <svg class="dropdown-arrow" width="12" height="12" viewBox="0 0 12 12">
                            <path fill="currentColor" d="M6 9L1 4h10z"/>
                        </svg>
                    </div>
                    <div class="custom-select-dropdown" id="toDropdown">
                        <div class="currency-search">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                            <input type="text" placeholder="Search currencies..." id="toSearch">
                        </div>
                        <div class="currency-list" id="toList"></div>
                    </div>
                    <input type="hidden" id="toCurrency" name="toCurrency" value="EUR">

                    <button type="submit" class="convert-btn">Convert</button>
                </div>
            </div>
            </div>
        </form>
    </div>

    <!-- Result Section -->
    <div class="result" id="result">
        <button class="reset-btn" id="resetBtn" title="Close result">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>
        <div class="result-label">Converted Amount</div>
        <div class="result-value" id="resultValue">0.00</div>
        <div class="result-details" id="resultDetails">Awaiting conversion...</div>
    </div>

    </div> <!-- End converter-result-grid -->

    <!-- Historical Rate Chart Section -->
    <div class="graph-section" id="graphSection">
        <h3 class="graph-title">30-Day Exchange Rate History</h3>
        <div class="graph-canvas-container">
            <canvas id="ratesChart"></canvas>
        </div>
    </div>

    <!-- Competitor Rates Comparison Section -->
    <div class="competitor-section" id="competitorSection">
        <h3 class="competitor-title">Compare Exchange Rates</h3>
        <p class="competitor-subtitle">See how our rates compare to other providers</p>
        
        <div class="comparison-table-wrapper">
            <table class="comparison-table" id="comparisonTable">
                <thead>
                    <tr>
                        <th class="row-label"></th>
                        <th class="provider-col our-provider">
                            <div class="provider-header">
                                <div class="provider-logo">₵</div>
                                <div class="provider-name">CurrencyX</div>
                            </div>
                        </th>
                        <th class="provider-col">
                            <div class="provider-header">
                                <div class="provider-logo">💸</div>
                                <div class="provider-name">Wise</div>
                            </div>
                        </th>
                        <th class="provider-col">
                            <div class="provider-header">
                                <div class="provider-logo">🌐</div>
                                <div class="provider-name">XE</div>
                            </div>
                        </th>
                        <th class="provider-col">
                            <div class="provider-header">
                                <div class="provider-logo">💳</div>
                                <div class="provider-name">PayPal</div>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody id="comparisonTableBody">
                    <tr>
                        <td colspan="5" class="loading-cell">Loading comparison...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    </div> <!-- Close main-content -->

    <script src="homepage.js"></script>
</body>
</html>