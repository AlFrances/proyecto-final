// API Configuration
const EXCHANGERATE_API_KEY = "35ac3323f08c75b689ea532f";
const EXCHANGERATE_API_URL = `https://v6.exchangerate-api.com/v6/${EXCHANGERATE_API_KEY}/latest/`;

// Alpha Vantage API for real-time forex data
const ALPHA_VANTAGE_KEY = "UWCEAC0C2CL9WA7G";
const ALPHA_VANTAGE_URL = "https://www.alphavantage.co/query";

// Cache settings (5-minute cache to respect API limits)
const CACHE_DURATION = 5 * 60 * 1000; // 5 minutes

// Major currency pairs to display
const CURRENCY_PAIRS = [
  { base: "EUR", quote: "USD", name: "EUR/USD" },
  { base: "GBP", quote: "USD", name: "GBP/USD" },
  { base: "USD", quote: "JPY", name: "USD/JPY" },
  { base: "AUD", quote: "USD", name: "AUD/USD" },
  { base: "USD", quote: "CAD", name: "USD/CAD" },
  { base: "USD", quote: "CHF", name: "USD/CHF" },
  { base: "NZD", quote: "USD", name: "NZD/USD" },
  { base: "EUR", quote: "GBP", name: "EUR/GBP" },
  { base: "EUR", quote: "JPY", name: "EUR/JPY" },
  { base: "GBP", quote: "JPY", name: "GBP/JPY" },
];

// Currency flags mapping
const currencyFlags = {
  USD: "us",
  EUR: "eu",
  GBP: "gb",
  JPY: "jp",
  AUD: "au",
  CAD: "ca",
  CHF: "ch",
  NZD: "nz",
};

let autoRefreshInterval = null;
let previousRates = {};

// Logout Handler
function handleLogout() {
  if (confirm("Are you sure you want to logout?")) {
    window.location.href = "logout.php";
  }
}

// Fetch real-time exchange rate from Alpha Vantage with caching
async function getExchangeRate(base, quote) {
  const cacheKey = `rate_${base}_${quote}`;
  const cached = localStorage.getItem(cacheKey);

  if (cached) {
    const { rate, timestamp } = JSON.parse(cached);
    // Use cached data if less than 5 minutes old
    if (Date.now() - timestamp < CACHE_DURATION) {
      console.log(`Using cached rate for ${base}/${quote}: ${rate}`);
      return rate;
    }
  }

  try {
    const url = `${ALPHA_VANTAGE_URL}?function=CURRENCY_EXCHANGE_RATE&from_currency=${base}&to_currency=${quote}&apikey=${ALPHA_VANTAGE_KEY}`;
    const response = await fetch(url);
    const data = await response.json();

    if (data["Realtime Currency Exchange Rate"]) {
      const rate = parseFloat(data["Realtime Currency Exchange Rate"]["5. Exchange Rate"]);

      // Cache the result
      localStorage.setItem(cacheKey, JSON.stringify({
        rate,
        timestamp: Date.now()
      }));

      console.log(`Fetched real-time rate for ${base}/${quote}: ${rate}`);
      return rate;
    }

    // If Alpha Vantage fails or rate limit hit, fall back to ExchangeRate-API
    console.warn(`Alpha Vantage failed for ${base}/${quote}, using fallback`);
    const fallbackResponse = await fetch(EXCHANGERATE_API_URL + base);
    const fallbackData = await fallbackResponse.json();
    if (fallbackData.result === "success") {
      const rate = fallbackData.conversion_rates[quote];

      // Cache fallback result
      localStorage.setItem(cacheKey, JSON.stringify({
        rate,
        timestamp: Date.now()
      }));

      return rate;
    }

    throw new Error("Both APIs failed");
  } catch (error) {
    console.error(`Error fetching ${base}/${quote}:`, error);
    return null;
  }
}

// Load previous rates from localStorage
function loadPreviousRates() {
  const stored = localStorage.getItem("currencyRates");
  if (stored) {
    try {
      previousRates = JSON.parse(stored);
    } catch (e) {
      previousRates = {};
    }
  }
}

// Save current rates to localStorage with demo variance for visible changes
function savePreviousRates(rates) {
  // For demo purposes, add small random variance (-0.5% to +0.5%) to saved rates
  // This ensures visible changes on each refresh for class presentations
  const DEMO_MODE = false; // Set to false for production

  if (DEMO_MODE) {
    const variedRates = {};
    for (const [pair, rate] of Object.entries(rates)) {
      // Add random variance between -0.5% and +0.5%
      const variance = (Math.random() - 0.5) * 0.01; // -0.005 to +0.005
      variedRates[pair] = rate * (1 + variance);
    }
    localStorage.setItem("currencyRates", JSON.stringify(variedRates));
  } else {
    localStorage.setItem("currencyRates", JSON.stringify(rates));
  }
}

// Calculate percentage change
function calculateChange(current, previous) {
  if (!previous) return 0;
  return ((current - previous) / previous) * 100;
}

// Format number with proper decimals
function formatRate(rate, pair) {
  // JPY pairs typically show 2 decimals, others show 4-5
  if (pair.includes("JPY")) {
    return rate.toFixed(3);
  }
  return rate.toFixed(5);
}

// Update markets table
async function updateMarketsTable() {
  const tableBody = document.getElementById("marketsTableBody");
  const lastUpdateEl = document.getElementById("lastUpdate");

  try {
    tableBody.innerHTML =
      '<tr><td colspan="5" class="loading-cell">Updating rates...</td></tr>';

    // Load previous rates for comparison
    loadPreviousRates();

    // Fetch all rates concurrently
    const ratePromises = CURRENCY_PAIRS.map(async (pair) => {
      const rate = await getExchangeRate(pair.base, pair.quote);
      return { ...pair, rate };
    });

    const results = await Promise.all(ratePromises);

    // Calculate changes and build table rows
    const currentRates = {};
    let gainersCount = 0;
    let losersCount = 0;

    const rows = results
      .filter((result) => result.rate !== null)
      .map((result) => {
        const { name, base, quote, rate } = result;
        const previousRate = previousRates[name];
        const change = previousRate ? rate - previousRate : 0;
        const changePercent = calculateChange(rate, previousRate);

        // Store current rate
        currentRates[name] = rate;

        // Count gainers and losers
        if (changePercent > 0) gainersCount++;
        if (changePercent < 0) losersCount++;

        // Determine trend direction
        let trendClass = "neutral";
        let trendIcon = "→";
        if (changePercent > 0) {
          trendClass = "up";
          trendIcon = "↑";
        } else if (changePercent < 0) {
          trendClass = "down";
          trendIcon = "↓";
        }

        const baseFlag = currencyFlags[base] || "xx";
        const quoteFlag = currencyFlags[quote] || "xx";

        return `
                    <tr class="market-row">
                        <td class="pair-cell">
                            <div class="pair-flags">
                                <img src="https://flagcdn.com/w40/${baseFlag}.png" class="flag-icon-mini" alt="${base}">
                                <img src="https://flagcdn.com/w40/${quoteFlag}.png" class="flag-icon-mini" alt="${quote}">
                            </div>
                            <strong>${name}</strong>
                        </td>
                        <td class="rate-cell">${formatRate(rate, name)}</td>
                        <td class="change-cell ${trendClass}">
                            ${change >= 0 ? "+" : ""}${change.toFixed(5)}
                        </td>
                        <td class="percent-cell ${trendClass}">
                            ${changePercent >= 0 ? "+" : ""}${changePercent.toFixed(2)}%
                        </td>
                        <td class="trend-cell">
                            <span class="trend-indicator ${trendClass}">${trendIcon}</span>
                        </td>
                    </tr>
                `;
      });

    tableBody.innerHTML = rows.join("");

    // Update stats
    document.getElementById("activePairs").textContent = results.filter(
      (r) => r.rate !== null,
    ).length;
    document.getElementById("gainers").textContent = gainersCount;
    document.getElementById("losers").textContent = losersCount;
    lastUpdateEl.textContent = new Date().toLocaleTimeString();

    // Save current rates for next comparison
    savePreviousRates(currentRates);
  } catch (error) {
    console.error("Error updating markets table:", error);
    tableBody.innerHTML =
      '<tr><td colspan="5" class="error-cell">Unable to load market data. Please try again.</td></tr>';
  }
}

// Auto-refresh functionality
function setupAutoRefresh() {
  const autoRefreshCheckbox = document.getElementById("autoRefresh");

  function startAutoRefresh() {
    if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    autoRefreshInterval = setInterval(() => {
      if (autoRefreshCheckbox.checked) {
        updateMarketsTable();
      }
    }, 30000); // 30 seconds
  }

  function stopAutoRefresh() {
    if (autoRefreshInterval) {
      clearInterval(autoRefreshInterval);
      autoRefreshInterval = null;
    }
  }

  autoRefreshCheckbox.addEventListener("change", (e) => {
    if (e.target.checked) {
      startAutoRefresh();
    } else {
      stopAutoRefresh();
    }
  });

  // Start auto-refresh if checkbox is checked
  if (autoRefreshCheckbox.checked) {
    startAutoRefresh();
  }
}

// Manual refresh button
document.getElementById("refreshBtn").addEventListener("click", () => {
  // Clear cache to force fresh data on manual refresh
  CURRENCY_PAIRS.forEach(pair => {
    const cacheKey = `rate_${pair.base}_${pair.quote}`;
    localStorage.removeItem(cacheKey);
  });
  updateMarketsTable();
});

// Initialize demo data for first-time load (for demo purposes)
function initializeDemoData() {
  const DEMO_VERSION = "real_v1"; // Change this to force reset
  const currentVersion = localStorage.getItem("demoVersion");

  // Reset if version changed or not set
  if (currentVersion !== DEMO_VERSION) {
    console.log("Resetting demo data...");

    // Clear all old market data
    localStorage.removeItem("currencyRates");
    CURRENCY_PAIRS.forEach(pair => {
      localStorage.removeItem(`rate_${pair.base}_${pair.quote}`);
      localStorage.removeItem(`${pair.name}_prev`);
      localStorage.removeItem(`heatmap_${pair.name}`);
    });

    // Set baseline rates (offset for visible demo changes)
    const demoRates = {
      "EUR/USD": 1.08500,
      "GBP/USD": 1.26300,
      "USD/JPY": 147.500,
      "AUD/USD": 0.65800,
      "USD/CAD": 1.35200,
      "USD/CHF": 0.88500,
      "NZD/USD": 0.59800,
      "EUR/GBP": 0.85900,
      "EUR/JPY": 160.100,
      "GBP/JPY": 186.200
    };

    localStorage.setItem("currencyRates", JSON.stringify(demoRates));
    localStorage.setItem("demoVersion", DEMO_VERSION);
    console.log("Demo baseline data initialized with version", DEMO_VERSION);
  }
}

// Initialize on page load
window.addEventListener("load", () => {
  initializeDemoData(); // Set up demo baseline data
  updateMarketsTable();
  // setupAutoRefresh(); // Disabled to reduce API requests
});

// Cleanup on page unload (disabled - auto-refresh not in use)
// window.addEventListener("beforeunload", () => {
//   if (autoRefreshInterval) {
//     clearInterval(autoRefreshInterval);
//   }
// });

// ===== ADVANCED ANALYTICS COMPONENTS =====

// Currency Strength Meter
async function calculateCurrencyStrength() {
  const baseCurrencies = ["USD", "EUR", "GBP", "JPY", "AUD", "CAD", "CHF"];
  const strengthData = {};

  try {
    // Initialize strength scores
    baseCurrencies.forEach((currency) => {
      strengthData[currency] = { wins: 0, total: 0 };
    });

    // Compare each currency against all others
    // Use ExchangeRate-API for strength calculation to save Alpha Vantage calls
    for (const base of baseCurrencies) {
      const response = await fetch(EXCHANGERATE_API_URL + base);
      const data = await response.json();

      if (data.result === "success") {
        baseCurrencies.forEach((quote) => {
          if (base !== quote) {
            const rate = data.conversion_rates[quote];
            const previousKey = `${base}/${quote}_prev`;
            const previousRate = localStorage.getItem(previousKey);

            // Always count this comparison
            strengthData[base].total++;

            if (previousRate) {
              const prevRate = parseFloat(previousRate);
              // Real comparison - check if rate increased
              if (rate > prevRate) {
                strengthData[base].wins++;
              }
            } else {
              // No previous data - assume neutral (50% chance)
              if (Math.random() > 0.5) {
                strengthData[base].wins++;
              }
            }

            localStorage.setItem(previousKey, rate.toString());
          }
        });
      }
    }

    // Calculate strength percentages
    const strengthResults = baseCurrencies
      .map((currency) => {
        const { wins, total } = strengthData[currency];
        const strength = total > 0 ? (wins / total) * 100 : 50;
        console.log(`${currency}: ${wins}/${total} wins = ${strength.toFixed(0)}%`);
        return { currency, strength };
      })
      .sort((a, b) => b.strength - a.strength);

    return strengthResults;
  } catch (error) {
    console.error("Error calculating currency strength:", error);
    return [];
  }
}

function renderCurrencyStrength(strengthData) {
  const container = document.getElementById("strengthMeter");

  if (strengthData.length === 0) {
    container.innerHTML = '<div class="loading-text">Unable to calculate strength</div>';
    return;
  }

  const html = strengthData
    .map((item) => {
      const isStrong = item.strength >= 50;
      const barClass = isStrong ? "strong" : "weak";

      return `
                <div class="strength-item">
                    <div class="strength-label">${item.currency}</div>
                    <div class="strength-bar-wrapper">
                        <div class="strength-bar ${barClass}" style="width: ${item.strength}%">
                            <span class="strength-value">${item.strength.toFixed(0)}%</span>
                        </div>
                    </div>
                </div>
            `;
    })
    .join("");

  container.innerHTML = html;
}

// Market Volatility Heatmap
async function generateVolatilityHeatmap() {
  const topPairs = [
    { base: "EUR", quote: "USD", name: "EUR/USD" },
    { base: "GBP", quote: "USD", name: "GBP/USD" },
    { base: "USD", quote: "JPY", name: "USD/JPY" },
    { base: "AUD", quote: "USD", name: "AUD/USD" },
    { base: "USD", quote: "CAD", name: "USD/CAD" },
    { base: "USD", quote: "CHF", name: "USD/CHF" },
    { base: "EUR", quote: "GBP", name: "EUR/GBP" },
    { base: "EUR", quote: "JPY", name: "EUR/JPY" },
    { base: "GBP", quote: "JPY", name: "GBP/JPY" },
    { base: "NZD", quote: "USD", name: "NZD/USD" },
  ];

  try {
    const heatmapData = await Promise.all(
      topPairs.map(async (pair) => {
        try {
          const rate = await getExchangeRate(pair.base, pair.quote);
          const prevKey = `heatmap_${pair.name}`;
          const previousRate = localStorage.getItem(prevKey);

          let change = 0;

          if (rate && previousRate) {
            // Calculate real change from stored previous rate
            const prevRate = parseFloat(previousRate);
            change = ((rate - prevRate) / prevRate) * 100;
          } else if (rate) {
            // First load - show 0% until we have comparison data
            change = 0;
          }

          // Store current rate for next comparison (only if valid)
          if (rate) {
            localStorage.setItem(prevKey, rate.toString());
          }

          console.log(`${pair.name}: real-time change = ${change.toFixed(2)}% (rate: ${rate || 'N/A'})`);

          return {
            name: pair.name,
            change,
          };
        } catch (error) {
          console.error(`Error fetching ${pair.name}:`, error);
          // Return 0% if fetch fails
          return {
            name: pair.name,
            change: 0,
          };
        }
      }),
    );

    return heatmapData;
  } catch (error) {
    console.error("Error generating volatility heatmap:", error);
    return [];
  }
}

function renderVolatilityHeatmap(heatmapData) {
  const container = document.getElementById("volatilityHeatmap");

  if (heatmapData.length === 0) {
    container.innerHTML = '<div class="loading-text">Unable to load heatmap</div>';
    return;
  }

  const html = heatmapData
    .map((item) => {
      const isPositive = item.change >= 0;
      const cellClass = isPositive ? "positive" : "negative";
      const changeClass = isPositive ? "positive" : "negative";
      const sign = isPositive ? "+" : "";

      return `
                <div class="heatmap-cell ${cellClass}">
                    <div class="heatmap-pair">${item.name}</div>
                    <div class="heatmap-change ${changeClass}">
                        ${sign}${item.change.toFixed(2)}%
                    </div>
                </div>
            `;
    })
    .join("");

  container.innerHTML = `<div class="heatmap-grid">${html}</div>`;
}

// Economic Calendar
function generateEconomicCalendar() {
  // Mock economic calendar data
  // In production, fetch from a real economic calendar API (e.g., ForexFactory, Investing.com, etc.)
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);

  const events = [
    {
      time: "14:30",
      title: "US Non-Farm Payrolls",
      country: "USD",
      impact: "high",
      date: today,
    },
    {
      time: "12:00",
      title: "ECB Interest Rate Decision",
      country: "EUR",
      impact: "high",
      date: today,
    },
    {
      time: "08:30",
      title: "UK GDP Growth Rate",
      country: "GBP",
      impact: "medium",
      date: today,
    },
    {
      time: "23:50",
      title: "Japan BOJ Policy Rate",
      country: "JPY",
      impact: "high",
      date: today,
    },
    {
      time: "13:00",
      title: "US Fed Chair Speech",
      country: "USD",
      impact: "medium",
      date: tomorrow,
    },
    {
      time: "09:00",
      title: "German Manufacturing PMI",
      country: "EUR",
      impact: "medium",
      date: tomorrow,
    },
    {
      time: "15:00",
      title: "Canada Employment Change",
      country: "CAD",
      impact: "high",
      date: tomorrow,
    },
    {
      time: "07:00",
      title: "Australia Retail Sales",
      country: "AUD",
      impact: "low",
      date: tomorrow,
    },
  ];

  // Sort by date and time
  events.sort((a, b) => {
    if (a.date.getTime() === b.date.getTime()) {
      return a.time.localeCompare(b.time);
    }
    return a.date.getTime() - b.date.getTime();
  });

  return events;
}

function renderEconomicCalendar(events) {
  const container = document.getElementById("economicCalendar");

  if (events.length === 0) {
    container.innerHTML = '<div class="loading-text">No upcoming events</div>';
    return;
  }

  const html = events
    .map((event) => {
      const dateLabel =
        event.date.toDateString() === new Date().toDateString()
          ? "Today"
          : "Tomorrow";

      return `
                <div class="calendar-event ${event.impact}-impact">
                    <div class="event-time">${event.time}</div>
                    <div class="event-details">
                        <div class="event-title">${event.title}</div>
                        <div class="event-country">${event.country} • ${dateLabel}</div>
                    </div>
                    <div class="impact-badge ${event.impact}">${event.impact}</div>
                </div>
            `;
    })
    .join("");

  container.innerHTML = `<div class="calendar-list">${html}</div>`;
}

// Initialize advanced analytics
async function initializeAnalytics() {
  try {
    // Currency Strength
    const strengthData = await calculateCurrencyStrength();
    renderCurrencyStrength(strengthData);

    // Volatility Heatmap
    const heatmapData = await generateVolatilityHeatmap();
    renderVolatilityHeatmap(heatmapData);

    // Economic Calendar
    const calendarEvents = generateEconomicCalendar();
    renderEconomicCalendar(calendarEvents);
  } catch (error) {
    console.error("Error initializing analytics:", error);
  }
}

// Update analytics on page load and with markets data
const originalUpdateMarketsTable = updateMarketsTable;
updateMarketsTable = async function () {
  await originalUpdateMarketsTable();
  await initializeAnalytics();
};
