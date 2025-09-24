const datasetObject = (data, label) => {
  return {
    label: label,
    fill: false,
    borderDash: [],
    borderDashOffset: 0.0,
    pointRadius: 4,
    data: data.map(item => ({ label: item.name, value: item.candidates_count})),
    tension: 0.5,
    cubicInterpolationMode: "default",
  };
};



export const ministryChartData = (data) => {
  const labels = data.map(item => item.name);
  return {
    labels,
    datasets: [
      datasetObject(data)
    ]
  }
}


