export const chartColors = {
  default: {
    primary: "#0a56bb",
    secondary: "#0a46bb",
    third: "#0a56cc",
    danger: "#FF3860",
  },
};

const randomChartData = (n) => {
  const data = [];

  for (let i = 0; i < n; i++) {
    data.push(Math.round(Math.random() * 200));
  }

  return data;
};

const datasetObject = (color,data, label) => {
  return {
    label: label,
    fill: false,

    borderDash: [],
    borderDashOffset: 0.0,
    // pointBackgroundColor: chartColors.default[color],
    // pointHoverBackgroundColor: chartColors.default[color],
    // pointBorderWidth: 20,
    pointHoverRadius: 4,
    pointHoverBorderWidth: 10,
    pointRadius: 4,
    data: data,
    tension: 0.1,
    cubicInterpolationMode: "default",
  };
};

export const malefemaleChartData = (data) => {
  const labels = data[0];
  return {
    labels,
    datasets: [
      datasetObject("primary", data[1], 'Male'),
      datasetObject("danger", data[2], 'Female'),

    ],
  };
};
export const officerChartData = (data) => {
  const labels = data[0];
  return {
    labels,
    datasets: [
      datasetObject("primary", data[3], 'အမူထမ်း'),
      datasetObject("danger", data[4], 'အရာထမ်း'),
    ],
  };
};
