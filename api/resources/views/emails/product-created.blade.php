<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Created</title>
</head>
<body>
    <h2>New product created</h2>
    <table>
        <tr><td><strong>Name</strong></td><td>{{ $name }}</td></tr>
        <tr><td><strong>UUID</strong></td><td>{{ $uuid }}</td></tr>
        <tr><td><strong>Price</strong></td><td>{{ number_format($price, 2) }}</td></tr>
        <tr><td><strong>Created at</strong></td><td>{{ $createdAt }}</td></tr>
    </table>
</body>
</html>
