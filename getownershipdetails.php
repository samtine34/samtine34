<?php  
$servername = "localhost";  
$username = "root";  
$password = "";  
$dbname = "landregistrydb";  

// Create connection  
$conn = new mysqli($servername, $username, $password, $dbname);  

// Check connection  
if ($conn->connect_error) {  
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));  
}  

// Get parameters from query  
$landID = isset($_GET['landID']) ? $conn->real_escape_string($_GET['landID']) : null;  
$ownerPhone = isset($_GET['ownerPhone']) ? $conn->real_escape_string($_GET['ownerPhone']) : null;  

// Prepare the query based on the available parameter  
if ($landID) {  
    // Fetch ownership details by land ID  
    $query = "  
        SELECT o.Name AS ownerName,   
               o.Address AS ownerAddress,   
               o.Phone AS ownerPhone,   
               o.Email AS ownerEmail,   
               l.LandID,   
               l.Location AS landLocation,   
               l.Size AS landArea,   
               l.LandType AS landType  
        FROM Owners o  
        JOIN Lands l ON o.OwnerID = l.OwnerID  
        WHERE l.LandID = '$landID'  
    ";  
} elseif ($ownerPhone) {  
    // Fetch ownership details by owner phone  
    $query = "  
        SELECT o.Name AS ownerName,   
               o.Address AS ownerAddress,   
               o.Phone AS ownerPhone,   
               o.Email AS ownerEmail,   
               l.LandID,   
               l.Location AS landLocation,   
               l.Size AS landArea,   
               l.LandType AS landType  
        FROM Owners o  
        JOIN Lands l ON o.OwnerID = l.OwnerID  
        WHERE o.Phone = '$ownerPhone'  
    ";  
} else {  
    // If neither parameter is provided, return an empty array  
    echo json_encode([]);  
    exit;  
}  

$result = $conn->query($query);  

if ($result && $result->num_rows > 0) {  
    $data = [];  
    while ($row = $result->fetch_assoc()) {  
        $data[] = $row;  
    }  
    echo json_encode($data);  
} else {  
    echo json_encode([]);  
}  

$conn->close();  
?>