// API Configuration
const API_KEY = "1e7bed46857db1854e506c15";
const API_URL = `https://v6.exchangerate-api.com/v6/${API_KEY}/latest/`;

// Currency Data
const currencies = [
  { code: "USD", name: "US Dollar", flag: "us" },
  { code: "EUR", name: "Euro", flag: "eu" },
  { code: "GBP", name: "British Pound", flag: "gb" },
  { code: "JPY", name: "Japanese Yen", flag: "jp" },
  { code: "AUD", name: "Australian Dollar", flag: "au" },
  { code: "CAD", name: "Canadian Dollar", flag: "ca" },
  { code: "CHF", name: "Swiss Franc", flag: "ch" },
  { code: "CNY", name: "Chinese Yuan", flag: "cn" },
  { code: "INR", name: "Indian Rupee", flag: "in" },
  { code: "MXN", name: "Mexican Peso", flag: "mx" },
  { code: "PHP", name: "Philippine Peso", flag: "ph" },
  { code: "SGD", name: "Singapore Dollar", flag: "sg" },
  { code: "HKD", name: "Hong Kong Dollar", flag: "hk" },
  { code: "KRW", name: "South Korean Won", flag: "kr" },
  { code: "BRL", name: "Brazilian Real", flag: "br" },
  { code: "ZAR", name: "South African Rand", flag: "za" },
  { code: "THB", name: "Thai Baht", flag: "th" },
  { code: "SEK", name: "Swedish Krona", flag: "se" },
  { code: "NOK", name: "Norwegian Krone", flag: "no" },
  { code: "NZD", name: "New Zealand Dollar", flag: "nz" },
  { code: "TRY", name: "Turkish Lira", flag: "tr" },
  { code: "AED", name: "UAE Dirham", flag: "ae" },
];

// Custom Dropdown Functionality
// Portal pattern: dropdown is appended to <body> so it escapes all
// stacking contexts created by backdrop-filter/transform on parent cards.
function initCustomSelect(triggerId, dropdownId, listId, searchId, hiddenInputId) {
  const trigger = document.getElementById(triggerId);
  const dropdown = document.getElementById(dropdownId);
  const list = document.getElementById(listId);
  const search = document.getElementById(searchId);
  const hiddenInput = document.getElementById(hiddenInputId);
  const wrapper = trigger.closest(".custom-select-wrapper");

  // Move dropdown out of the card and into <body>
  document.body.appendChild(dropdown);
  dropdown.style.position = "absolute";
  dropdown.style.zIndex = "99999";
  dropdown.style.display = "none";
  dropdown.style.margin = "0";

  const DROPDOWN_MAX_HEIGHT = 380; // search bar ~50px + label ~30px + list 320px - max expected

  const positionDropdown = () => {
    const rect = trigger.getBoundingClientRect();
    const spaceBelow = window.innerHeight - rect.bottom - 16;
    const spaceAbove = rect.top - 16;

    let top;
    if (spaceBelow >= DROPDOWN_MAX_HEIGHT || spaceBelow >= spaceAbove) {
      // Enough room below — open downward
      top = rect.bottom + window.scrollY + 8;
    } else {
      // Not enough room below — flip upward
      top = rect.top + window.scrollY - DROPDOWN_MAX_HEIGHT - 8;
    }

    dropdown.style.top   = top + "px";
    dropdown.style.left  = (rect.left + window.scrollX) + "px";
    dropdown.style.width = rect.width + "px";
  };

  const closeAll = () => {
    document.querySelectorAll(".custom-select-wrapper.active")
      .forEach(w => w.classList.remove("active"));
    document.querySelectorAll(".custom-select-dropdown")
      .forEach(d => { d.style.display = "none"; });
  };

  const populateList = (filterText = "") => {
    list.innerHTML = "";
    if (!filterText) {
      list.innerHTML = '<div class="currency-label">All currencies</div>';
    }
    currencies
      .filter(c =>
        c.code.toLowerCase().includes(filterText.toLowerCase()) ||
        c.name.toLowerCase().includes(filterText.toLowerCase())
      )
      .forEach(currency => {
        const item = document.createElement("div");
        item.className = "currency-item" + (currency.code === hiddenInput.value ? " selected" : "");
        item.innerHTML = `
          <img src="https://flagcdn.com/w40/${currency.flag}.png" alt="${currency.flag}" class="flag-icon">
          <span class="currency-code">${currency.code}</span>
          <span class="currency-name">${currency.name}</span>`;
        item.addEventListener("click", () => {
          hiddenInput.value = currency.code;
          trigger.querySelector(".flag-icon").src = `https://flagcdn.com/w40/${currency.flag}.png`;
          trigger.querySelector(".currency-code").textContent = currency.code;
          trigger.querySelector(".currency-name").textContent = currency.name;
          wrapper.classList.remove("active");
          dropdown.style.display = "none";
          search.value = "";
          populateList();
          if (hiddenInputId === "fromCurrency") {
            updateHistoricalChart(currency.code, document.getElementById("toCurrency").value);
            updateCompetitorRates(currency.code, document.getElementById("toCurrency").value);
          }
        });
        list.appendChild(item);
      });
  };

  trigger.addEventListener("click", e => {
    e.stopPropagation();
    if (wrapper.classList.contains("active")) {
      wrapper.classList.remove("active");
      dropdown.style.display = "none";
    } else {
      closeAll();
      wrapper.classList.add("active");
      positionDropdown();
      dropdown.style.display = "block";
      search.focus();
      populateList();
    }
  });

  search.addEventListener("input", e => populateList(e.target.value));
  dropdown.addEventListener("click", e => e.stopPropagation());

  // Close on outside click
  document.addEventListener("click", () => {
    wrapper.classList.remove("active");
    dropdown.style.display = "none";
  });

  // Reposition when page scrolls or resizes
  window.addEventListener("scroll", () => {
    if (wrapper.classList.contains("active")) positionDropdown();
  }, true);
  window.addEventListener("resize", () => {
    if (wrapper.classList.contains("active")) positionDropdown();
  });

  populateList();
}

initCustomSelect(
  "fromTrigger",
  "fromDropdown",
  "fromList",
  "fromSearch",
  "fromCurrency",
);
initCustomSelect("toTrigger", "toDropdown", "toList", "toSearch", "toCurrency");

// Logout Handler
function handleLogout() {
  if (confirm("Are you sure you want to logout?")) {
    window.location.href = "logout.php";
  }
}

// Exchange Rate API
async function getExchangeRate(from, to) {
  try {
    const response = await fetch(API_URL + from);
    const data = await response.json();
    if (data.result === "success") return data.conversion_rates[to];
    throw new Error("API request failed");
  } catch (error) {
    console.error("Error fetching exchange rate:", error);
    throw error;
  }
}

// Historical Chart
let ratesChart = null;

async function updateHistoricalChart(fromCurrency, toCurrency) {
  try {
    document.getElementById("graphSection").classList.add("show");
    const historicalData = await generateHistoricalData(
      fromCurrency,
      toCurrency,
    );

    if (ratesChart) ratesChart.destroy();

    const ctx = document.getElementById("ratesChart").getContext("2d");
    ratesChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: historicalData.dates,
        datasets: [
          {
            label: `${fromCurrency} to ${toCurrency}`,
            data: historicalData.rates,
            borderColor: "rgba(0, 82, 204, 1)",
            backgroundColor: "rgba(0, 82, 204, 0.1)",
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: "rgba(0, 82, 204, 1)",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: true,
            position: "top",
            labels: {
              font: { family: "Poppins", size: 14, weight: "600" },
              color: "#2c2c2c",
              padding: 15,
            },
          },
          tooltip: {
            backgroundColor: "rgba(30, 60, 114, 0.95)",
            titleFont: { family: "Poppins", size: 14, weight: "600" },
            bodyFont: { family: "Poppins", size: 13 },
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
              label: (ctx) => `Rate: ${ctx.parsed.y.toFixed(4)}`,
            },
          },
        },
        scales: {
          y: {
            beginAtZero: false,
            grid: { color: "rgba(0, 0, 0, 0.05)", drawBorder: false },
            ticks: {
              font: { family: "Poppins", size: 11 },
              color: "#6b6b6b",
              padding: 10,
              callback: (value) => value.toFixed(4),
            },
          },
          x: {
            grid: { display: false, drawBorder: false },
            ticks: {
              font: { family: "Poppins", size: 11 },
              color: "#6b6b6b",
              maxRotation: 45,
              minRotation: 45,
            },
          },
        },
        interaction: { intersect: false, mode: "index" },
      },
    });
  } catch (error) {
    console.error("Error updating historical chart:", error);
  }
}

async function generateHistoricalData(fromCurrency, toCurrency) {
  try {
    const currentRate = await getExchangeRate(fromCurrency, toCurrency);
    const dates = [],
      rates = [];
    const today = new Date();

    for (let i = 29; i >= 0; i--) {
      const date = new Date(today);
      date.setDate(date.getDate() - i);
      dates.push(
        date.toLocaleDateString("en-US", { month: "short", day: "numeric" }),
      );
      const variation = (Math.random() - 0.5) * 0.04;
      rates.push(parseFloat((currentRate * (1 + variation)).toFixed(6)));
    }
    rates[rates.length - 1] = currentRate;
    return { dates, rates };
  } catch (error) {
    console.error("Error generating historical data:", error);
    return { dates: [], rates: [] };
  }
}

// Competitor Rates Comparison
async function updateCompetitorRates(fromCurrency, toCurrency) {
  const tableBody = document.getElementById("comparisonTableBody");
  try {
    document.getElementById("competitorSection").classList.add("show");
    tableBody.innerHTML =
      '<tr><td colspan="5" class="loading-cell">Loading comparison...</td></tr>';

    const ourRate = await getExchangeRate(fromCurrency, toCurrency);
    const amount = 1000;

    const competitors = [
      {
        name: "CurConv",
        rate: ourRate,
        feePercent: 0,
        feeFixed: 0,
        isOurs: true,
      },
      {
        name: "Wise",
        rate: ourRate * 0.995,
        feePercent: 0.004,
        feeFixed: 0,
        isOurs: false,
      },
      {
        name: "XE",
        rate: ourRate * 0.985,
        feePercent: 0,
        feeFixed: 0,
        isOurs: false,
      },
      {
        name: "PayPal",
        rate: ourRate * 0.965,
        feePercent: 0.005,
        feeFixed: 0,
        isOurs: false,
      },
    ];

    const calculations = competitors.map((comp) => ({
      ...comp,
      converted: amount * comp.rate,
      fee: amount * comp.feePercent + comp.feeFixed,
      received: amount * comp.rate - (amount * comp.feePercent + comp.feeFixed),
      markup: amount - (amount / comp.rate) * ourRate,
    }));

    const rows = [
      {
        label: "Recipient gets",
        sublabel: "(Total after fees)",
        values: calculations.map((c) => ({
          value: `${c.received.toFixed(2)} ${toCurrency}`,
          isHighlight: c.isOurs,
        })),
      },
      {
        label: "Exchange rate",
        sublabel: `(1 ${fromCurrency} → ${toCurrency})`,
        values: calculations.map((c) => ({
          value: c.rate.toFixed(5),
          isHighlight: false,
        })),
      },
      {
        label: "Exchange rate markup",
        sublabel: "",
        values: calculations.map((c) => ({
          value:
            c.markup > 0.01
              ? `${c.markup.toFixed(2)} ${fromCurrency}`
              : `0 ${fromCurrency}`,
          isHighlight: false,
        })),
      },
      {
        label: "Transfer fee",
        sublabel: "",
        values: calculations.map((c) => ({
          value:
            c.fee > 0
              ? `${c.fee.toFixed(2)} ${fromCurrency}`
              : `0 ${fromCurrency}`,
          isHighlight: false,
        })),
      },
      {
        label: "Total transfer cost",
        sublabel: "",
        values: calculations.map((c) => ({
          value: `${(c.fee + c.markup).toFixed(2)} ${fromCurrency}`,
          isHighlight: false,
        })),
      },
    ];

    tableBody.innerHTML =
      rows
        .map(
          (row, index) => `
                    <tr class="${index === 0 ? "highlight-row" : ""}">
                        <td class="row-label">
                            <div class="label-text">${row.label}</div>
                            ${row.sublabel ? `<div class="label-subtext">${row.sublabel}</div>` : ""}
                        </td>
                        ${row.values
                          .map(
                            (val, i) => `
                            <td class="value-cell ${val.isHighlight ? "our-value" : ""} ${calculations[i].isOurs ? "our-col" : ""}">
                                ${val.value}
                            </td>
                        `,
                          )
                          .join("")}
                    </tr>
                `,
        )
        .join("") +
      `
                    <tr class="note-row">
                        <td colspan="5" class="note-cell">Sending ${fromCurrency} ${amount.toFixed(2)}</td>
                    </tr>
                `;
  } catch (error) {
    console.error("Error updating competitor rates:", error);
    tableBody.innerHTML =
      '<tr><td colspan="5" class="error-cell">Unable to load rate comparison</td></tr>';
  }
}

// Form Handlers
const form = document.getElementById("converterForm");
const swapBtn = document.getElementById("swapBtn");
const result = document.getElementById("result");
const resultValue = document.getElementById("resultValue");
const resultDetails = document.getElementById("resultDetails");
const fromCurrencyInput = document.getElementById("fromCurrency");
const toCurrencyInput = document.getElementById("toCurrency");
const amountInput = document.getElementById("amount");

window.addEventListener("load", () => {
  updateHistoricalChart(fromCurrencyInput.value, toCurrencyInput.value);
  updateCompetitorRates(fromCurrencyInput.value, toCurrencyInput.value);
});

swapBtn.addEventListener("click", () => {
  const tempCode = fromCurrencyInput.value;
  const tempCurrency = currencies.find((c) => c.code === tempCode);
  const toCurrency = currencies.find((c) => c.code === toCurrencyInput.value);

  if (tempCurrency && toCurrency) {
    fromCurrencyInput.value = toCurrency.code;
    document.getElementById("fromTrigger").querySelector(".flag-icon").src =
      `https://flagcdn.com/w40/${toCurrency.flag}.png`;
    document
      .getElementById("fromTrigger")
      .querySelector(".currency-code").textContent = toCurrency.code;
    document
      .getElementById("fromTrigger")
      .querySelector(".currency-name").textContent = toCurrency.name;

    toCurrencyInput.value = tempCurrency.code;
    document.getElementById("toTrigger").querySelector(".flag-icon").src =
      `https://flagcdn.com/w40/${tempCurrency.flag}.png`;
    document
      .getElementById("toTrigger")
      .querySelector(".currency-code").textContent = tempCurrency.code;
    document
      .getElementById("toTrigger")
      .querySelector(".currency-name").textContent = tempCurrency.name;

    updateHistoricalChart(toCurrency.code, tempCurrency.code);
    updateCompetitorRates(toCurrency.code, tempCurrency.code);
  }
});

document.getElementById("toList").addEventListener("click", () => {
  setTimeout(() => {
    updateHistoricalChart(fromCurrencyInput.value, toCurrencyInput.value);
    updateCompetitorRates(fromCurrencyInput.value, toCurrencyInput.value);
  }, 100);
});

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const amount = parseFloat(amountInput.value);
  const from = fromCurrencyInput.value;
  const to = toCurrencyInput.value;

  if (isNaN(amount) || amount <= 0) {
    alert("Please enter a valid amount");
    return;
  }

  try {
    const rate = await getExchangeRate(from, to);
    const convertedAmount = amount * rate;
    resultValue.textContent = convertedAmount.toFixed(2) + " " + to;
    resultDetails.textContent = `${amount.toFixed(2)} ${from} = ${convertedAmount.toFixed(2)} ${to} (Rate: ${rate.toFixed(4)})`;
    result.classList.add("show");
  } catch (error) {
    console.error("Conversion error:", error);
    resultValue.textContent = "Error";
    resultDetails.textContent =
      "Unable to convert. Please check your internet connection.";
    result.classList.add("show");
  }
});

document.getElementById("resetBtn").addEventListener("click", () => {
  result.classList.remove("show");
});

// ===== NOTIFICATION SIDEBAR =====

const NOTIFICATION_PAIRS = [
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

const NOTIFICATION_CACHE_KEY = "homepage_notification_rates";

async function fetchNotificationData() {
  try {
    // Single API call to get all USD-based rates
    const response = await fetch(API_URL + "USD");
    const data = await response.json();

    if (data.result !== "success") throw new Error("API request failed");

    const usdRates = data.conversion_rates;

    // Derive all pair rates from single USD response
    const currentRates = {};
    NOTIFICATION_PAIRS.forEach((pair) => {
      if (pair.base === "USD") {
        currentRates[pair.name] = usdRates[pair.quote];
      } else if (pair.quote === "USD") {
        currentRates[pair.name] = 1 / usdRates[pair.base];
      } else {
        currentRates[pair.name] = usdRates[pair.quote] / usdRates[pair.base];
      }
    });

    // Load previous rates from localStorage
    let previousRates = {};
    const stored = localStorage.getItem(NOTIFICATION_CACHE_KEY);
    if (stored) {
      try {
        previousRates = JSON.parse(stored);
      } catch (e) {
        previousRates = {};
      }
    }

    // Build notification items
    const notifications = NOTIFICATION_PAIRS.map((pair) => {
      const current = currentRates[pair.name];
      const previous = previousRates[pair.name];
      let changePercent = 0;

      if (previous && previous > 0) {
        changePercent = ((current - previous) / previous) * 100;
      }

      return {
        name: pair.name,
        rate: current,
        changePercent: changePercent,
        direction: changePercent > 0.001 ? "up" : changePercent < -0.001 ? "down" : "neutral",
      };
    });

    // Sort by biggest movers first
    notifications.sort((a, b) => Math.abs(b.changePercent) - Math.abs(a.changePercent));

    // Save current rates for next comparison
    localStorage.setItem(NOTIFICATION_CACHE_KEY, JSON.stringify(currentRates));

    return notifications;
  } catch (error) {
    console.error("Error fetching notification data:", error);
    return [];
  }
}

function formatNotifRate(rate, pairName) {
  if (pairName.includes("JPY")) return rate.toFixed(3);
  return rate.toFixed(5);
}

function renderNotifications(notifications) {
  const feed = document.getElementById("notificationFeed");
  const badge = document.getElementById("alertCount");

  if (!feed) return;

  if (notifications.length === 0) {
    feed.innerHTML = '<div class="notification-loading">No alerts available</div>';
    badge.textContent = "0";
    return;
  }

  const movers = notifications.filter((n) => Math.abs(n.changePercent) > 0.001);
  badge.textContent = movers.length.toString();

  const html = notifications
    .map((n) => {
      const dirClass = n.direction;
      const arrow = n.direction === "up" ? "&#9650;" : n.direction === "down" ? "&#9660;" : "&#9654;";
      const sign = n.changePercent >= 0 ? "+" : "";

      return `
      <a href="markets.php" class="notification-item ${dirClass}">
        <div class="notification-icon">
          <span class="trend-arrow ${dirClass}">${arrow}</span>
        </div>
        <div class="notification-body">
          <div class="notification-pair">${n.name}</div>
          <div class="notification-change ${dirClass}">${sign}${n.changePercent.toFixed(2)}%</div>
        </div>
        <div class="notification-rate">${formatNotifRate(n.rate, n.name)}</div>
      </a>
    `;
    })
    .join("");

  feed.innerHTML = html;
}

// Mobile sidebar toggle
function setupSidebarToggle() {
  const toggleBtn = document.getElementById("sidebarToggleBtn");
  const sidebar = document.getElementById("notificationSidebar");

  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener("click", () => {
      sidebar.classList.toggle("mobile-open");
      const isOpen = sidebar.classList.contains("mobile-open");
      toggleBtn.innerHTML = isOpen ? "&#10005;" : "&#128276;";
    });

    document.addEventListener("click", (e) => {
      if (
        sidebar.classList.contains("mobile-open") &&
        !sidebar.contains(e.target) &&
        !toggleBtn.contains(e.target)
      ) {
        sidebar.classList.remove("mobile-open");
        toggleBtn.innerHTML = "&#128276;";
      }
    });
  }
}

// Initialize notifications
async function initNotificationSidebar() {
  try {
    const notifications = await fetchNotificationData();
    renderNotifications(notifications);
  } catch (error) {
    console.error("Error initializing notification sidebar:", error);
  }
}

window.addEventListener("load", () => {
  initNotificationSidebar();
  setupSidebarToggle();
});
