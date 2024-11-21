<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    // Single Line Chart
var lineChartData = {
labels: ['Label1', 'Label2', 'Label3', 'Label4', 'Label5'], // Replace with your labels
datasets: [
    {
        label: 'Data Set 1',
        data: [10, 20, 15, 25, 30], // Replace with your data
        borderColor: 'rgba(75, 192, 192, 1)', // Replace with your desired color
        borderWidth: 2,
        fill: false
    }
    // Add more datasets if needed
]
};

var lineChartOptions = {
scales: {
    x: {
        type: 'linear', // Change the type if your x-axis is not numeric
        position: 'bottom'
    },
    y: {
        type: 'linear', // Change the type if your y-axis is not numeric
        position: 'left'
    }
}
// Add more options as needed
};

var lineChart = new Chart(document.getElementById('line-chart'), {
type: 'line',
data: lineChartData,
options: lineChartOptions
});

// Repeat similar code for other charts

    // Single Line Chart Data
var lineChartData = {
labels: ['January', 'February', 'March', 'April', 'May'], // Labels for X-axis
datasets: [{
    label: 'Sales',
    data: [120, 150, 170, 200, 180], // Data points for Y-axis
    borderColor: 'rgb(75, 192, 192)', // Border color for the line
    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Fill color under the line
    tension: 0.4 // Line tension (curvature)
}]
};

// Single Line Chart Options
var lineChartOptions = {
responsive: true,
plugins: {
    title: {
        display: true,
        text: 'Sales Performance'
    },
    legend: {
        display: true,
        position: 'top'
    }
},
scales: {
    x: {
        display: true,
        title: {
            display: true,
            text: 'Months'
        }
    },
    y: {
        display: true,
        title: {
            display: true,
            text: 'Sales Amount'
        }
    }
}
};

// Creating the Single Line Chart
var lineChart = new Chart(document.getElementById('line-chart'), {
type: 'line',
data: lineChartData,
options: lineChartOptions
});

