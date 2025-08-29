<?php
// Get date parameter or set default
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
// Get pet_id and dog_id parameters
$petId = isset($_GET['pet_id']) ? $_GET['pet_id'] : 'Snoopy';
$dogId = isset($_GET['dog_id']) ? $_GET['dog_id'] : 'CANINE001';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barking Frequency Chart</title>
    <link rel="stylesheet" href="new.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="chart.js"></script>
    <?php include 'navbar.php'; ?>
    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .back-link:hover {
            transform: translateX(-5px);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .date-display {
            font-size: 1.2rem;
            color: var(--primary);
            font-weight: 600;
        }
        
        #chartContainer {
            height: 400px;
            padding: 20px;
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        
        .summary-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            flex: 1;
            min-width: 180px;
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 20px;
            text-align: center;
        }
        
        .stat-title {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .frequency-scale {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding: 0 10px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php?date=<?php echo $date; ?>&pet_id=<?php echo $petId; ?>&dog_id=<?php echo $dogId; ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
        
        <div class="chart-header">
            <h1 class="page-title">Barking Frequency Chart</h1>
            <div class="date-display">Date: <?php echo date('d-m-Y', strtotime($date)); ?></div>
        </div>
        
        <div id="chartContainer">
            <canvas id="barkingFrequencyChart"></canvas>
            <div class="frequency-scale">
                <span>0 = None</span>
                <span>1 = Low</span>
                <span>2 = Medium</span>
                <span>3 = High</span>
            </div>
        </div>
        
        <div class="summary-stats">
            <div class="stat-card">
                <h3 class="stat-title">Average Frequency</h3>
                <div class="stat-value" id="avgFrequency">-</div>
            </div>
            
            <div class="stat-card">
                <h3 class="stat-title">Most Common</h3>
                <div class="stat-value" id="mostCommon">-</div>
            </div>
            
            <div class="stat-card">
                <h3 class="stat-title">Maximum Level</h3>
                <div class="stat-value" id="maxFrequency">-</div>
            </div>
        </div>
    </div>

    <script>
        // Format date for API call
        const queryDate = '<?php echo date('d-m-Y', strtotime($date)); ?>';
        
        // Fetch data from the server
        fetch(`fetch_dog_data.php?date=${queryDate}&dog_id=<?php echo $dogId; ?>`)
        .then(response => response.json())
        .then(data => {
            const hours = data.map(d => d.Hour);
            
            // Convert Barking Frequency to numerical values
            const barkingValues = data.map(d => {
                switch (d.F_Desc.toLowerCase()) {
                    case 'none': return 0;
                    case 'low': return 1;
                    case 'medium': return 2;
                    case 'high': return 3;
                    default: return 0;
                }
            });
            
            // Calculate statistics
            const avgFrequency = (barkingValues.reduce((a, b) => a + b, 0) / barkingValues.length).toFixed(1);
            const maxFrequency = Math.max(...barkingValues);
            
            // Find most common frequency
            const frequencyCounts = {};
            barkingValues.forEach(val => {
                frequencyCounts[val] = (frequencyCounts[val] || 0) + 1;
            });
            
            let mostCommonValue = 0;
            let maxCount = 0;
            
            for (const [value, count] of Object.entries(frequencyCounts)) {
                if (count > maxCount) {
                    mostCommonValue = parseInt(value);
                    maxCount = count;
                }
            }
            
            const frequencyLabels = ['None', 'Low', 'Medium', 'High'];
            
            // Update statistics display
            document.getElementById('avgFrequency').textContent = avgFrequency;
            document.getElementById('mostCommon').textContent = frequencyLabels[mostCommonValue];
            document.getElementById('maxFrequency').textContent = frequencyLabels[maxFrequency];

            // Create chart using the chart.js utility
            createLineChart(
                document.getElementById('barkingFrequencyChart').getContext('2d'),
                'Barking Frequency',
                hours,
                barkingValues,
                '#EF476F'
            );
        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('chartContainer').innerHTML = '<p class="error-message">Error loading chart data. Please try again later.</p>';
        });
    </script>
</body>
</html>
