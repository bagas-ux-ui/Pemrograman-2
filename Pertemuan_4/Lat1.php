
<!DOCTYPE html>
<html>
<head>
    <title>Tabel Quantity dan Price</title>
</head>
<body>

<?php
$brush_price = 5;
$counter = 10;

echo "<table border='1' align='center' cellpadding='5'>";
echo "<tr><th>Quantity</th><th>Price</th></tr>";

while ($counter <= 100) {
    echo "<tr>";
    echo "<td>" . $counter . "</td>";
    echo "<td>" . ($brush_price * $counter) . "</td>";
    echo "</tr>";

    $counter += 10;
}

echo "</table>";
?>

</body>
</html>
