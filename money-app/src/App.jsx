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

// Options pour le dropdown des devises
const currenciesOptions = [
  { value: "USD", label: "USD (Dollar)" },
  { value: "EUR", label: "EUR (Euro)" },
  { value: "GBP", label: "GBP (Livre Sterling)" },
  { value: "JPY", label: "JPY (Yen)"},
];

// Options pour le filtre temporel
const timeOptions = [
  { value: "year", label: "Par année" },
  { value: "month", label: "Par mois" },
  { value: "week", label: "Par semaine" },
];

const App = () => {
  const [data, setData] = useState([]); // Les données brutes récupérées via Axios
  const [filteredData, setFilteredData] = useState([]); // Les données filtrées selon les options
  const [selectedCurrencies, setSelectedCurrencies] = useState([]);
  const [timeFilter, setTimeFilter] = useState("month");

  // Fonction pour récupérer les données avec Axios
  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await axios.get("/rates.json"); // URL du fichier JSON
        setData(response.data); // Stocker les données brutes
        setFilteredData(response.data); // Initialiser les données filtrées
      } catch (error) {
        console.error("Erreur lors de la récupération des données :", error);
      }
    };

    fetchData();
  }, []);

  // Fonction pour filtrer les données selon le filtre temporel
  useEffect(() => {
    const filterDataByTime = () => {
      if (timeFilter === "year") {
        // Exemple simplifié : filtre par année (prend une donnée sur 12 pour simuler un regroupement annuel)
        return data.filter((_, index) => index % 12 === 0);
      } else if (timeFilter === "week") {
        // Exemple simplifié : filtre par semaine (prend une donnée sur 4)
        return data.filter((_, index) => index % 4 === 0);
      }
      // Par défaut : données par mois
      return data;
    };

    setFilteredData(filterDataByTime());
  }, [timeFilter, data]);

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
          defaultValue={timeOptions[1]} // Par défaut : Par mois
        />
      </div>

      {/* Graphique */}
      <ResponsiveContainer width="100%" height={400}>
        <LineChart
          data={filteredData}
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
                currency === "USD"
                  ? "#8884d8"
                  : currency === "EUR"
                  ? "#82ca9d"
                  : "#ffc658"
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