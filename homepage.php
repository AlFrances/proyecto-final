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

    <!-- Result Section (moved outside container) -->
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

    <script>
        // ========================================
        // API CONFIGURATION
        // ========================================
        const API_KEY = 'ef5910f5f4ea3dda753f47df';
        const API_URL = `https://v6.exchangerate-api.com/v6/${API_KEY}/latest/`;

        // ========================================
        // CURRENCY DATA
        // ========================================
        const currencies = [
            { code: 'USD', name: 'US Dollar', flag: 'us' },
            { code: 'EUR', name: 'Euro', flag: 'eu' },
            { code: 'GBP', name: 'British Pound', flag: 'gb' },
            { code: 'JPY', name: 'Japanese Yen', flag: 'jp' },
            { code: 'AUD', name: 'Australian Dollar', flag: 'au' },
            { code: 'CAD', name: 'Canadian Dollar', flag: 'ca' },
            { code: 'CHF', name: 'Swiss Franc', flag: 'ch' },
            { code: 'CNY', name: 'Chinese Yuan', flag: 'cn' },
            { code: 'INR', name: 'Indian Rupee', flag: 'in' },
            { code: 'MXN', name: 'Mexican Peso', flag: 'mx' },
            { code: 'PHP', name: 'Philippine Peso', flag: 'ph' },
            { code: 'SGD', name: 'Singapore Dollar', flag: 'sg' },
            { code: 'HKD', name: 'Hong Kong Dollar', flag: 'hk' },
            { code: 'KRW', name: 'South Korean Won', flag: 'kr' },
            { code: 'BRL', name: 'Brazilian Real', flag: 'br' },
            { code: 'ZAR', name: 'South African Rand', flag: 'za' },
            { code: 'THB', name: 'Thai Baht', flag: 'th' },
            { code: 'SEK', name: 'Swedish Krona', flag: 'se' },
            { code: 'NOK', name: 'Norwegian Krone', flag: 'no' },
            { code: 'NZD', name: 'New Zealand Dollar', flag: 'nz' },
            { code: 'TRY', name: 'Turkish Lira', flag: 'tr' },
            { code: 'AED', name: 'UAE Dirham', flag: 'ae' }
        ];

        // ========================================
        // CUSTOM DROPDOWN FUNCTIONALITY
        // ========================================
        function initCustomSelect(triggerId, dropdownId, listId, searchId, hiddenInputId) {
            const trigger = document.getElementById(triggerId);
            const dropdown = document.getElementById(dropdownId);
            const list = document.getElementById(listId);
            const search = document.getElementById(searchId);
            const hiddenInput = document.getElementById(hiddenInputId);
            const wrapper = trigger.closest('.custom-select-wrapper');

            // Populate currency list
            function populateList(filterText = '') {
                list.innerHTML = '';
                
                // Add "All currencies" label if no filter
                if (!filterText) {
                    const label = document.createElement('div');
                    label.className = 'currency-label';
                    label.textContent = 'All currencies';
                    list.appendChild(label);
                }
                
                const filtered = currencies.filter(c => 
                    c.code.toLowerCase().includes(filterText.toLowerCase()) ||
                    c.name.toLowerCase().includes(filterText.toLowerCase())
                );

                filtered.forEach(currency => {
                    const item = document.createElement('div');
                    item.className = 'currency-item';
                    if (currency.code === hiddenInput.value) {
                        item.classList.add('selected');
                    }
                    item.innerHTML = `
                        <img src="https://flagcdn.com/w40/${currency.flag}.png" alt="${currency.flag}" class="flag-icon">
                        <span class="currency-code">${currency.code}</span>
                        <span class="currency-name">${currency.name}</span>
                    `;
                    item.addEventListener('click', () => selectCurrency(currency));
                    list.appendChild(item);
                });
            }

            // Select currency
            function selectCurrency(currency) {
                hiddenInput.value = currency.code;
                trigger.querySelector('.flag-icon').src = `https://flagcdn.com/w40/${currency.flag}.png`;
                trigger.querySelector('.currency-code').textContent = currency.code;
                trigger.querySelector('.currency-name').textContent = currency.name;
                wrapper.classList.remove('active');
                search.value = '';
                populateList();

                // Trigger updates
                if (hiddenInputId === 'fromCurrency') {
                    updateHistoricalChart(currency.code, document.getElementById('toCurrency').value);
                    updateCompetitorRates(currency.code, document.getElementById('toCurrency').value);
                }
            }

            // Toggle dropdown
            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                wrapper.classList.toggle('active');
                if (wrapper.classList.contains('active')) {
                    search.focus();
                    populateList();
                }
            });

            // Search functionality
            search.addEventListener('input', (e) => {
                populateList(e.target.value);
            });

            // Close on outside click
            document.addEventListener('click', () => {
                wrapper.classList.remove('active');
            });

            dropdown.addEventListener('click', (e) => {
                e.stopPropagation();
            });

            // Initial population
            populateList();
        }

        // Initialize both dropdowns
        initCustomSelect('fromTrigger', 'fromDropdown', 'fromList', 'fromSearch', 'fromCurrency');
        initCustomSelect('toTrigger', 'toDropdown', 'toList', 'toSearch', 'toCurrency');

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
        // HISTORICAL CHART FUNCTIONALITY
        // ========================================
        let ratesChart = null;

        async function updateHistoricalChart(fromCurrency, toCurrency) {
            const graphSection = document.getElementById('graphSection');
            const canvas = document.getElementById('ratesChart');

            try {
                graphSection.classList.add('show');

                // Generate simulated historical data for the last 30 days
                const historicalData = await generateHistoricalData(fromCurrency, toCurrency);
                
                // Destroy existing chart
                if (ratesChart) {
                    ratesChart.destroy();
                }

                // Create new line chart
                const ctx = canvas.getContext('2d');
                ratesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: historicalData.dates,
                        datasets: [{
                            label: `${fromCurrency} to ${toCurrency}`,
                            data: historicalData.rates,
                            borderColor: 'rgba(0, 82, 204, 1)',
                            backgroundColor: 'rgba(0, 82, 204, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgba(0, 82, 204, 1)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
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
                                        size: 14,
                                        weight: '600'
                                    },
                                    color: '#2c2c2c',
                                    padding: 15
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(30, 60, 114, 0.95)',
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
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        return `Rate: ${context.parsed.y.toFixed(4)}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Poppins',
                                        size: 11
                                    },
                                    color: '#6b6b6b',
                                    padding: 10,
                                    callback: function(value) {
                                        return value.toFixed(4);
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Poppins',
                                        size: 11
                                    },
                                    color: '#6b6b6b',
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            } catch (error) {
                console.error('Error updating historical chart:', error);
            }
        }

        // Generate simulated historical data (last 30 days)
        async function generateHistoricalData(fromCurrency, toCurrency) {
            try {
                // Get current rate
                const currentRate = await getExchangeRate(fromCurrency, toCurrency);
                
                const dates = [];
                const rates = [];
                const today = new Date();

                // Generate data for last 30 days
                for (let i = 29; i >= 0; i--) {
                    const date = new Date(today);
                    date.setDate(date.getDate() - i);
                    
                    // Format date as MMM DD
                    const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    dates.push(dateStr);
                    
                    // Generate realistic rate variation (±2% from current rate)
                    const variation = (Math.random() - 0.5) * 0.04; // ±2%
                    const historicalRate = currentRate * (1 + variation);
                    rates.push(parseFloat(historicalRate.toFixed(6)));
                }

                // Set the last rate to the current rate for accuracy
                rates[rates.length - 1] = currentRate;

                return { dates, rates };
            } catch (error) {
                console.error('Error generating historical data:', error);
                return { dates: [], rates: [] };
            }
        }

        // ========================================
        // COMPETITOR RATES COMPARISON
        // ========================================
        async function updateCompetitorRates(fromCurrency, toCurrency) {
            const competitorSection = document.getElementById('competitorSection');
            const tableBody = document.getElementById('comparisonTableBody');

            try {
                competitorSection.classList.add('show');
                tableBody.innerHTML = '<tr><td colspan="5" class="loading-cell">Loading comparison...</td></tr>';

                // Get our rate (mid-market rate)
                const ourRate = await getExchangeRate(fromCurrency, toCurrency);
                const amount = 1000; // Compare on 1000 units

                // Simulate competitor rates (typically add fees/markup)
                const competitors = [
                    {
                        name: 'CurrencyX',
                        rate: ourRate,
                        feePercent: 0,
                        feeFixed: 0,
                        isOurs: true
                    },
                    {
                        name: 'Wise',
                        rate: ourRate * 0.995, // ~0.5% markup
                        feePercent: 0.004, // 0.4% fee
                        feeFixed: 0,
                        isOurs: false
                    },
                    {
                        name: 'XE',
                        rate: ourRate * 0.985, // ~1.5% markup
                        feePercent: 0,
                        feeFixed: 0,
                        isOurs: false
                    },
                    {
                        name: 'PayPal',
                        rate: ourRate * 0.965, // ~3.5% markup
                        feePercent: 0.005, // 0.5% fee
                        feeFixed: 0,
                        isOurs: false
                    }
                ];

                // Calculate values for each competitor
                const calculations = competitors.map(comp => {
                    const converted = amount * comp.rate;
                    const fee = (amount * comp.feePercent) + comp.feeFixed;
                    const received = converted - fee;
                    const markup = amount - (amount / comp.rate * ourRate);
                    
                    return {
                        ...comp,
                        converted: converted,
                        fee: fee,
                        received: received,
                        markup: markup
                    };
                });

                // Build table rows
                const rows = [
                    {
                        label: 'Recipient gets',
                        sublabel: '(Total after fees)',
                        values: calculations.map(c => ({
                            value: `${c.received.toFixed(2)} ${toCurrency}`,
                            isHighlight: c.isOurs
                        }))
                    },
                    {
                        label: 'Exchange rate',
                        sublabel: `(1 ${fromCurrency} → ${toCurrency})`,
                        values: calculations.map(c => ({
                            value: c.rate.toFixed(5),
                            isHighlight: false
                        }))
                    },
                    {
                        label: 'Exchange rate markup',
                        sublabel: '',
                        values: calculations.map(c => ({
                            value: c.markup > 0.01 ? `${c.markup.toFixed(2)} ${fromCurrency}` : `0 ${fromCurrency}`,
                            isHighlight: false
                        }))
                    },
                    {
                        label: 'Transfer fee',
                        sublabel: '',
                        values: calculations.map(c => ({
                            value: c.fee > 0 ? `${c.fee.toFixed(2)} ${fromCurrency}` : `0 ${fromCurrency}`,
                            isHighlight: false
                        }))
                    },
                    {
                        label: 'Total transfer cost',
                        sublabel: '',
                        values: calculations.map(c => ({
                            value: `${(c.fee + c.markup).toFixed(2)} ${fromCurrency}`,
                            isHighlight: false
                        }))
                    }
                ];

                // Render table
                tableBody.innerHTML = rows.map((row, index) => `
                    <tr class="${index === 0 ? 'highlight-row' : ''}">
                        <td class="row-label">
                            <div class="label-text">${row.label}</div>
                            ${row.sublabel ? `<div class="label-subtext">${row.sublabel}</div>` : ''}
                        </td>
                        ${row.values.map((val, i) => `
                            <td class="value-cell ${val.isHighlight ? 'our-value' : ''} ${calculations[i].isOurs ? 'our-col' : ''}">
                                ${val.value}
                            </td>
                        `).join('')}
                    </tr>
                `).join('');

                // Add note row
                tableBody.innerHTML += `
                    <tr class="note-row">
                        <td colspan="5" class="note-cell">
                            Sending ${fromCurrency} ${amount.toFixed(2)}
                        </td>
                    </tr>
                `;

            } catch (error) {
                console.error('Error updating competitor rates:', error);
                tableBody.innerHTML = '<tr><td colspan="5" class="error-cell">Unable to load rate comparison</td></tr>';
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
        const fromCurrencyInput = document.getElementById('fromCurrency');
        const toCurrencyInput = document.getElementById('toCurrency');
        const amountInput = document.getElementById('amount');

        // Initialize charts with USD to EUR on page load
        window.addEventListener('load', () => {
            const from = fromCurrencyInput.value;
            const to = toCurrencyInput.value;
            updateHistoricalChart(from, to);
            updateCompetitorRates(from, to);
        });

        // Swap currencies
        swapBtn.addEventListener('click', () => {
            const tempCode = fromCurrencyInput.value;
            const tempCurrency = currencies.find(c => c.code === tempCode);
            const toCurrency = currencies.find(c => c.code === toCurrencyInput.value);

            if (tempCurrency && toCurrency) {
                // Update From
                fromCurrencyInput.value = toCurrency.code;
                document.getElementById('fromTrigger').querySelector('.flag-icon').src = `https://flagcdn.com/w40/${toCurrency.flag}.png`;
                document.getElementById('fromTrigger').querySelector('.currency-code').textContent = toCurrency.code;
                document.getElementById('fromTrigger').querySelector('.currency-name').textContent = toCurrency.name;

                // Update To
                toCurrencyInput.value = tempCurrency.code;
                document.getElementById('toTrigger').querySelector('.flag-icon').src = `https://flagcdn.com/w40/${tempCurrency.flag}.png`;
                document.getElementById('toTrigger').querySelector('.currency-code').textContent = tempCurrency.code;
                document.getElementById('toTrigger').querySelector('.currency-name').textContent = tempCurrency.name;

                // Update charts
                updateHistoricalChart(toCurrency.code, tempCurrency.code);
                updateCompetitorRates(toCurrency.code, tempCurrency.code);
            }
        });

        // Update charts when "To" currency changes
        const toTrigger = document.getElementById('toTrigger');
        const originalSelectCurrency = toTrigger.onclick;
        
        // Add listener to update charts when "To" currency changes
        document.getElementById('toList').addEventListener('click', () => {
            setTimeout(() => {
                const from = fromCurrencyInput.value;
                const to = toCurrencyInput.value;
                updateHistoricalChart(from, to);
                updateCompetitorRates(from, to);
            }, 100);
        });

        // Handle form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const amount = parseFloat(amountInput.value);
            const from = fromCurrencyInput.value;
            const to = toCurrencyInput.value;

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

        // Reset button handler
        const resetBtn = document.getElementById('resetBtn');
        resetBtn.addEventListener('click', () => {
            result.classList.remove('show');
        });
    </script>
</body>
</html>