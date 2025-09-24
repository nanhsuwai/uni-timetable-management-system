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
    data.push(Math.round(Math.random() * 5));
  }

  return data;
};

const datasetObject = (color, data, label) => {
  return {
    label: label,
    fill: false,
    borderDash: [],
    borderDashOffset: 0.0,
    pointBackgroundColor: chartColors.default[color],
    pointHoverBackgroundColor: chartColors.default[color],
    // pointBorderWidth: 20,
    pointHoverRadius: 4,
    pointHoverBorderWidth: 15,
    pointRadius: 4,
    data: data,
    tension: 0.5,
    cubicInterpolationMode: "default",
  };
};

export const sampleChartData = (data) => {
  const labels = data[0]
  return {
    labels,
    datasets: [
      datasetObject("primary", data[3], 'အကြိမ်အရေအတွက်'),
    ],
  };
};
