// position in the ranking as a two-digit label: 0 -> "01"
const rankLabel = index => String(index + 1).padStart(2, "0");

export { rankLabel };
