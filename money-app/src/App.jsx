/*import { useEffect, useState } from "react";
import axios from "axios";

function App() {
  const [rates, setRates] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Appel API vers le backend Symfony
    axios.get("http://localhost:8000/api/rates")
      .then((response) => {
        setRates(response.data.rates);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Erreur API :", err);
        setError("Erreur lors du chargement des taux de change.");
        setLoading(false);
      });
  }, []);

  return (
    <div>
      <h1>Taux de Change</h1>
      {loading ? (
        <p>Chargement...</p>
      ) : error ? (
        <p>{error}</p>
      ) : (
        <ul>
          {Object.entries(rates).map(([currency, value]) => (
            <li key={currency}>
              {currency} : {value}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
export default App;*/

//API -> JSON

import React, { useState, useEffect } from "react";
import axios from "axios";
import Select from "react-select";
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from "recharts";

// Options pour le filtre temporel
const timeOptions = [
  { value: "year", label: "Par année" },
  { value: "month", label: "Par mois" },
  { value: "week", label: "Par semaine" },
];

const App = () => {
  const [data, setData] = useState([]); // Les données brutes récupérées via Axios
  const [filteredData, setFilteredData] = useState([]); // Les données filtrées selon les options
  const [currenciesOptions, setCurrenciesOptions] = useState([]); // Options dynamiques pour les devises
  const [selectedCurrencies, setSelectedCurrencies] = useState([]);
  const [timeFilter, setTimeFilter] = useState("month");

  // Fonction pour récupérer la liste des devises (dropdown dynamique)
  useEffect(() => {
    const fetchCurrencies = async () => {
      try {
        const response = await axios.get("/api/currency_list");
        const currencies = response.data.data;
        console.log(currencies)
        if (currencies) {
          
          const currenciesOption = []

          for (const [key, currency] of Object.entries(currencies)) {
            currenciesOption.push({
              value: currency.code,
              label: currency.name
            })
          }
          
          setCurrenciesOptions(
            currenciesOption
          )
        } else {
          console.error("Données invalides pour les devises :", currencies);
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des devises :", error);
      }
    };
  
    fetchCurrencies();
  }, []);
  
  useEffect(() => {
    const fetchData = async () => {
      try {
        const endpoint =
          timeFilter === "year"
            ? "/api/currency_historical"
            : "/api/currency_latest";
        const response = await axios.get(endpoint);
        const ratesData = response.data.data; // Assurez-vous que les données sont sous "data"
        if (ratesData && Array.isArray(ratesData)) {
          setData(ratesData);
          setFilteredData(ratesData);
        } else {
          console.error("Données invalides pour les cours :", ratesData);
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des données :", error);
      }
    };

    fetchData();
  }, [timeFilter]);

  // Filtrage des données selon les devises sélectionnées
  const filteredGraphData = filteredData.map((entry) => {
    const filteredEntry = { date: entry.date }; // Inclure la date dans chaque entrée
    selectedCurrencies.forEach((currency) => {
      if (entry[currency]) {
        filteredEntry[currency] = entry[currency];
      }
    });
    return filteredEntry;
  });

  return (
    <div style={{ padding: "20px" }}>
      <h1>Évolution des Cours de Change</h1>

      {/* Dropdown pour les devises */}
      <div style={{ marginBottom: "20px" }}>
        <label>Sélectionnez les devises à afficher :</label>
        <Select
          options={currenciesOptions}
          isMulti
          onChange={(selectedOptions) =>
            setSelectedCurrencies(selectedOptions.map((opt) => opt.value))
          }
        />
      </div>

      {/* Dropdown pour le filtre temporel */}
      <div style={{ marginBottom: "20px" }}>
        <label>Filtrer l'évolution par :</label>
        <Select
          options={timeOptions}
          onChange={(selectedOption) => setTimeFilter(selectedOption.value)}
          defaultValue={timeOptions[1]}
        />
      </div>

      {/* Graphique */}
      <ResponsiveContainer width="100%" height={400}>
        <LineChart
          data={filteredGraphData}
          margin={{ top: 10, right: 30, left: 0, bottom: 0 }}
        >
          <CartesianGrid strokeDasharray="3 3" />
          <XAxis dataKey="date" />
          <YAxis />
          <Tooltip />
          <Legend />
          {/* Tracer les lignes pour chaque devise sélectionnée */}
          {selectedCurrencies.map((currency) => (
            <Line
              key={currency}
              type="monotone"
              dataKey={currency}
              stroke={
                currency.code === "USD"
                  ? "#8884d8"
                  : currency.code === "EUR"
                  ? "#82ca9d"
                  : currency.code === "GBP"
                  ? "#ffc658"
                  : "#d84a44" // Couleur par défaut pour les autres devises
              }
              activeDot={{ r: 8 }}
            />
          ))}
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
};

export default App;
