export const generateIntermediateDates = (startDate, endDate, filter) => {
    const dates = [];
    let currentDate = new Date(startDate);

    while (currentDate <= endDate) {
        dates.push(new Date(currentDate));
        if (filter === "year") currentDate.setMonth(currentDate.getMonth() + 1);
        else if (filter === "month") currentDate.setDate(currentDate.getDate() + 7);
        else if (filter === "week") currentDate.setDate(currentDate.getDate() + 1);
    }
    return dates;
};