import React from "react";
import Select from "react-select";

const timeOptions = [
  { value: "year", label: "Par année" },
  { value: "month", label: "Par mois" },
  { value: "week", label: "Par semaine" },
];

const TimeFilterSelector = ({ selectedFilter, onChange }) => {
  return (
    <div style={{ marginBottom: "20px" }}>
      <label>Filtrer l'évolution par :</label>
      <Select
        options={timeOptions}
        onChange={(selectedOption) => onChange(selectedOption.value)}
        defaultValue={timeOptions.find((opt) => opt.value === selectedFilter)}
      />
    </div>
  );
};

export default TimeFilterSelector;
