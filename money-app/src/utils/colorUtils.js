const colors = [
    "#8884d8", "#82ca9d", "#ff7300", "#ff0000", "#0088fe", "#00c49f", "#ffbb28", "#ff8042",
];

export const assignColorsToCurrencies = (currencies) => {
    const currencyColors = {};
    currencies.forEach((currency, index) => {
        currencyColors[currency] = colors[index % colors.length];
    });
    return currencyColors;
};