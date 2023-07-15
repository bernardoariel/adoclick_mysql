<?php

class UserStats{

    private $db;
    
    public function __construct($db){
        $this->db = $db;
    }

    public function getStats($dateFrom,$dateTo,$totalClicks = null){

        $sql = "
            SELECT CONCAT(users.first_name,' ',users.last_name) AS full_name,
            SUM(user_stats.views) AS total_views,
            SUM(user_stats.clicks) AS total_clicks,
            SUM(user_stats.conversions) AS total_conversions,
            ROUND((SUM(user_stats.conversions) / SUM(user_stats.clicks)) * 100, 2) AS cr,
            MAX(user_stats.date) AS last_date
            FROM user_stats
            INNER JOIN users ON user_stats.user_id = users.id
            WHERE user_stats.date BETWEEN '$dateFrom' AND '$dateTo' AND users.status = 'active'
            GROUP BY users.id, users.first_name, users.last_name
            ";
            // cr: (conversion rate) calcularlo con la siguiente formula (total de conversions / total de clicks)*100 y redondearlo a 2 decimales
        if($totalClicks != null){
            $sql .= " HAVING total_clicks >= $totalClicks ";
        }
      
                // echo '<pre>'; print_r($sql); echo '</pre>'; 
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}


/* Conexion a la bd */
$dbHost = 'localhost';
$dbName = 'prueba';
$dbUsername = 'root';
$dbPassword = '';

try{

    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch(PDOException $e){

    die("Error al conectarse con la base de datos: " . $e->getMessage());

}

$userStats = new UserStats($db);

/* datos */
$dateFrom = '2022-10-01';
$dateTo = '2022-10-15';
$totalClicks = 8000;
$stats = $userStats->getStats($dateFrom,$dateTo,$totalClicks);


echo '<pre>'; print_r($stats); echo '</pre>';
/* 
[full_name] => marge simpson
[total_views] => 8312072
[total_clicks] => 9271
[total_conversions] => 639
[cr] => 6.89
[last_date] => 2022-10-15 */