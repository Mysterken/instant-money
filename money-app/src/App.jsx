import { useEffect, useState } from "react";
import axios from "axios";
import {
  LineChart,
  Line,
  CartesianGrid,
  XAxis,
  YAxis,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from "recharts";

function App() {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    axios.get("https://localhost/api/currency_list")
      .then((response) => {
        setData(response.data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Erreur lors du chargement du fichier JSON :", err);
        setError("Erreur lors du chargement des taux.");
        setLoading(false);
      });
  }, []);
  useEffect(() => {

    axios.get("/rates.json")
      .then((response) => {
        setData(response.data);
        setLoading(false);
      })
      .catch((err) => {
        console.error("Erreur lors du chargement du fichier JSON :", err);
        setError("Erreur lors du chargement des taux.");
        setLoading(false);
      });
  }, []);

  return (
    <div className="container mt-5">
      <h1 className="text-center mb-4">Taux de Change par Rapport au Temps</h1>
      {loading ? (
        <p className="text-center">Chargement...</p>
      ) : error ? (
        <p className="text-danger text-center">{error}</p>
      ) : (
        <ResponsiveContainer width="100%" height={400}>
          <LineChart data={data}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="date" />
            <YAxis />
            <Tooltip />
            <Legend />

            <Line type="monotone" dataKey="USD" stroke="#8884d8" name="USD (Dollar)" />
            <Line type="monotone" dataKey="GBP" stroke="#82ca9d" name="GBP (Livre)" />
            <Line type="monotone" dataKey="JPY" stroke="#ffc658" name="JPY (Yen)" />
          </LineChart>
        </ResponsiveContainer>
      )}
    </div>
  );
}

export default App;