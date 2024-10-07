<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "landregistrydb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$ownerName = $data['ownerName'];
$ownerAddress = $data['ownerAddress'];
$ownerPhone = $data['ownerPhone'];
$ownerEmail = $data['ownerEmail'];
$landLocation = $data['landLocation'];
$landArea = $data['landArea'];
$landType = $data['landType'];

// Register the owner if not already registered
$registerOwnerQuery = "
    INSERT INTO Owners (Name, Address, Phone, Email)
    VALUES ('$ownerName', '$ownerAddress', '$ownerPhone', '$ownerEmail')
    ON DUPLICATE KEY UPDATE
    Name = VALUES(Name), Address = VALUES(Address), Phone = VALUES(Phone), Email = VALUES(Email)
";
if ($conn->query($registerOwnerQuery) === TRUE) {
    // Fetch the owner's ID
    $getOwnerIDQuery = "SELECT OwnerID FROM Owners WHERE Address = '$ownerAddress'";
    $result = $conn->query($getOwnerIDQuery);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $ownerID = $row['OwnerID'];

        // Register the land
        $registerLandQuery = "
            INSERT INTO Lands (Location, Size, LandType, OwnerID)
            VALUES ('$landLocation', '$landArea', '$landType', '$ownerID')
        ";
        if ($conn->query($registerLandQuery) === TRUE) {
            $landID = $conn->insert_id;
            echo json_encode([
                "message" => "Land registered successfully!",
                "landID" => $landID,
                "ownerName" => $ownerName,
                "ownerAddress" => $ownerAddress,
                "landLocation" => $landLocation,
                "landArea" => $landArea,
                "landType" => $landType
            ]);
        } else {
            echo json_encode(["error" => "Error registering land: " . $conn->error]);
        }
    } else {
        echo json_encode(["error" => "Error fetching owner ID: " . $conn->error]);
    }
} else {
    echo json_encode(["error" => "Error registering owner: " . $conn->error]);
}

$conn->close();
?>
