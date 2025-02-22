<!DOCTYPE html>
<html>
<head>
    <title>Request Logs</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Request Logs</h1>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Method</th>
                <th>URI</th>
                <th>Query</th>
                <th>Status</th>
                <th>IP</th>
                <th>Response Time (ms)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo isset($log['timestamp']) ? htmlspecialchars($log['timestamp']) : ''; ?></td>
                        <td><?php echo isset($log['method']) ? htmlspecialchars($log['method']) : ''; ?></td>
                        <td><?php echo isset($log['uri']) ? htmlspecialchars($log['uri']) : ''; ?></td>
                        <td><?php echo isset($log['query']) ? htmlspecialchars($log['query']) : ''; ?></td>
                        <td><?php echo isset($log['status_code']) ? htmlspecialchars($log['status_code']) : ''; ?></td>
                        <td><?php echo isset($log['ip']) ? htmlspecialchars($log['ip']) : ''; ?></td>
                        <td><?php echo isset($log['response_time']) ? htmlspecialchars($log['response_time']) : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No logs found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
