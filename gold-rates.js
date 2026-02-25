// API Configuration
const API_KEY = "1e7bed46857db1854e506c15";
const API_URL = `https://v6.exchangerate-api.com/v6/${API_KEY}/latest/`;

// Gold API proxied through server to avoid CORS and hide API key
const GOLD_API_URL = "gold-proxy.php";

// Currency Data (needed for flag display)
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

// Logout Handler
function handleLogout() {
  if (confirm("Are you sure you want to logout?")) {
    window.location.href = "logout.php";
  }
}

// Exchange Rate API — fetch all rates in one call
async function getAllExchangeRates(base) {
  const response = await fetch(API_URL + base);
  const data = await response.json();
  if (data.result === "success") return data.conversion_rates;
  throw new Error("Exchange rate API failed: " + (data["error-type"] || "unknown"));
}

// Gold Price Functions
let goldPricesCache = null;
let goldLastUpdate = null;

async function fetchGoldPrices() {
  try {
    const response = await fetch(GOLD_API_URL);

    if (!response.ok) throw new Error("Gold API request failed");

    const data = await response.json();
    goldPricesCache = data;
    goldLastUpdate = new Date();
    return data;
  } catch (error) {
    console.error("Error fetching gold prices:", error);
    throw error;
  }
}

async function updateGoldTable() {
  const tableBody = document.getElementById("goldTableBody");
  const updateTimeEl = document.getElementById("goldUpdateTime");
  const goldAmount =
    parseFloat(document.getElementById("goldAmount").value) || 1;
  const goldUnit = document.getElementById("goldUnit").value;

  try {
    tableBody.innerHTML =
      '<tr><td colspan="3" class="loading-cell">Loading gold prices...</td></tr>';

    const goldData = await fetchGoldPrices();

    // Convert weight to troy ounces
    let weightInOz = goldAmount;
    if (goldUnit === "gram") {
      weightInOz = goldAmount / 31.1035;
    } else if (goldUnit === "kg") {
      weightInOz = goldAmount * 32.1507;
    }

    // Get exchange rates for multiple currencies
    const targetCurrencies = [
      "USD",
      "EUR",
      "GBP",
      "JPY",
      "AUD",
      "CAD",
      "CHF",
      "CNY",
    ];
    const pricePerOzUSD = goldData.price_gram_24k * 31.1035;

    // Fetch all rates in a single API call
    const allRates = await getAllExchangeRates("USD");

    const rows = targetCurrencies.map((currency) => {
      const rate = currency === "USD" ? 1 : allRates[currency];
      if (!rate) {
        return `<tr><td>${currency}</td><td colspan="2" class="error-cell">Rate unavailable</td></tr>`;
      }
      const priceInCurrency = pricePerOzUSD * rate;
      const yourAmount = priceInCurrency * weightInOz;
      const currencyInfo = currencies.find((c) => c.code === currency);
      const flagImg = currencyInfo
        ? `<img src="https://flagcdn.com/w40/${currencyInfo.flag}.png" class="flag-icon-small" alt="${currency}">`
        : "";
      return `
        <tr>
          <td class="currency-cell">${flagImg} <strong>${currency}</strong></td>
          <td class="price-cell">${priceInCurrency.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
          <td class="amount-cell"><strong>${yourAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></td>
        </tr>`;
    });

    tableBody.innerHTML = rows.join("");
    updateTimeEl.textContent = `Last updated: ${goldLastUpdate.toLocaleTimeString()}`;
  } catch (error) {
    console.error("Error updating gold table:", error);
    tableBody.innerHTML =
      '<tr><td colspan="3" class="error-cell">Unable to load gold prices. Please try again.</td></tr>';
    updateTimeEl.textContent = "Update failed";
  }
}

// Gold event listeners
document
  .getElementById("refreshGoldBtn")
  .addEventListener("click", updateGoldTable);
document
  .getElementById("goldAmount")
  .addEventListener("input", updateGoldTable);
document.getElementById("goldUnit").addEventListener("change", updateGoldTable);

// Load gold prices on page load
window.addEventListener("load", () => {
  updateGoldTable();
});
