<?php

date_default_timezone_set('Australia/Sydney');

class Role {
    public $id;
    public $name;
    public $type;


    // constructor
    public function __construct($id, $name, $type) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;

    }


    // search location by one field or combination of fields
    public function searchLocation($conn, $locationId, $description, $charging_station, $cost_per_hour, $available_status) {
        $conditions = array();
        $params = array();
        $types = "";

        if (!empty($locationId)) {
            $conditions[] = "locationId = ?";
            $params[] = $locationId;
            $types .= "i";
        }
        if (!empty($description)) {
            $conditions[] = "description LIKE ?";
            $params[] = "%$description%";
            $types .= "s";
        }
        if (!empty($charging_station)) {
            $conditions[] = "charging_station = ?";
            $params[] = $charging_station;
            $types .= "i";
        }
        if (!empty($cost_per_hour)) {
            $conditions[] = "cost_per_hour = ?";
            $params[] = $cost_per_hour;
            $types .= "d";
        }

        // Handle available status
        if ($available_status == "available") {
            $conditions[] = "available_stations > 0";
        } elseif ($available_status == "full") {
            $conditions[] = "available_stations = 0";
        }

        $sql = "SELECT * FROM Location";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
}

class User extends Role {
    // list charging records by the user
    public function searchCharging($conn, $status) {
        if ($status == "all") {
            $sql = "SELECT c.*, l.description, l.cost_per_hour 
            FROM Charging c 
            INNER JOIN Location l ON c.locationId = l.locationId 
            WHERE c.userId = ?";
        } else {
            $sql = "SELECT c.*, l.description, l.cost_per_hour 
            FROM Charging c 
            INNER JOIN Location l ON c.locationId = l.locationId 
            WHERE c.userId = ? AND c.status = ?";
        }
        $stmt = $conn->prepare($sql);
        if ($status == "all") {
            $stmt->bind_param("i", $this->id);
        } else {
            $stmt->bind_param("is", $this->id, $status);
        }
        $stmt->execute();
        return $stmt->get_result();
    }


    // check in for charging
    public function checkIn($conn, $locationId) {
           // Decrease available stations
            $stmt = $conn->prepare("UPDATE Location SET available_stations = available_stations - 1 WHERE locationId = ?");
            $stmt->bind_param("i", $locationId);
            $stmt->execute();

            // Create charging record
            $start_time = new DateTime();
            $formatted_time = $start_time->format('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO Charging (locationId, userId, start_time) VALUES (?, ?, ?);");
            $stmt->bind_param("iis", $locationId, $this->id, $formatted_time);
            $stmt->execute();
            return $formatted_time;
    }

    // check out for charging
    public function checkOut($conn, $chargingId, $locationId, $start_time, $cost_per_hour) {
            // Increase available stations
            $stmt = $conn->prepare("UPDATE Location SET available_stations = available_stations + 1 WHERE locationId = ?");
            $stmt->bind_param("i", $locationId);
            $stmt->execute();
     
            // Calculate cost
            $finish_time = new DateTime();
            $formatted_finish_time = $finish_time->format('Y-m-d H:i:s');
            
            // Convert start_time string to DateTime object if it's not already
            if (!($start_time instanceof DateTime)) {
                $start_time = new DateTime($start_time);
            }
            
            // Calculate difference between finish and start time
            $diff = $finish_time->diff($start_time);
            
            // Calculate total hours including partial hours
            $total_hours = $diff->days * 24 + $diff->h + ($diff->i / 60);
            $cost = $total_hours * $cost_per_hour;
            $cost = round($cost, 2);

            // Update charging record
            $stmt = $conn->prepare(
                "UPDATE Charging 
                SET finish_time = ?, 
                    cost = ?, 
                    status = 'completed' 
                WHERE chargingId = ?"
            );
            $stmt->bind_param("sdi", $formatted_finish_time, $cost, $chargingId);
            $stmt->execute();

            // Return both the finish time and cost as an array
            return [
                'finish_time' => $formatted_finish_time,
                'cost' => $cost
            ];
        }
}

class Administrator extends Role {

    // insert a new location
    public function insertLocation($conn, $description, $charging_station, $cost_per_hour, $available_stations) {
        $stmt = $conn->prepare(
            "INSERT INTO Location (description, charging_station, cost_per_hour, available_stations) 
            VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("sidd", $description, $charging_station, $cost_per_hour, $available_stations);
        return $stmt->execute();
    }

    //get a location by locationId
    public function getLocation($conn, $locationId) {
        $stmt = $conn->prepare("SELECT * FROM Location WHERE locationId = ?");
        $stmt->bind_param("i", $locationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // update a location
    public function updateLocation($conn, $locationId, $description, $charging_station, $cost_per_hour, $available_stations) {
        $stmt = $conn->prepare(
            "UPDATE Location 
            SET description = ?, 
                charging_station = ?, 
                cost_per_hour = ?, 
                available_stations = ? 
            WHERE locationId = ?"
        );
        $stmt->bind_param("siddi", $description, $charging_station, $cost_per_hour, $available_stations, $locationId);
        return $stmt->execute();
    }

    // search users by checkin_user, if checkin_user is all, return all users, if checkin_user is checkin, return users who are currently checked in
    public function searchUsers($conn, $checkin_user) {
        if ($checkin_user == "all") {
            $sql = "SELECT * FROM User";
        } else {
            $sql = "SELECT DISTINCT u.*
            FROM User u 
            INNER JOIN Charging c ON u.id = c.userId 
            WHERE c.status = 'charging'";
        }
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result();
    }


}
?>

    
    





