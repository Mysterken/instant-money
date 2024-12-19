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
  const [data, setData] = useState([]); // Données brutes
  const [currenciesOptions, setCurrenciesOptions] = useState([]); // Options des devises
  const [selectedCurrencies, setSelectedCurrencies] = useState([]); // Devises sélectionnées
  const [timeFilter, setTimeFilter] = useState("month"); // Filtre temporel

  // Récupération des devises au chargement
  useEffect(() => {
    const fetchCurrencies = async () => {
      try {
        const response = await axios.get("/api/currency_list");
        const currencies = response.data.data;

        if (currencies) {
          const options = Object.entries(currencies).map(([key, currency]) => ({
            value: currency.code,
            label: currency.name,
          }));
          setCurrenciesOptions(options);
        } else {
          console.error("Données invalides pour les devises :", currencies);
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des devises :", error);
      }
    };

    fetchCurrencies();
  }, []);

  // Récupération des données selon le filtre temporel
  useEffect(() => {
    const fetchData = async () => {
      try {
        const endpoint ="/api/currency_historical"

        const response = await axios.get(endpoint+"?date=2024-12-18");
        const response2 = await axios.get(endpoint+"?date=2023-12-18");
        const ratesData = response.data.data["2024-12-18"];
        const ratesData2 = response2.data.data["2023-12-18"];

        if (ratesData) {
          if (timeFilter === "year") {
            // Formatage des données pour les taux historiques
            const formattedData = Object.entries(ratesData).map(
              ([date, rates]) => ({
                date,
                ...rates, // Inclure toutes les devises avec leurs valeurs
              })
            );
            console.log(formattedData)
            setData(formattedData);
          } else {
            // Formatage pour les taux récents
            const today = new Date().toISOString().split("T")[0];
            console.log(ratesData)
            console.log(ratesData2)
            setData([
              {
                date: "2023-12-19",
                ...ratesData,
              },
              {
                date: "2024-12-18",
                ...ratesData2,
              },
            ]);
          }
        } else {
          console.error("Données invalides pour les cours :", ratesData);
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des données :", error);
      }
    };

    fetchData();
  }, [timeFilter]);

  // Filtrage des données pour le graphique
  const filteredGraphData = data.map((entry) => {
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

      {/* Menu déroulant pour sélectionner les devises */}
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

      {/* Menu déroulant pour le filtre temporel */}
      <div style={{ marginBottom: "20px" }}>
        <label>Filtrer l'évolution par :</label>
        <Select
          options={timeOptions}
          onChange={(selectedOption) => setTimeFilter(selectedOption.value)}
          defaultValue={timeOptions[1]} // Par défaut, "Par mois"
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
              stroke="#8884d8" // Couleur par défaut
              activeDot={{ r: 8 }}
            />
          ))}
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
};

export default App;