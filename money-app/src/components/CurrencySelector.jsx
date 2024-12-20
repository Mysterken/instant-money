import React from "react";
import Select from "react-select";

const CurrencySelector = ({ options, onSelect }) => {
  return (
    <div style={{ marginBottom: "20px" }}>
      <label>Sélectionnez les devises à afficher :</label>
      <Select
        options={options}
        isMulti
        onChange={(selectedOptions) =>
          onSelect(selectedOptions.map((opt) => opt.value))
        }
      />
    </div>
  );
};

export default CurrencySelector;
