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

// Get LandID from query parameter
$landID = $_GET['landID'];

// Fetch land details
$query = "
    SELECT l.LandID, l.Location, l.Size AS landArea, l.LandType, o.Name AS ownerName, o.Address AS ownerAddress
    FROM Lands l
    JOIN Owners o ON l.OwnerID = o.OwnerID
    WHERE l.LandID = '$landID'
";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode($data);
} else {
    echo json_encode(["error" => "No land found with the given ID"]);
}

$conn->close();
?>
