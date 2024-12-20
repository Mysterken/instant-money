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

  // Fonction pour formater une date au format YYYY-MM-DD
  const getFormattedDate = (date) => {
    const d = new Date(date);
    const month = d.getMonth() + 1;
    const day = d.getDate();
    const year = d.getFullYear();
    return `${year}-${month < 10 ? `0${month}` : month}-${day < 10 ? `0${day}` : day}`;
  };

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
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(today.getDate() - 1);
    
        let startDate = new Date(yesterday);
    
        // Calculer la date de début en fonction du filtre temporel
        if (timeFilter === "year") {
          startDate.setFullYear(yesterday.getFullYear() - 1);
        } else if (timeFilter === "month") {
          startDate.setMonth(yesterday.getMonth() - 1);
        } else if (timeFilter === "week") {
          startDate.setDate(yesterday.getDate() - 7);
        }
    
        const formattedYesterday = getFormattedDate(yesterday);
        const formattedStartDate = getFormattedDate(startDate);
    
        // Générer les dates intermédiaires
        const intermediateDates = generateIntermediateDates(startDate, yesterday, timeFilter);
    
        // Logs pour vérifier les dates
        console.log("Fetching data for dates:", intermediateDates);
    
        const endpoint = "/api/currency_historical";
    
        // Récupérer les données pour toutes les dates générées
        const responses = await Promise.all(
          intermediateDates.map((date) => axios.get(`${endpoint}?date=${getFormattedDate(date)}`))
        );
    
        // Extraire les données et les formater
        const formattedData = responses.map((response, index) => {
          const date = getFormattedDate(intermediateDates[index]);
          const rates = response.data.data[date];
          return { date, ...rates }; // Inclure la date et les taux pour chaque réponse
        });
    
        setData(formattedData); // Mettre à jour les données pour le graphique
      } catch (error) {
        console.error("Erreur lors de la récupération des données :", error);
      }
    };
    
    // Fonction pour générer les dates intermédiaires
    const generateIntermediateDates = (startDate, endDate, filter) => {
      const dates = [];
      let currentDate = new Date(startDate);
    
      while (currentDate <= endDate) {
        dates.push(new Date(currentDate)); // Ajouter une copie de la date actuelle
    
        if (filter === "year") {
          currentDate.setMonth(currentDate.getMonth() + 1); // Ajouter un mois
        } else if (filter === "month") {
          currentDate.setDate(currentDate.getDate() + 7); // Ajouter une semaine
        } else if (filter === "week") {
          currentDate.setDate(currentDate.getDate() + 1); // Ajouter un jour
        }
      }
    
      return dates;
    };
    
    // Fonction pour formater une date au format requis (AAAA-MM-JJ)
    const getFormattedDate = (date) => {
      return date.toISOString().split("T")[0];
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
