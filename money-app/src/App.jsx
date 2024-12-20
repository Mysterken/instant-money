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
    const loadCurrenciesAndData = async () => {
      const options = await fetchCurrencies();
      setCurrenciesOptions(options);
      const formattedData = await fetchHistoricalData(timeFilter);
      setData(formattedData);
    };
    loadCurrenciesAndData();
  }, [timeFilter]);

  useEffect(() => {
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
