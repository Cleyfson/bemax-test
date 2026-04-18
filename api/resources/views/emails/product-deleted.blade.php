<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Deleted</title>
</head>
<body>
    <h2>Product deleted</h2>
    <table>
        <tr><td><strong>Name</strong></td><td>{{ $name }}</td></tr>
        <tr><td><strong>UUID</strong></td><td>{{ $uuid }}</td></tr>
        <tr><td><strong>Price</strong></td><td>{{ number_format($price, 2) }}</td></tr>
        <tr><td><strong>Deleted at</strong></td><td>{{ $deletedAt }}</td></tr>
    </table>
</body>
</html>
