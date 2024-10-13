<?php
    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'personalproject');

    // Check connection
    if ($conn->connect_error) {
        die('Connection Failed: ' . $conn->connect_error);
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'] ?? '';
        $hero = $_POST['hero'] ?? '';
        $victory = $_POST['victory'] ?? '';
        $totalSouls = $_POST['totalSouls'] ?? 0;
        $kills = $_POST['kills'] ?? 0;
        $deaths = $_POST['deaths'] ?? 0;
        $assists = $_POST['assists'] ?? 0;
        $playerDmg = $_POST['playerDmg'] ?? 0;
        $objDmg = $_POST['objDmg'] ?? 0;
        $healing = $_POST['healing'] ?? 0;

        // Check if user wants to view stats
        if (isset($_POST['viewStats'])) {
            // Fetch all game results for the user
            $stmt = $conn->prepare("SELECT * FROM game_results WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Initialize counters for additional statistics
            $totalGames = 0;
            $victories = 0;
            $totalKills = 0;
            $totalDeaths = 0;
            $totalAssists = 0;
            $totalPlayerDmg = 0;
            $totalObjDmg = 0;
            $totalHealing = 0;
            $totalSouls = 0;
            $heroWins = [];
            $heroPlays = []; // Tracks total games played per hero

            echo "<h2>Game Stats for " . htmlspecialchars($username) . "</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Victory</th><th>Hero</th><th>Total Souls</th><th>Kills</th><th>Deaths</th><th>Assists</th><th>Player Damage</th><th>Objective Damage</th><th>Healing</th></tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['victory']) . "</td>";
                echo "<td>" . htmlspecialchars($row['hero']) . "</td>";
                echo "<td>" . htmlspecialchars($row['total_souls']) . "</td>";
                echo "<td>" . htmlspecialchars($row['kills']) . "</td>";
                echo "<td>" . htmlspecialchars($row['deaths']) . "</td>";
                echo "<td>" . htmlspecialchars($row['assists']) . "</td>";
                echo "<td>" . htmlspecialchars($row['player_dmg']) . "</td>";
                echo "<td>" . htmlspecialchars($row['obj_dmg']) . "</td>";
                echo "<td>" . htmlspecialchars($row['healing']) . "</td>";
                echo "</tr>";

                // Count games
                $totalGames++;

                // Track games per hero
                $hero = $row['hero'];
                if (!isset($heroPlays[$hero])) {
                    $heroPlays[$hero] = 0;
                }
                $heroPlays[$hero]++;

                // Count victories
                if ($row['victory'] === 'yes') {
                    $victories++;

                    // Count victories per hero
                    if (!isset($heroWins[$hero])) {
                        $heroWins[$hero] = 0;
                    }
                    $heroWins[$hero]++;
                }

                // Sum up for average calculation
                $totalKills += $row['kills'];
                $totalDeaths += $row['deaths'];
                $totalAssists += $row['assists'];
                $totalPlayerDmg += $row['player_dmg'];
                $totalObjDmg += $row['obj_dmg'];
                $totalHealing += $row['healing'];
                $totalSouls += $row['total_souls'];
            }
            echo "</table>";
            $stmt->close();

            // Calculate averages
            $averageKills = $totalGames > 0 ? $totalKills / $totalGames : 0;
            $averageDeaths = $totalGames > 0 ? $totalDeaths / $totalGames : 0;
            $averageAssists = $totalGames > 0 ? $totalAssists / $totalGames : 0;
            $averagePlayerDmg = $totalGames > 0 ? $totalPlayerDmg / $totalGames : 0;
            $averageObjDmg = $totalGames > 0 ? $totalObjDmg / $totalGames : 0;
            $averageHealing = $totalGames > 0 ? $totalHealing / $totalGames : 0;
            $averageSouls = $totalGames > 0 ? $totalSouls / $totalGames : 0;

            // Calculate K/D ratio
            $killDeathRatio = $totalDeaths > 0 ? $totalKills / $totalDeaths : $totalKills;

            // Calculate win rate
            $winRate = $totalGames > 0 ? ($victories / $totalGames) * 100 : 0;

            // Determine the hero with the most wins
            $tophero = '';
            $maxWins = 0;
            foreach ($heroWins as $char => $wins) {
                if ($wins > $maxWins) {
                    $maxWins = $wins;
                    $tophero = $char;
                }
            }

            // Determine most and least played heroes
            $mostPlayedHero = '';
            $leastPlayedHero = '';
            $mostPlayedCount = 0;
            $leastPlayedCount = PHP_INT_MAX;
            
            foreach ($heroPlays as $char => $plays) {
                if ($plays > $mostPlayedCount) {
                    $mostPlayedCount = $plays;
                    $mostPlayedHero = $char;
                }
                if ($plays < $leastPlayedCount) {
                    $leastPlayedCount = $plays;
                    $leastPlayedHero = $char;
                }
            }

            // Determine hero with the least wins
            $leastWinsHero = '';
            $minWins = PHP_INT_MAX;
            foreach ($heroWins as $char => $wins) {
                if ($wins < $minWins) {
                    $minWins = $wins;
                    $leastWinsHero = $char;
                }
            }

            // Display additional statistics
            echo "<h3>Additional Stats for " . htmlspecialchars($username) . ":</h3>";
            echo "<p>Total Games Played: " . $totalGames . "</p>";
            echo "<p>Average Win Rate: " . number_format($winRate, 2) . "%</p>";
            echo "<p>Kill/Death Ratio: " . number_format($killDeathRatio, 2) . "</p>";
            echo "<p>Average Kills: " . number_format($averageKills, 2) . "</p>";
            echo "<p>Average Deaths: " . number_format($averageDeaths, 2) . "</p>";
            echo "<p>Average Assists: " . number_format($averageAssists, 2) . "</p>";
            echo "<p>Average Player Damage: " . number_format($averagePlayerDmg, 2) . "</p>";
            echo "<p>Average Objective Damage: " . number_format($averageObjDmg, 2) . "</p>";
            echo "<p>Average Healing: " . number_format($averageHealing, 2) . "</p>";
            echo "<p>Average Souls Collected: " . number_format($averageSouls, 2) . "</p>";

               // Display hero with the most wins
            if ($tophero) {
                echo "<p>Hero with Most Wins: " . htmlspecialchars($tophero) . " (Wins: $maxWins)</p>";
            } else {
                echo "<p>No wins recorded for any hero.</p>";
            }

             // Display hero with the least wins
            if ($leastWinsHero) {
                echo "<p>Hero with Least Wins: " . htmlspecialchars($leastWinsHero) . " (Wins: $minWins)</p>";
            } else {
                echo "<p>No hero has recorded a win.</p>";
            }

            // Display most and least played heroes
            if ($mostPlayedHero) {
                echo "<p>Most Played Hero: " . htmlspecialchars($mostPlayedHero) . " (Games: $mostPlayedCount)</p>";
            }
            if ($leastPlayedHero) {
                echo "<p>Least Played Hero: " . htmlspecialchars($leastPlayedHero) . " (Games: $leastPlayedCount)</p>";
            }

        
        } else {
            // Insert game stats into the database
            $stmt = $conn->prepare("INSERT INTO game_results (username, hero, victory, total_souls, kills, deaths, assists, player_dmg, obj_dmg, healing) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiiiiiii", $username, $hero, $victory, $totalSouls, $kills, $deaths, $assists, $playerDmg, $objDmg, $healing);
            $stmt->execute();
            echo "Game results successfully saved!";
            $stmt->close();
        }
    }

    $conn->close();
?>
