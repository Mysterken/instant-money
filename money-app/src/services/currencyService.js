import axios from "axios";
import { generateIntermediateDates } from "../utils/dateUtils";

export const fetchCurrencies = async() => {
    try {
        const response = await axios.get("/api/currency_list");
        const currencies = response.data.data;
        return Object.entries(currencies).map(([key, currency]) => ({
            value: currency.code,
            label: currency.name,
        }));
    } catch (error) {
        console.error("Erreur lors de la récupération des devises :", error);
        return [];
    }
};

export const fetchHistoricalData = async(timeFilter) => {
    try {
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(today.getDate() - 1);

        let startDate = new Date(yesterday);
        if (timeFilter === "year") startDate.setFullYear(yesterday.getFullYear() - 1);
        else if (timeFilter === "month") startDate.setMonth(yesterday.getMonth() - 1);
        else if (timeFilter === "week") startDate.setDate(yesterday.getDate() - 7);

        const intermediateDates = generateIntermediateDates(startDate, yesterday, timeFilter);
        const endpoint = "/api/currency_historical";

        const responses = await Promise.all(
            intermediateDates.map((date) =>
                axios.get(`${endpoint}?date=${date.toISOString().split("T")[0]}`)
            )
        );

        return responses.map((response, index) => {
            const date = intermediateDates[index].toISOString().split("T")[0];
            const rates = response.data.data[date];
            return { date, ...rates };
        });
    } catch (error) {
        console.error("Erreur lors de la récupération des données :", error);
        return [];
    }
};