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
                    <select id="fromCurrency" name="fromCurrency" required>
                        <option value="USD" selected>🇺🇸 USD - US Dollar</option>
                        <option value="EUR">🇪🇺 EUR - Euro</option>
                        <option value="GBP">🇬🇧 GBP - British Pound</option>
                        <option value="JPY">🇯🇵 JPY - Japanese Yen</option>
                        <option value="AUD">🇦🇺 AUD - Australian Dollar</option>
                        <option value="CAD">🇨🇦 CAD - Canadian Dollar</option>
                        <option value="CHF">🇨🇭 CHF - Swiss Franc</option>
                        <option value="CNY">🇨🇳 CNY - Chinese Yuan</option>
                        <option value="INR">🇮🇳 INR - Indian Rupee</option>
                        <option value="MXN">🇲🇽 MXN - Mexican Peso</option>
                        <option value="PHP">🇵🇭 PHP - Philippine Peso</option>
                        <option value="SGD">🇸🇬 SGD - Singapore Dollar</option>
                        <option value="HKD">🇭🇰 HKD - Hong Kong Dollar</option>
                        <option value="KRW">🇰🇷 KRW - South Korean Won</option>
                        <option value="BRL">🇧🇷 BRL - Brazilian Real</option>
                        <option value="ZAR">🇿🇦 ZAR - South African Rand</option>
                        <option value="THB">🇹🇭 THB - Thai Baht</option>
                        <option value="SEK">🇸🇪 SEK - Swedish Krona</option>
                        <option value="NOK">🇳🇴 NOK - Norwegian Krone</option>
                        <option value="NZD">🇳🇿 NZD - New Zealand Dollar</option>
                        <option value="TRY">🇹🇷 TRY - Turkish Lira</option>
                        <option value="AED">🇦🇪 AED - UAE Dirham</option>
                    </select>
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
                <select id="toCurrency" name="toCurrency" required>
                    <option value="USD">🇺🇸 USD - US Dollar</option>
                    <option value="EUR" selected>🇪🇺 EUR - Euro</option>
                    <option value="GBP">🇬🇧 GBP - British Pound</option>
                    <option value="JPY">🇯🇵 JPY - Japanese Yen</option>
                    <option value="AUD">🇦🇺 AUD - Australian Dollar</option>
                    <option value="CAD">🇨🇦 CAD - Canadian Dollar</option>
                    <option value="CHF">🇨🇭 CHF - Swiss Franc</option>
                    <option value="CNY">🇨🇳 CNY - Chinese Yuan</option>
                    <option value="INR">🇮🇳 INR - Indian Rupee</option>
                    <option value="MXN">🇲🇽 MXN - Mexican Peso</option>
                    <option value="PHP">🇵🇭 PHP - Philippine Peso</option>
                    <option value="SGD">🇸🇬 SGD - Singapore Dollar</option>
                    <option value="HKD">🇭🇰 HKD - Hong Kong Dollar</option>
                    <option value="KRW">🇰🇷 KRW - South Korean Won</option>
                    <option value="BRL">🇧🇷 BRL - Brazilian Real</option>
                    <option value="ZAR">🇿🇦 ZAR - South African Rand</option>
                    <option value="THB">🇹🇭 THB - Thai Baht</option>
                    <option value="SEK">🇸🇪 SEK - Swedish Krona</option>
                    <option value="NOK">🇳🇴 NOK - Norwegian Krone</option>
                    <option value="NZD">🇳🇿 NZD - New Zealand Dollar</option>
                    <option value="TRY">🇹🇷 TRY - Turkish Lira</option>
                    <option value="AED">🇦🇪 AED - UAE Dirham</option>
                </select>
            </div>

            <button type="submit" class="convert-btn">Convert</button>
        </form>

        <div class="result" id="result">
            <div class="result-label">Converted Amount</div>
            <div class="result-value" id="resultValue">0.00</div>
            <div class="result-details" id="resultDetails">Awaiting conversion...</div>
        </div>
    </div>

    <!-- Graph Section (Outside Container) -->
    <div class="graph-section" id="graphSection">
        <h3 class="graph-title">Live Exchange Rates</h3>
        <div class="graph-canvas-container">
            <canvas id="ratesChart"></canvas>
        </div>
    </div>

    </div> <!-- Close main-content -->

    <script>
        // ========================================
        // API CONFIGURATION
        // ========================================
        const API_KEY = 'ef5910f5f4ea3dda753f47df';
        const API_URL = `https://v6.exchangerate-api.com/v6/${API_KEY}/latest/`;

        // ========================================
        // LOGOUT HANDLER
        // ========================================
        function handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }

        // ========================================
        // CURRENCY CONVERSION WITH LIVE API
        // ========================================
        async function getExchangeRate(from, to) {
            try {
                const response = await fetch(API_URL + from);
                const data = await response.json();
                
                if (data.result === 'success') {
                    return data.conversion_rates[to];
                } else {
                    throw new Error('API request failed');
                }
            } catch (error) {
                console.error('Error fetching exchange rate:', error);
                throw error;
            }
        }

        // ========================================
        // GRAPH FUNCTIONALITY
        // ========================================
        let ratesChart = null;

        async function updateGraph(baseCurrency) {
            const graphSection = document.getElementById('graphSection');
            const canvas = document.getElementById('ratesChart');
            
            try {
                // Show loading state
                graphSection.classList.add('show');
                
                // Fetch live rates
                const response = await fetch(API_URL + baseCurrency);
                const data = await response.json();
                
                if (data.result === 'success') {
                    // Get popular currencies for the graph - expanded list
                    const popularCurrencies = ['EUR', 'GBP', 'JPY', 'AUD', 'CAD', 'CHF', 'CNY', 'INR', 'PHP', 'SGD', 'HKD', 'KRW', 'BRL', 'ZAR', 'THB', 'MXN', 'SEK', 'NOK', 'NZD', 'TRY', 'AED'];
                    const currencies = [];
                    const rates = [];
                    
                    popularCurrencies.forEach(currency => {
                        if (data.conversion_rates[currency] && currency !== baseCurrency) {
                            currencies.push(currency);
                            rates.push(data.conversion_rates[currency]);
                        }
                    });
                    
                    // Destroy existing chart if it exists
                    if (ratesChart) {
                        ratesChart.destroy();
                    }
                    
                    // Create new chart
                    const ctx = canvas.getContext('2d');
                    ratesChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: currencies,
                            datasets: [{
                                label: `${baseCurrency} Exchange Rates`,
                                data: rates,
                                backgroundColor: [
                                    'rgba(30, 60, 114, 0.7)',
                                    'rgba(42, 82, 152, 0.7)',
                                    'rgba(74, 144, 226, 0.7)',
                                    'rgba(0, 82, 204, 0.7)',
                                    'rgba(30, 60, 114, 0.8)',
                                    'rgba(42, 82, 152, 0.8)',
                                    'rgba(74, 144, 226, 0.8)',
                                    'rgba(0, 82, 204, 0.8)',
                                    'rgba(30, 60, 114, 0.6)',
                                    'rgba(42, 82, 152, 0.6)',
                                    'rgba(74, 144, 226, 0.6)',
                                    'rgba(0, 82, 204, 0.6)',
                                    'rgba(30, 60, 114, 0.75)',
                                    'rgba(42, 82, 152, 0.75)',
                                    'rgba(74, 144, 226, 0.75)'
                                ],
                                borderColor: [
                                    'rgba(30, 60, 114, 1)',
                                    'rgba(42, 82, 152, 1)',
                                    'rgba(74, 144, 226, 1)',
                                    'rgba(0, 82, 204, 1)',
                                    'rgba(30, 60, 114, 1)',
                                    'rgba(42, 82, 152, 1)',
                                    'rgba(74, 144, 226, 1)',
                                    'rgba(0, 82, 204, 1)',
                                    'rgba(30, 60, 114, 1)',
                                    'rgba(42, 82, 152, 1)',
                                    'rgba(74, 144, 226, 1)',
                                    'rgba(0, 82, 204, 1)',
                                    'rgba(30, 60, 114, 1)',
                                    'rgba(42, 82, 152, 1)',
                                    'rgba(74, 144, 226, 1)'
                                ],
                                borderWidth: 2,
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        font: {
                                            family: 'Poppins',
                                            size: 12,
                                            weight: '600'
                                        },
                                        color: '#2c2c2c'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(30, 60, 114, 0.9)',
                                    titleFont: {
                                        family: 'Poppins',
                                        size: 14,
                                        weight: '600'
                                    },
                                    bodyFont: {
                                        family: 'Poppins',
                                        size: 13
                                    },
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: {
                                        label: function(context) {
                                            return `1 ${baseCurrency} = ${context.parsed.y.toFixed(4)} ${context.label}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    },
                                    ticks: {
                                        font: {
                                            family: 'Poppins',
                                            size: 11
                                        },
                                        color: '#6b6b6b'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: {
                                            family: 'Poppins',
                                            size: 12,
                                            weight: '600'
                                        },
                                        color: '#2c2c2c'
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error updating graph:', error);
            }
        }

        // ========================================
        // FORM HANDLERS
        // ========================================
        const form = document.getElementById('converterForm');
        const swapBtn = document.getElementById('swapBtn');
        const result = document.getElementById('result');
        const resultValue = document.getElementById('resultValue');
        const resultDetails = document.getElementById('resultDetails');
        const fromCurrency = document.getElementById('fromCurrency');
        const toCurrency = document.getElementById('toCurrency');
        const amountInput = document.getElementById('amount');

        // Initialize graph with USD on page load
        window.addEventListener('load', () => {
            updateGraph('USD');
        });

        // Update graph when "from" currency changes
        fromCurrency.addEventListener('change', () => {
            updateGraph(fromCurrency.value);
        });

        // Swap currencies
        swapBtn.addEventListener('click', () => {
            const temp = fromCurrency.value;
            fromCurrency.value = toCurrency.value;
            toCurrency.value = temp;
            
            // Update graph with new base currency
            updateGraph(fromCurrency.value);
        });

        // Handle form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const amount = parseFloat(amountInput.value);
            const from = fromCurrency.value;
            const to = toCurrency.value;

            if (isNaN(amount) || amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            try {
                // Get exchange rate from live API
                const rate = await getExchangeRate(from, to);
                const convertedAmount = amount * rate;

                // Display result
                resultValue.textContent = convertedAmount.toFixed(2) + ' ' + to;
                resultDetails.textContent = `${amount.toFixed(2)} ${from} = ${convertedAmount.toFixed(2)} ${to} (Rate: ${rate.toFixed(4)})`;
                result.classList.add('show');
            } catch (error) {
                console.error('Conversion error:', error);
                resultValue.textContent = 'Error';
                resultDetails.textContent = 'Unable to convert. Please check your internet connection.';
                result.classList.add('show');
            }
        });
    </script>
</body>
</html>