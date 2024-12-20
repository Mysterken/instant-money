import './App.css';
import React, {useEffect, useState} from "react";
import {fetchCurrencies, fetchHistoricalData} from "./services/currencyService";
import {assignColorsToCurrencies} from "./utils/colorUtils";
import CurrencySelector from "./components/CurrencySelector";
import TimeFilterSelector from "./components/TimeFilterSelector";
import ExchangeRateChart from "./components/ExchangeRateChart";

const App = () => {
  const [data, setData] = useState([]);
  const [currenciesOptions, setCurrenciesOptions] = useState([]);
  const [selectedCurrencies, setSelectedCurrencies] = useState([]);
  const [timeFilter, setTimeFilter] = useState("month");
  const [currencyColors, setCurrencyColors] = useState({});

  useEffect(() => {
    // Récupération des devises
    const loadCurrencies = async () => {
      const options = await fetchCurrencies();
      setCurrenciesOptions(options);
    };
    loadCurrencies();
  }, []);

  useEffect(() => {
    // Récupération des données selon le filtre temporel
    const loadHistoricalData = async () => {
      const formattedData = await fetchHistoricalData(timeFilter);
      setData(formattedData);
    };
    loadHistoricalData();
  }, [timeFilter]);

  useEffect(() => {
    // Mise à jour des couleurs des devises sélectionnées
    const newCurrencyColors = assignColorsToCurrencies(selectedCurrencies);
    setCurrencyColors(newCurrencyColors);
  }, [selectedCurrencies]);

  return (
    <div className="graph-container">
      <h1>Évolution des cours de change</h1>
      <CurrencySelector
        options={currenciesOptions}
        onSelect={setSelectedCurrencies}
      />
      <TimeFilterSelector
        selectedFilter={timeFilter}
        onChange={setTimeFilter}
      />
      <ExchangeRateChart
        data={data}
        selectedCurrencies={selectedCurrencies}
        currencyColors={currencyColors}
      />
    </div>
  );
};

export default App;
